<?php

namespace App\Services;

use App\Actions\ActivityLog\RecordActivity;
use App\Enums\DocumentType;
use App\Enums\PurchaseOrderStatus;
use App\Enums\ReferensiTipe;
use App\Enums\SpbStatus;
use App\Enums\StatusSupply;
use App\Enums\WIPStatus;
use App\Models\Customer;
use App\Models\DocumentTemplate;
use App\Models\PurchaseOrder;
use App\Models\Site;
use App\Models\Spb;
use App\Models\User;
use App\Models\WipOrder;
use App\Notifications\SpbCreatedNotification;
use App\Notifications\WipTersupplyNotification;
use App\Support\NotificationHelper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SpbService
{
    public function __construct(
        private readonly DocumentNumberService $documentNumberService,
        private readonly SpbPDFService $spbPDFService,
        private readonly RecordActivity $recordActivity,
        private readonly NotificationHelper $notificationHelper,
    ) {}

    public function create(array $data, Model $spbAble, User $user): Spb
    {
        $this->validateSource($spbAble);

        return DB::transaction(function () use ($data, $spbAble, $user): Spb {
            $customer = $this->resolveCustomer($data, $spbAble);
            $template = $this->resolveTemplate($customer);
            $reference = $this->resolveReference($spbAble);
            $date = Carbon::parse($data['tgl_spb']);

            $this->validateSite($data['site_id'] ?? null, $customer);
            $itemsToCreate = $this->validatedItemsForSource($data['items'], $spbAble);

            $spb = Spb::create([
                'no_spb' => $this->documentNumberService->generateSpbNumber($date),
                'tgl_spb' => $date,
                'customer_id' => $customer->id,
                'site_id' => $data['site_id'] ?? null,
                'template_id' => $template->id,
                'spb_able_type' => $spbAble::class,
                'spb_able_id' => $spbAble->getKey(),
                'referensi_tipe' => $reference['tipe'],
                'no_referensi' => $reference['nomor'],
                'nama_ekspedisi' => $data['nama_ekspedisi'],
                'etd' => $data['etd'] ?? null,
                'eta' => $data['eta'] ?? null,
                'catatan' => $data['catatan'] ?? null,
                'status' => SpbStatus::Shipped,
                'created_by' => $user->id,
            ]);

            foreach ($itemsToCreate as $item) {
                $spb->items()->create([
                    'part_no' => $item['part_no'],
                    'deskripsi' => $item['deskripsi'],
                    'qty' => $item['qty_kirim'],
                    'berat' => $item['berat'] ?? 0,
                    'volume' => $item['volume'] ?? 0,
                    'dimensi' => $item['dimensi'] ?? null,
                    'sku' => $item['sku'] ?? null,
                ]);
            }

            if ($spbAble instanceof WipOrder) {
                $spbAble->update([
                    'status_supply' => StatusSupply::Tersupply,
                    'tersupply_at' => $spbAble->tersupply_at ?? now(),
                ]);
                $this->notificationHelper->getUsersByRole('Sales')->each->notify(new WipTersupplyNotification($spbAble, 'Sales'));
                $this->notificationHelper->getUsersByRole('Gudang')->each->notify(new WipTersupplyNotification($spbAble, 'Gudang'));
            }

            $this->spbPDFService->generate($spb->refresh());
            $this->recordActivity->handle('created_spb', $spb, "{$user->name} membuat SPB {$spb->no_spb}");
            $this->notificationHelper->getUsersByRole('Finance')->each->notify(new SpbCreatedNotification($spb));

            return $spb->load(['customer', 'site', 'template', 'items', 'spbAble']);
        });
    }

    public function void(Spb $spb, string $alasan, User $user): Spb
    {
        if ($spb->status === SpbStatus::Void) {
            throw ValidationException::withMessages(['status' => 'SPB sudah berstatus Void.']);
        }

        if (! $spb->isVoidable()) {
            throw ValidationException::withMessages(['status' => 'SPB tidak bisa divoid karena sudah memiliki Invoice/Nota aktif.']);
        }

        return DB::transaction(function () use ($spb, $alasan, $user): Spb {
            $spb->update([
                'status' => SpbStatus::Void,
                'voided_by' => $user->id,
                'voided_at' => now(),
                'alasan_void' => $alasan,
            ]);

            $spbAble = $spb->spbAble;
            if ($spbAble instanceof WipOrder && ! $this->hasOtherActiveSpb($spb)) {
                $spbAble->update([
                    'status_supply' => StatusSupply::BelumTersupply,
                    'tersupply_at' => null,
                ]);
            }

            $this->recordActivity->handle('voided_spb', $spb, "{$user->name} void SPB {$spb->no_spb}");

            return $spb->refresh()->load(['customer', 'site', 'items', 'spbAble']);
        });
    }

    public function updateReferensi(Spb $spb, User $user): Spb
    {
        $reference = $this->resolveReference($spb->spbAble);

        if ($reference['tipe'] !== ReferensiTipe::PO) {
            throw ValidationException::withMessages(['no_referensi' => 'Nomor PO customer belum tersedia.']);
        }

        $spb->update([
            'referensi_tipe' => ReferensiTipe::PO,
            'no_referensi' => $reference['nomor'],
        ]);

        $this->recordActivity->handle('updated_referensi_spb', $spb, "{$user->name} update referensi SPB {$spb->no_spb} ke {$spb->no_referensi}");

        return $spb->refresh();
    }

    private function validateSource(Model $spbAble): void
    {
        if ($spbAble instanceof WipOrder) {
            if ($spbAble->status !== WIPStatus::Active) {
                throw ValidationException::withMessages(['spb_able' => 'SPB hanya bisa dibuat dari WIP Active.']);
            }

            return;
        }

        if ($spbAble instanceof PurchaseOrder) {
            if ($spbAble->status !== PurchaseOrderStatus::Approved) {
                throw ValidationException::withMessages(['spb_able' => 'SPB hanya bisa dibuat dari Purchase Order Approved.']);
            }

            return;
        }

        throw ValidationException::withMessages(['spb_able' => 'Sumber SPB tidak valid.']);
    }

    private function resolveCustomer(array $data, Model $spbAble): Customer
    {
        if ($spbAble instanceof WipOrder) {
            $customer = $spbAble->salesOrder?->customer;

            if (! $customer) {
                throw ValidationException::withMessages(['customer_id' => 'Customer Sales Order tidak ditemukan.']);
            }

            return $customer;
        }

        if ($spbAble instanceof PurchaseOrder) {
            $customer = $spbAble->customer;

            if (! $customer) {
                throw ValidationException::withMessages(['customer_id' => 'Customer Purchase Order tidak ditemukan.']);
            }

            return $customer;
        }

        $customer = Customer::query()->find($data['customer_id'] ?? null);

        if (! $customer) {
            throw ValidationException::withMessages(['customer_id' => 'Customer SPB tidak valid.']);
        }

        return $customer;
    }

    private function resolveTemplate(Customer $customer): DocumentTemplate
    {
        $template = $customer->spbTemplate()->first()
            ?? DocumentTemplate::query()
                ->where('tipe_dokumen', DocumentType::Spb->value)
                ->where('is_default', true)
                ->first();

        if (! $template) {
            throw ValidationException::withMessages(['template_id' => 'Template SPB default belum tersedia.']);
        }

        return $template;
    }

    /**
     * @return array{tipe: ReferensiTipe, nomor: string}
     */
    private function resolveReference(Model $spbAble): array
    {
        $referenceOwner = $spbAble instanceof WipOrder ? $spbAble->salesOrder : $spbAble;

        if (filled($referenceOwner?->no_po_customer)) {
            return ['tipe' => ReferensiTipe::PO, 'nomor' => $referenceOwner->no_po_customer];
        }

        if (filled($referenceOwner?->no_pr_customer)) {
            return ['tipe' => ReferensiTipe::PR, 'nomor' => $referenceOwner->no_pr_customer];
        }

        throw ValidationException::withMessages(['no_referensi' => 'No. PO atau No. PR customer wajib tersedia sebelum membuat SPB.']);
    }

    private function validateSite(?int $siteId, Customer $customer): void
    {
        if (! $siteId) {
            return;
        }

        $belongsToCustomer = Site::query()
            ->whereKey($siteId)
            ->where('customer_id', $customer->id)
            ->exists();

        if (! $belongsToCustomer) {
            throw ValidationException::withMessages(['site_id' => 'Site tujuan harus sesuai dengan customer SPB.']);
        }
    }

    private function hasOtherActiveSpb(Spb $spb): bool
    {
        return Spb::query()
            ->where('spb_able_type', $spb->spb_able_type)
            ->where('spb_able_id', $spb->spb_able_id)
            ->whereKeyNot($spb->id)
            ->where('status', '!=', SpbStatus::Void->value)
            ->exists();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function validatedItemsForSource(array $items, Model $spbAble): array
    {
        $sourceItems = $this->sourceItemsByPartNo($spbAble);
        $itemsToCreate = [];

        foreach ($items as $index => $item) {
            $partNo = $item['part_no'] ?? '';
            $qtyKirim = (int) ($item['qty_kirim'] ?? $item['qty'] ?? 0);

            if ($qtyKirim <= 0) {
                continue;
            }

            if (! isset($sourceItems[$partNo])) {
                throw ValidationException::withMessages(["items.{$index}.part_no" => 'Item tidak ditemukan di sumber dokumen.']);
            }

            if ($qtyKirim > $sourceItems[$partNo]['qty_sisa']) {
                throw ValidationException::withMessages(["items.{$index}.qty_kirim" => "Qty kirim tidak boleh melebihi qty sisa ({$sourceItems[$partNo]['qty_sisa']})."]);
            }

            $itemsToCreate[] = [
                'part_no' => $partNo,
                'deskripsi' => $sourceItems[$partNo]['deskripsi'],
                'qty_kirim' => $qtyKirim,
                'berat' => $item['berat'] ?? 0,
                'volume' => $item['volume'] ?? 0,
                'dimensi' => $item['dimensi'] ?? null,
                'sku' => $item['sku'] ?? null,
            ];
        }

        if ($itemsToCreate === []) {
            throw ValidationException::withMessages(['items' => 'Minimal 1 item harus memiliki qty kirim > 0.']);
        }

        return $itemsToCreate;
    }

    /**
     * @return array<string, array{deskripsi: string, qty_sisa: int}>
     */
    private function sourceItemsByPartNo(Model $spbAble): array
    {
        if ($spbAble instanceof WipOrder) {
            $spbAble->loadMissing('items');
            $qtyTerkirim = Spb::getQtyTerkirimGrouped(WipOrder::class, $spbAble->id);

            return $spbAble->items
                ->mapWithKeys(fn ($item): array => [
                    $item->part_no => [
                        'deskripsi' => $item->deskripsi,
                        'qty_sisa' => max(0, $item->qty - ($qtyTerkirim[$item->part_no] ?? 0)),
                    ],
                ])->toArray();
        }

        if ($spbAble instanceof PurchaseOrder) {
            $spbAble->loadMissing('items.katalog');
            $qtyTerkirim = Spb::getQtyTerkirimGrouped(PurchaseOrder::class, $spbAble->id);

            return $spbAble->items
                ->mapWithKeys(function ($item) use ($qtyTerkirim): array {
                    $partNo = $item->katalog?->part_no ?? '';

                    return [
                        $partNo => [
                            'deskripsi' => $item->deskripsi,
                            'qty_sisa' => max(0, $item->qty - ($qtyTerkirim[$partNo] ?? 0)),
                        ],
                    ];
                })->toArray();
        }

        return [];
    }
}
