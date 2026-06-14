<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use App\Http\Requests\WipOrder\StoreWipOrderRequest;
use App\Http\Requests\WipOrder\VoidWipOrderRequest;
use App\Models\PurchaseOrder;
use App\Models\WipOrder;
use App\Services\WipOrderService;
use Illuminate\Http\RedirectResponse;

class WipOrderController extends Controller
{
    public function __construct(
        private readonly WipOrderService $wipOrderService,
    ) {}

    public function store(StoreWipOrderRequest $request, PurchaseOrder $purchaseOrder): RedirectResponse
    {
        $this->wipOrderService->create($request->validated(), $purchaseOrder, $request->user());

        return to_route('quotations.show', $purchaseOrder->quotation)->with('success', 'WIP berhasil diinput.');
    }

    public function void(VoidWipOrderRequest $request, WipOrder $wipOrder): RedirectResponse
    {
        $this->wipOrderService->void($wipOrder, $request->validated('alasan_void'), $request->user());

        return to_route('quotations.show', $wipOrder->purchaseOrder->quotation)->with('success', 'WIP berhasil divoid.');
    }
}
