<?php

namespace App\Http\Controllers\Transaction;

use App\Enums\InvoiceStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Invoice\StoreInvoiceRequest;
use App\Http\Requests\Invoice\UpdatePembayaranRequest;
use App\Http\Requests\Invoice\UploadTtdRequest;
use App\Http\Requests\Invoice\VoidInvoiceRequest;
use App\Models\Invoice;
use App\Models\InvoicePaymentDocument;
use App\Models\PurchaseOrder;
use App\Models\Spb;
use App\Models\WipOrder;
use App\Services\InvoicePDFService;
use App\Services\InvoiceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class InvoiceController extends Controller
{
    public function __construct(
        private readonly InvoiceService $invoiceService,
        private readonly InvoicePDFService $invoicePDFService,
    ) {}

    public function store(StoreInvoiceRequest $request, Spb $spb): RedirectResponse
    {
        $invoice = $this->invoiceService->create($request->validated(), $spb, $request->user());

        return $this->redirectToParent($invoice->spb)->with('success', 'Invoice/Nota berhasil dibuat.');
    }

    public function updatePembayaran(UpdatePembayaranRequest $request, Invoice $invoice): RedirectResponse
    {
        $invoice = $this->invoiceService->updatePembayaran($invoice, $request->validated(), $request->user());

        return $this->redirectToParent($invoice->spb)->with('success', 'Pembayaran invoice berhasil diperbarui.');
    }

    public function uploadTtd(UploadTtdRequest $request, Invoice $invoice): RedirectResponse
    {
        $invoice = $this->invoiceService->uploadTtd($invoice, [
            'file_spb' => $request->file('file_spb'),
            'file_invoice' => $request->file('file_invoice'),
            'file_tanda_terima' => $request->file('file_tanda_terima'),
        ], $request->user());

        return $this->redirectToParent($invoice->spb)->with('success', 'File TTD berhasil digabung.');
    }

    public function void(VoidInvoiceRequest $request, Invoice $invoice): RedirectResponse
    {
        $invoice = $this->invoiceService->void($invoice, $request->validated('alasan_void'), $request->user());

        return $this->redirectToParent($invoice->spb)->with('success', 'Invoice/Nota berhasil divoid.');
    }

    public function download(Invoice $invoice, string $tipe): BinaryFileResponse
    {
        abort_if($invoice->status === InvoiceStatus::Void, 403);

        $path = match ($tipe) {
            'invoice' => $this->invoicePDFService->path($invoice, 'invoice'),
            'faktur' => $this->invoicePDFService->path($invoice, 'faktur'),
            'tanda-terima' => $this->invoicePDFService->path($invoice, 'tanda-terima'),
            'gabungan' => $invoice->file_ttd_gabungan,
            default => abort(404),
        };

        abort_if(! $path, 404);

        if ($tipe !== 'gabungan' && ! Storage::disk('local')->exists($path)) {
            $this->invoicePDFService->generateAll($invoice);
        }

        abort_unless(Storage::disk('local')->exists($path), 404);

        $fileName = str_replace('/', '-', $invoice->no_dokumen).'-'.$tipe.'.pdf';

        return response()->file(Storage::disk('local')->path($path), [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$fileName.'"',
        ]);
    }

    public function downloadPaymentDocument(InvoicePaymentDocument $document): BinaryFileResponse
    {
        abort_if($document->invoice?->status === InvoiceStatus::Void, 403);
        abort_unless(Storage::disk('local')->exists($document->file_path), 404);

        return response()->download(Storage::disk('local')->path($document->file_path), $document->nama_file);
    }

    private function redirectToParent(Spb $spb): RedirectResponse
    {
        $spbAble = $spb->spbAble;

        if ($spbAble instanceof WipOrder) {
            return to_route('quotations.show', $spbAble->salesOrder->quotation_id);
        }

        if ($spbAble instanceof PurchaseOrder) {
            return to_route('purchase-orders.show', $spbAble);
        }

        return to_route('dashboard');
    }
}
