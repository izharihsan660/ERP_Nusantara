<?php

namespace App\Observers;

use App\Actions\ActivityLog\RecordActivity;
use App\Enums\ReferensiTipe;
use App\Models\SalesOrder;
use App\Models\Spb;
use App\Models\WipOrder;

class SalesOrderObserver
{
    public function __construct(
        private readonly RecordActivity $recordActivity,
    ) {}

    public function updated(SalesOrder $salesOrder): void
    {
        if (! $salesOrder->wasChanged('no_po_customer') || blank($salesOrder->no_po_customer)) {
            return;
        }

        $wipIds = $salesOrder->wipOrders()->pluck('id');

        if ($wipIds->isEmpty()) {
            return;
        }

        Spb::query()
            ->where('spb_able_type', WipOrder::class)
            ->whereIn('spb_able_id', $wipIds)
            ->where('referensi_tipe', ReferensiTipe::PR->value)
            ->get()
            ->each(function (Spb $spb) use ($salesOrder): void {
                $spb->update([
                    'referensi_tipe' => ReferensiTipe::PO,
                    'no_referensi' => $salesOrder->no_po_customer,
                ]);

                $this->recordActivity->handle('updated_referensi_spb', $spb, "Referensi SPB {$spb->no_spb} otomatis berubah ke PO {$salesOrder->no_po_customer}");
            });
    }
}
