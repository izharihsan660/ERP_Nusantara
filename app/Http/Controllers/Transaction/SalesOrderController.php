<?php

namespace App\Http\Controllers\Transaction;

use App\Helpers\FileCompressionHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\SalesOrder\StoreSalesOrderRequest;
use App\Http\Requests\SalesOrder\VoidSalesOrderRequest;
use App\Models\Quotation;
use App\Models\SalesOrder;
use App\Models\SalesOrderDocument;
use App\Services\SalesOrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

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

    public function uploadDokumen(Request $request, SalesOrder $salesOrder): RedirectResponse
    {
        $validated = $request->validate([
            'documents' => ['required', 'array', 'min:1', 'max:3'],
            'documents.*' => ['file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
        ]);

        foreach ($validated['documents'] as $file) {
            $path = $file->store('sales-order-docs', 'local');
            FileCompressionHelper::compress(Storage::disk('local')->path($path));

            $salesOrder->documents()->create([
                'file_path' => $path,
                'nama_file' => str($file->getClientOriginalName())->limit(100, '')->toString(),
            ]);
        }

        return back()->with('success', 'Dokumen PO Customer berhasil diupload.');
    }

    public function downloadDokumen(SalesOrderDocument $document): BinaryFileResponse
    {
        abort_unless(Storage::disk('local')->exists($document->file_path), 404);

        return response()->download(Storage::disk('local')->path($document->file_path), $document->nama_file);
    }
}
