<?php

namespace App\Services;

use App\Actions\ActivityLog\RecordActivity;
use App\Enums\PurchaseOrderStatus;
use App\Models\PurchaseOrder;
use App\Models\User;
use App\Notifications\PoNajSubmittedNotification;
use App\Support\NotificationHelper;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PurchaseOrderService
{
    public function __construct(
        private readonly DocumentNumberService $documentNumberService,
        private readonly PurchaseOrderPDFService $purchaseOrderPDFService,
        private readonly RecordActivity $recordActivity,
        private readonly NotificationHelper $notificationHelper,
    ) {}

    public function paginate(array $filters): LengthAwarePaginator
    {
        $sort = in_array($filters['sort'] ?? null, ['no_purchase_order', 'tgl_po', 'status', 'created_at'], true)
            ? $filters['sort']
            : 'created_at';

        return PurchaseOrder::query()
            ->with(['vendor:id,nama_vendor', 'items:id,purchase_order_id,qty,harga_satuan,jumlah'])
            ->when($filters['search'] ?? null, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('no_purchase_order', 'like', "%{$search}%")
                        ->orWhere('no_pr_customer', 'like', "%{$search}%")
                        ->orWhere('no_po_customer', 'like', "%{$search}%")
                        ->orWhereHas('vendor', fn ($vendorQuery) => $vendorQuery->where('nama_vendor', 'like', "%{$search}%"));
                });
            })
            ->when($filters['vendor_id'] ?? null, fn ($query, string $vendorId) => $query->where('vendor_id', $vendorId))
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->when($filters['date_from'] ?? null, fn ($query, string $date) => $query->whereDate('tgl_po', '>=', $date))
            ->when($filters['date_to'] ?? null, fn ($query, string $date) => $query->whereDate('tgl_po', '<=', $date))
            ->orderBy($sort, ($filters['direction'] ?? 'desc') === 'asc' ? 'asc' : 'desc')
            ->paginate((int) ($filters['per_page'] ?? 10))
            ->withQueryString();
    }

    public function create(array $data, User $user): PurchaseOrder
    {
        return DB::transaction(function () use ($data, $user): PurchaseOrder {
            $date = Carbon::parse($data['tgl_po']);
            $purchaseOrder = PurchaseOrder::create([
                'no_purchase_order' => $this->documentNumberService->generatePurchaseOrderNumber($date),
                'tgl_po' => $date,
                'customer_id' => $data['customer_id'],
                'vendor_id' => $data['vendor_id'],
                'no_pr_customer' => $data['no_pr_customer'] ?? null,
                'no_po_customer' => $data['no_po_customer'] ?? null,
                'status' => PurchaseOrderStatus::Draft,
                'catatan' => $data['catatan'] ?? null,
                'created_by' => $user->id,
            ]);

            foreach ($data['items'] as $item) {
                $purchaseOrder->items()->create([
                    'katalog_id' => $item['katalog_id'] ?? null,
                    'deskripsi' => $item['deskripsi'],
                    'qty' => $item['qty'],
                    'satuan' => $item['satuan'],
                    'harga_satuan' => $item['harga_satuan'],
                    'jumlah' => (int) $item['qty'] * (float) $item['harga_satuan'],
                ]);
            }

            $this->recordActivity->handle('created_purchase_order', $purchaseOrder, "{$user->name} membuat Purchase Order {$purchaseOrder->no_purchase_order}");

            return $purchaseOrder->load(['vendor', 'items']);
        });
    }

    public function submit(PurchaseOrder $purchaseOrder, User $user): PurchaseOrder
    {
        $this->ensureStatus($purchaseOrder, PurchaseOrderStatus::Draft, 'Purchase Order hanya bisa disubmit dari status Draft.');

        if (! $purchaseOrder->items()->exists()) {
            throw ValidationException::withMessages(['items' => 'Purchase Order harus memiliki minimal 1 item sebelum submit.']);
        }

        $purchaseOrder->update(['status' => PurchaseOrderStatus::PendingApproval]);
        $this->recordActivity->handle('submitted_purchase_order', $purchaseOrder, "{$user->name} submit Purchase Order {$purchaseOrder->no_purchase_order}");
        $this->notificationHelper->getUsersByRole('Manager')->each->notify(new PoNajSubmittedNotification($purchaseOrder));

        // Send approval email
        NotificationService::sendApprovalEmail($purchaseOrder, 'purchase_order');

        return $purchaseOrder->refresh();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateReferensi(PurchaseOrder $purchaseOrder, array $data, User $user): PurchaseOrder
    {
        $purchaseOrder->update([
            'no_pr_customer' => $data['no_pr_customer'] ?? null,
            'no_po_customer' => $data['no_po_customer'] ?? null,
        ]);
        $this->recordActivity->handle('updated_referensi_purchase_order', $purchaseOrder, "{$user->name} memperbarui referensi Purchase Order {$purchaseOrder->no_purchase_order}");

        return $purchaseOrder->refresh();
    }

    public function approve(PurchaseOrder $purchaseOrder, User $user): PurchaseOrder
    {
        $this->ensureStatus($purchaseOrder, PurchaseOrderStatus::PendingApproval, 'Purchase Order hanya bisa diapprove dari status Pending Approval.');

        return DB::transaction(function () use ($purchaseOrder, $user): PurchaseOrder {
            $purchaseOrder->update([
                'status' => PurchaseOrderStatus::Approved,
                'qr_token' => Str::random(64),
                'approved_by' => $user->id,
                'approved_at' => now(),
            ]);

            $this->purchaseOrderPDFService->generate($purchaseOrder->refresh());
            $this->recordActivity->handle('approved_purchase_order', $purchaseOrder, "{$user->name} approve Purchase Order {$purchaseOrder->no_purchase_order}");

            return $purchaseOrder->refresh();
        });
    }

    public function reject(PurchaseOrder $purchaseOrder, string $catatan, User $user): PurchaseOrder
    {
        $this->ensureStatus($purchaseOrder, PurchaseOrderStatus::PendingApproval, 'Purchase Order hanya bisa direject dari status Pending Approval.');

        $purchaseOrder->update([
            'status' => PurchaseOrderStatus::Draft,
            'catatan_rejection' => $catatan,
        ]);
        $this->recordActivity->handle('rejected_purchase_order', $purchaseOrder, "{$user->name} reject Purchase Order {$purchaseOrder->no_purchase_order}");

        return $purchaseOrder->refresh();
    }

    public function void(PurchaseOrder $purchaseOrder, string $alasan, User $user): PurchaseOrder
    {
        if ($purchaseOrder->status === PurchaseOrderStatus::Void) {
            throw ValidationException::withMessages(['status' => 'Purchase Order sudah berstatus Void.']);
        }

        if (! $purchaseOrder->isVoidable()) {
            throw ValidationException::withMessages(['status' => 'PO tidak bisa di-void karena sudah memiliki SPB/Invoice aktif. Void dokumen hilir terlebih dahulu.']);
        }

        $purchaseOrder->update([
            'status' => PurchaseOrderStatus::Void,
            'voided_by' => $user->id,
            'voided_at' => now(),
            'alasan_void' => $alasan,
        ]);
        $this->recordActivity->handle('voided_purchase_order', $purchaseOrder, "{$user->name} void Purchase Order {$purchaseOrder->no_purchase_order}");

        return $purchaseOrder->refresh();
    }

    private function ensureStatus(PurchaseOrder $purchaseOrder, PurchaseOrderStatus $status, string $message): void
    {
        if ($purchaseOrder->status !== $status) {
            throw ValidationException::withMessages(['status' => $message]);
        }
    }
}
