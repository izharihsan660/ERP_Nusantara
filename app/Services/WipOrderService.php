<?php

namespace App\Services;

use App\Actions\ActivityLog\RecordActivity;
use App\Enums\SalesOrderStatus;
use App\Enums\StatusSupply;
use App\Enums\TipeOrder;
use App\Enums\WIPStatus;
use App\Models\SalesOrder;
use App\Models\User;
use App\Models\WipOrder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WipOrderService
{
    public function __construct(
        private readonly RecordActivity $recordActivity,
    ) {}

    public function create(array $data, SalesOrder $salesOrder, User $user): WipOrder
    {
        if ($salesOrder->status !== SalesOrderStatus::Open) {
            throw ValidationException::withMessages(['sales_order_id' => 'WIP hanya bisa dibuat dari Sales Order Open.']);
        }

        if (($data['tipe_order'] ?? null) === TipeOrder::VOR->value && blank($data['nama_ekspedisi'] ?? null)) {
            throw ValidationException::withMessages(['nama_ekspedisi' => 'Nama ekspedisi wajib diisi untuk tipe VOR.']);
        }

        $itemsToCreate = $this->validateItems($data['items'] ?? [], $salesOrder);

        return DB::transaction(function () use ($data, $itemsToCreate, $salesOrder, $user): WipOrder {
            $wip = WipOrder::create([
                'sales_order_id' => $salesOrder->id,
                'no_wip' => $data['no_wip'],
                'tipe_order' => $data['tipe_order'],
                'nama_ekspedisi' => ($data['tipe_order'] ?? null) === TipeOrder::VOR->value ? $data['nama_ekspedisi'] : null,
                'status_supply' => StatusSupply::BelumTersupply,
                'status' => WIPStatus::Active,
                'created_by' => $user->id,
            ]);

            foreach ($itemsToCreate as $item) {
                $wip->items()->create($item);
            }

            $this->recordActivity->handle('created_wip', $wip, "{$user->name} input WIP {$wip->no_wip}");

            return $wip->load(['salesOrder', 'items']);
        });
    }

    public function void(WipOrder $wip, string $alasan, User $user): WipOrder
    {
        if (! $wip->isVoidable()) {
            throw ValidationException::withMessages(['status' => 'WIP tidak bisa divoid jika sudah Tersupply atau bukan Active.']);
        }

        $wip->update([
            'status' => WIPStatus::Void,
            'alasan_void' => $alasan,
        ]);

        $this->recordActivity->handle('voided_wip', $wip, "{$user->name} void WIP {$wip->no_wip}");

        return $wip->refresh();
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @return array<int, array<string, mixed>>
     */
    private function validateItems(array $items, SalesOrder $salesOrder): array
    {
        $salesOrder->loadMissing(['quotation.items', 'wipOrders.items']);
        $quotationItems = $salesOrder->quotation->items->keyBy('part_no');
        $usedQty = [];

        foreach ($salesOrder->wipOrders as $wipOrder) {
            if ($wipOrder->status === WIPStatus::Void) {
                continue;
            }

            foreach ($wipOrder->items as $wipItem) {
                $usedQty[$wipItem->part_no] = ($usedQty[$wipItem->part_no] ?? 0) + (int) $wipItem->qty;
            }
        }

        $itemsToCreate = [];

        foreach ($items as $index => $item) {
            $partNo = $item['part_no'] ?? '';
            $qty = (int) ($item['qty'] ?? 0);
            $quotationItem = $quotationItems->get($partNo);

            if (! $quotationItem) {
                throw ValidationException::withMessages(["items.{$index}.part_no" => 'Item tidak ada di quotation terkait.']);
            }

            $remainingQty = max(0, (int) $quotationItem->qty - ($usedQty[$partNo] ?? 0));

            if ($qty > $remainingQty) {
                throw ValidationException::withMessages(["items.{$index}.qty" => "Qty WIP tidak boleh melebihi sisa ({$remainingQty})."]);
            }

            $itemsToCreate[] = [
                'katalog_id' => $item['katalog_id'] ?? $quotationItem->katalog_id,
                'part_no' => $partNo,
                'deskripsi' => $quotationItem->deskripsi,
                'qty' => $qty,
            ];
        }

        if ($itemsToCreate === []) {
            throw ValidationException::withMessages(['items' => 'Minimal 1 item harus dipilih untuk WIP.']);
        }

        return $itemsToCreate;
    }
}
