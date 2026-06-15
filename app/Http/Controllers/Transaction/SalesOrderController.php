<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use App\Http\Requests\SalesOrder\StoreSalesOrderRequest;
use App\Http\Requests\SalesOrder\VoidSalesOrderRequest;
use App\Models\Quotation;
use App\Models\SalesOrder;
use App\Services\SalesOrderService;
use Illuminate\Http\RedirectResponse;

class SalesOrderController extends Controller
{
    public function __construct(
        private readonly SalesOrderService $salesOrderService,
    ) {}

    public function store(StoreSalesOrderRequest $request, Quotation $quotation): RedirectResponse
    {
        $this->salesOrderService->create($request->validated(), $quotation, $request->user());

        return to_route('quotations.show', $quotation)->with('success', 'Sales Order berhasil diinput.');
    }

    public function void(VoidSalesOrderRequest $request, SalesOrder $salesOrder): RedirectResponse
    {
        $this->salesOrderService->void($salesOrder, $request->validated('alasan_void'), $request->user());

        return to_route('quotations.show', $salesOrder->quotation)->with('success', 'Sales Order berhasil divoid.');
    }
}
