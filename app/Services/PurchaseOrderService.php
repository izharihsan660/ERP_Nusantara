<?php

namespace App\Services;

use App\Actions\ActivityLog\RecordActivity;
use App\Enums\MetodePembayaran;
use App\Enums\POStatus;
use App\Enums\QuotationStatus;
use App\Models\PurchaseOrder;
use App\Models\Quotation;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PurchaseOrderService
{
    public function __construct(
        private readonly RecordActivity $recordActivity,
    ) {}

    public function create(array $data, Quotation $quotation, User $user): PurchaseOrder
    {
        if ($quotation->status !== QuotationStatus::Approved) {
            throw ValidationException::withMessages(['quotation_id' => 'PO Customer hanya bisa dibuat dari quotation Approved.']);
        }

        if ($quotation->purchaseOrder()->exists()) {
            throw ValidationException::withMessages(['quotation_id' => 'Quotation ini sudah memiliki PO Customer.']);
        }

        return DB::transaction(function () use ($data, $quotation, $user): PurchaseOrder {
            $po = PurchaseOrder::create([
                'quotation_id' => $quotation->id,
                'customer_id' => $quotation->customer_id,
                'no_po_customer' => $data['no_po_customer'],
                'no_pr_customer' => $data['no_pr_customer'] ?? null,
                'tgl_po' => $data['tgl_po'],
                'metode_pembayaran' => $data['metode_pembayaran'],
                'top_hari' => ($data['metode_pembayaran'] ?? null) === MetodePembayaran::TOP->value ? $data['top_hari'] : null,
                'status' => POStatus::Open,
                'created_by' => $user->id,
            ]);

            $this->recordActivity->handle('PO Customer input', $po, "{$user->name} input PO Customer {$po->no_po_customer}");

            return $po->load(['quotation', 'customer']);
        });
    }

    public function void(PurchaseOrder $po, string $alasan, User $user): PurchaseOrder
    {
        if (! $po->isVoidable()) {
            throw ValidationException::withMessages(['status' => 'PO Customer tidak bisa divoid jika bukan Open atau masih memiliki WIP Active.']);
        }

        $po->update([
            'status' => POStatus::Void,
            'alasan_void' => $alasan,
        ]);

        $this->recordActivity->handle('PO Customer void', $po, "{$user->name} void PO Customer {$po->no_po_customer}");

        return $po->refresh();
    }
}
