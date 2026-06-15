<?php

namespace App\Observers;

use App\Actions\ActivityLog\RecordActivity;
use App\Enums\ReferensiTipe;
use App\Models\PurchaseOrder;
use App\Models\Spb;

class PurchaseOrderObserver
{
    public function __construct(
        private readonly RecordActivity $recordActivity,
    ) {}

    public function updated(PurchaseOrder $purchaseOrder): void
    {
        if (! $purchaseOrder->wasChanged('no_po_customer') || blank($purchaseOrder->no_po_customer)) {
            return;
        }

        Spb::query()
            ->where('spb_able_type', PurchaseOrder::class)
            ->where('spb_able_id', $purchaseOrder->id)
            ->where('referensi_tipe', ReferensiTipe::PR->value)
            ->get()
            ->each(function (Spb $spb) use ($purchaseOrder): void {
                $spb->update([
                    'referensi_tipe' => ReferensiTipe::PO,
                    'no_referensi' => $purchaseOrder->no_po_customer,
                ]);

                $this->recordActivity->handle('updated_referensi_spb', $spb, "Referensi SPB {$spb->no_spb} otomatis berubah ke PO {$purchaseOrder->no_po_customer}");
            });
    }
}
