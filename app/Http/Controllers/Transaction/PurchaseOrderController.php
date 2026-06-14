<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use App\Http\Requests\PurchaseOrder\StorePurchaseOrderRequest;
use App\Http\Requests\PurchaseOrder\VoidPurchaseOrderRequest;
use App\Models\PurchaseOrder;
use App\Models\Quotation;
use App\Services\PurchaseOrderService;
use Illuminate\Http\RedirectResponse;

class PurchaseOrderController extends Controller
{
    public function __construct(
        private readonly PurchaseOrderService $purchaseOrderService,
    ) {}

    public function store(StorePurchaseOrderRequest $request, Quotation $quotation): RedirectResponse
    {
        $this->purchaseOrderService->create($request->validated(), $quotation, $request->user());

        return to_route('quotations.show', $quotation)->with('success', 'PO Customer berhasil diinput.');
    }

    public function void(VoidPurchaseOrderRequest $request, PurchaseOrder $purchaseOrder): RedirectResponse
    {
        $this->purchaseOrderService->void($purchaseOrder, $request->validated('alasan_void'), $request->user());

        return to_route('quotations.show', $purchaseOrder->quotation)->with('success', 'PO Customer berhasil divoid.');
    }
}
