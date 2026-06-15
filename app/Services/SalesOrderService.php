<?php

namespace App\Services;

use App\Actions\ActivityLog\RecordActivity;
use App\Enums\MetodePembayaran;
use App\Enums\QuotationStatus;
use App\Enums\SalesOrderStatus;
use App\Models\Quotation;
use App\Models\SalesOrder;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SalesOrderService
{
    public function __construct(
        private readonly RecordActivity $recordActivity,
    ) {}

    public function create(array $data, Quotation $quotation, User $user): SalesOrder
    {
        if ($quotation->status !== QuotationStatus::Approved) {
            throw ValidationException::withMessages(['quotation_id' => 'Sales Order hanya bisa dibuat dari quotation Approved.']);
        }

        if ($quotation->salesOrder()->exists()) {
            throw ValidationException::withMessages(['quotation_id' => 'Quotation ini sudah memiliki Sales Order.']);
        }

        return DB::transaction(function () use ($data, $quotation, $user): SalesOrder {
            $salesOrder = SalesOrder::create([
                'quotation_id' => $quotation->id,
                'customer_id' => $quotation->customer_id,
                'no_po_customer' => $data['no_po_customer'],
                'no_pr_customer' => $data['no_pr_customer'] ?? null,
                'tgl_po' => $data['tgl_po'],
                'metode_pembayaran' => $data['metode_pembayaran'],
                'top_hari' => ($data['metode_pembayaran'] ?? null) === MetodePembayaran::TOP->value ? $data['top_hari'] : null,
                'status' => SalesOrderStatus::Open,
                'created_by' => $user->id,
            ]);

            $this->recordActivity->handle('input_sales_order', $salesOrder, "{$user->name} input Sales Order {$salesOrder->no_po_customer}");

            return $salesOrder->load(['quotation', 'customer']);
        });
    }

    public function void(SalesOrder $salesOrder, string $alasan, User $user): SalesOrder
    {
        if (! $salesOrder->isVoidable()) {
            throw ValidationException::withMessages(['status' => 'Sales Order tidak bisa divoid jika bukan Open atau masih memiliki WIP Active.']);
        }

        $salesOrder->update([
            'status' => SalesOrderStatus::Void,
            'alasan_void' => $alasan,
        ]);

        $this->recordActivity->handle('void_sales_order', $salesOrder, "{$user->name} void Sales Order {$salesOrder->no_po_customer}");

        return $salesOrder->refresh();
    }
}
