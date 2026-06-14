<?php

namespace App\Services;

use App\Actions\ActivityLog\RecordActivity;
use App\Enums\POStatus;
use App\Enums\StatusSupply;
use App\Enums\TipeOrder;
use App\Enums\WIPStatus;
use App\Models\PurchaseOrder;
use App\Models\User;
use App\Models\WipOrder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WipOrderService
{
    public function __construct(
        private readonly RecordActivity $recordActivity,
    ) {}

    public function create(array $data, PurchaseOrder $po, User $user): WipOrder
    {
        if ($po->status !== POStatus::Open) {
            throw ValidationException::withMessages(['purchase_order_id' => 'WIP hanya bisa dibuat dari PO Customer Open.']);
        }

        if (($data['tipe_order'] ?? null) === TipeOrder::VOR->value && blank($data['nama_ekspedisi'] ?? null)) {
            throw ValidationException::withMessages(['nama_ekspedisi' => 'Nama ekspedisi wajib diisi untuk tipe VOR.']);
        }

        return DB::transaction(function () use ($data, $po, $user): WipOrder {
            $wip = WipOrder::create([
                'purchase_order_id' => $po->id,
                'no_wip' => $data['no_wip'],
                'tipe_order' => $data['tipe_order'],
                'nama_ekspedisi' => ($data['tipe_order'] ?? null) === TipeOrder::VOR->value ? $data['nama_ekspedisi'] : null,
                'status_supply' => StatusSupply::BelumTersupply,
                'status' => WIPStatus::Active,
                'created_by' => $user->id,
            ]);

            $this->recordActivity->handle('WIP buat', $wip, "{$user->name} input WIP {$wip->no_wip}");

            return $wip->load('purchaseOrder');
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

        $this->recordActivity->handle('WIP void', $wip, "{$user->name} void WIP {$wip->no_wip}");

        return $wip->refresh();
    }
}
