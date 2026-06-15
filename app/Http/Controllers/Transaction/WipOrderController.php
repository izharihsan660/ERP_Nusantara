<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use App\Http\Requests\WipOrder\StoreWipOrderRequest;
use App\Http\Requests\WipOrder\VoidWipOrderRequest;
use App\Models\SalesOrder;
use App\Models\WipOrder;
use App\Services\WipOrderService;
use Illuminate\Http\RedirectResponse;

class WipOrderController extends Controller
{
    public function __construct(
        private readonly WipOrderService $wipOrderService,
    ) {}

    public function store(StoreWipOrderRequest $request, SalesOrder $salesOrder): RedirectResponse
    {
        $this->wipOrderService->create($request->validated(), $salesOrder, $request->user());

        return to_route('quotations.show', $salesOrder->quotation)->with('success', 'WIP berhasil diinput.');
    }

    public function void(VoidWipOrderRequest $request, WipOrder $wipOrder): RedirectResponse
    {
        $this->wipOrderService->void($wipOrder, $request->validated('alasan_void'), $request->user());

        return to_route('quotations.show', $wipOrder->salesOrder->quotation)->with('success', 'WIP berhasil divoid.');
    }
}
