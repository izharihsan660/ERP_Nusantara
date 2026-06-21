<?php

namespace App\Services;

use App\Actions\ActivityLog\RecordActivity;
use App\Enums\QuotationStatus;
use App\Models\Katalog;
use App\Models\Quotation;
use App\Models\User;
use App\Notifications\QuotationSubmittedNotification;
use App\Support\NotificationHelper;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class QuotationService
{
    public function __construct(
        private readonly DocumentNumberService $documentNumberService,
        private readonly QuotationPDFService $quotationPDFService,
        private readonly RecordActivity $recordActivity,
        private readonly NotificationHelper $notificationHelper,
    ) {}

    public function paginate(array $filters): LengthAwarePaginator
    {
        $sort = in_array($filters['sort'] ?? null, ['no_quotation', 'tgl_quotation', 'revisi', 'status', 'created_at'], true)
            ? $filters['sort']
            : 'created_at';

        return Quotation::query()
            ->with(['customer:id,nama_customer', 'items:id,quotation_id,jumlah'])
            ->when($filters['search'] ?? null, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('no_quotation', 'like', "%{$search}%")
                        ->orWhereHas('customer', fn ($customerQuery) => $customerQuery->where('nama_customer', 'like', "%{$search}%"));
                });
            })
            ->when($filters['customer_id'] ?? null, fn ($query, string $customerId) => $query->where('customer_id', $customerId))
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->when($filters['date_from'] ?? null, fn ($query, string $date) => $query->whereDate('tgl_quotation', '>=', $date))
            ->when($filters['date_to'] ?? null, fn ($query, string $date) => $query->whereDate('tgl_quotation', '<=', $date))
            ->orderBy($sort, ($filters['direction'] ?? 'desc') === 'asc' ? 'asc' : 'desc')
            ->paginate((int) ($filters['per_page'] ?? 10))
            ->withQueryString();
    }

    public function create(array $data, User $user): Quotation
    {
        return DB::transaction(function () use ($data, $user): Quotation {
            $date = Carbon::parse($data['tgl_quotation']);
            $quotation = Quotation::create([
                'no_quotation' => $this->documentNumberService->generateQuotationNumber($date),
                'tgl_quotation' => $date,
                'customer_id' => $data['customer_id'],
                'template_id' => $data['template_id'],
                'revisi' => $data['revisi'] ?? 0,
                'status' => QuotationStatus::Draft,
                'catatan' => $data['catatan'] ?? null,
                'created_by' => $user->id,
            ]);

            $this->syncItems($quotation, $data['items']);
            $this->recordActivity->handle('created_quotation', $quotation, "Membuat quotation {$quotation->no_quotation}");

            return $quotation->load(['customer', 'template', 'items']);
        });
    }

    public function submit(Quotation $quotation, User $user): Quotation
    {
        $this->ensureStatus($quotation, QuotationStatus::Draft, 'Quotation hanya bisa disubmit dari status Draft.');

        $quotation->update(['status' => QuotationStatus::PendingApproval]);
        $this->recordActivity->handle('submitted_quotation', $quotation, "{$user->name} submit quotation {$quotation->no_quotation}");
        $this->notificationHelper->getUsersByRole('Manager')->each->notify(new QuotationSubmittedNotification($quotation));

        return $quotation->refresh();
    }

    public function approve(Quotation $quotation, User $user): Quotation
    {
        $this->ensureStatus($quotation, QuotationStatus::PendingApproval, 'Quotation hanya bisa diapprove dari status Pending Approval.');

        return DB::transaction(function () use ($quotation, $user): Quotation {
            $quotation->update([
                'status' => QuotationStatus::Approved,
                'qr_token' => Str::random(64),
                'approved_by' => $user->id,
                'approved_at' => now(),
            ]);

            $this->quotationPDFService->generate($quotation->refresh());
            $this->recordActivity->handle('approved_quotation', $quotation, "{$user->name} approve quotation {$quotation->no_quotation}");

            return $quotation->refresh();
        });
    }

    public function reject(Quotation $quotation, string $catatan, User $user): Quotation
    {
        $this->ensureStatus($quotation, QuotationStatus::PendingApproval, 'Quotation hanya bisa direject dari status Pending Approval.');

        $quotation->update([
            'status' => QuotationStatus::Rejected,
            'catatan_rejection' => $catatan,
        ]);
        $this->recordActivity->handle('rejected_quotation', $quotation, "{$user->name} reject quotation {$quotation->no_quotation}");

        return $quotation->refresh();
    }

    public function void(Quotation $quotation, string $alasan, User $user): Quotation
    {
        if (! $quotation->isVoidable()) {
            throw ValidationException::withMessages(['status' => 'Quotation sudah berstatus Void.']);
        }

        $quotation->update([
            'status' => QuotationStatus::Void,
            'voided_by' => $user->id,
            'voided_at' => now(),
            'alasan_void' => $alasan,
        ]);
        $this->recordActivity->handle('voided_quotation', $quotation, "{$user->name} void quotation {$quotation->no_quotation}");

        return $quotation->refresh();
    }

    public function duplicate(Quotation $quotation, User $user): Quotation
    {
        $quotation->loadMissing('items');

        return $this->create([
            'tgl_quotation' => now()->toDateString(),
            'customer_id' => $quotation->customer_id,
            'template_id' => $quotation->template_id,
            'catatan' => $quotation->catatan,
            'revisi' => $quotation->revisi + 1,
            'items' => $quotation->items->map(fn ($item): array => [
                'katalog_id' => $item->katalog_id,
                'qty' => $item->qty,
                'harga_satuan' => $item->harga_satuan,
            ])->all(),
        ], $user);
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     */
    private function syncItems(Quotation $quotation, array $items): void
    {
        foreach ($items as $item) {
            $katalog = Katalog::query()->active()->findOrFail($item['katalog_id']);

            $quotation->items()->create([
                'katalog_id' => $katalog->id,
                'part_no' => $katalog->part_no,
                'deskripsi' => str($katalog->nama_barang)->limit(200, '')->toString(),
                'qty' => $item['qty'],
                'satuan' => $katalog->satuan,
                'harga_satuan' => $item['harga_satuan'],
                'hpp_satuan' => $katalog->hpp,
            ]);
        }
    }

    private function ensureStatus(Quotation $quotation, QuotationStatus $status, string $message): void
    {
        if ($quotation->status !== $status) {
            throw ValidationException::withMessages(['status' => $message]);
        }
    }
}
