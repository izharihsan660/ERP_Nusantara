<?php

namespace App\Services;

use App\Enums\InvoiceStatus;
use App\Enums\PDStatus;
use App\Enums\PurchaseOrderStatus;
use App\Enums\QuotationStatus;
use App\Enums\SpbStatus;
use App\Enums\StatusSupply;
use App\Models\PermintaanDana;
use App\Models\PurchaseOrder;
use App\Models\Quotation;
use App\Models\Spb;
use App\Models\User;
use App\Models\WipOrder;

class SidebarBadgeService
{
    public static function getBadges(?User $user): array
    {
        if (! $user) {
            return [];
        }

        $badges = [];

        // Manager & Superadmin — pending approvals
        if ($user->hasAnyRole(['Manager', 'Superadmin'])) {
            $badges['quotation'] = Quotation::where('status', QuotationStatus::PendingApproval)->count();
            $badges['purchase_order'] = PurchaseOrder::where('status', PurchaseOrderStatus::PendingApproval)->count();
            $badges['permintaan_dana'] = PermintaanDana::where('status', PDStatus::PendingApproval)->count();
        }

        // Gudang — WIP tersupply belum dibuat SPB
        if ($user->hasAnyRole(['Gudang', 'Superadmin'])) {
            $badges['spb'] = WipOrder::where('status_supply', StatusSupply::Tersupply)
                ->whereDoesntHave('spb', fn ($q) => $q->where('status', '!=', SpbStatus::Void))
                ->count();
        }

        // Finance — SPB shipped belum dibuat Invoice
        if ($user->hasAnyRole(['Finance', 'Superadmin'])) {
            $badges['invoice'] = Spb::where('status', SpbStatus::Shipped)
                ->whereDoesntHave('invoice', fn ($q) => $q->where('status', '!=', InvoiceStatus::Void))
                ->count();
        }

        // Procurement — PD approved belum upload bukti
        if ($user->hasAnyRole(['Procurement', 'Superadmin'])) {
            $badges['permintaan_dana_procurement'] = PermintaanDana::where('status', PDStatus::Approved)
                ->whereDoesntHave('documents')
                ->count();
        }

        return $badges;
    }
}
