<?php

namespace App\Services;

use App\Enums\TipeDokumen;
use App\Models\Invoice;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\QuotationItem;
use App\Models\Spb;
use App\Models\SpbItem;
use App\Models\WipOrder;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class InvoicePDFService
{
    public function generateAll(Invoice $invoice): array
    {
        return [
            'invoice' => $this->generateInvoice($invoice),
            'faktur' => $this->generateFakturPajak($invoice),
            'tanda-terima' => $this->generateTandaTerima($invoice),
        ];
    }

    public function generateInvoice(Invoice $invoice): string
    {
        $invoice->loadMissing(['customer', 'spb.items', 'spb.spbAble']);

        $view = $invoice->tipe_dokumen === TipeDokumen::Invoice
            ? 'pdf.invoice.invoice'
            : 'pdf.invoice.nota';

        $pdf = Pdf::loadView($view, [
            'invoice' => $invoice,
            'items' => $this->items($invoice),
        ])->setPaper('a4')->setOptions(['enable_compression' => true]);

        $path = $this->path($invoice, 'invoice');
        Storage::disk('local')->put($path, $pdf->output());

        return $path;
    }

    public function generateFakturPajak(Invoice $invoice): string
    {
        $invoice->loadMissing(['customer', 'spb.items', 'spb.spbAble']);

        $pdf = Pdf::loadView('pdf.invoice.faktur-pajak', [
            'invoice' => $invoice,
            'items' => $this->items($invoice),
        ])->setPaper('a4')->setOptions(['enable_compression' => true]);

        $path = $this->path($invoice, 'faktur');
        Storage::disk('local')->put($path, $pdf->output());

        return $path;
    }

    public function generateTandaTerima(Invoice $invoice): string
    {
        $invoice->loadMissing(['customer', 'spb.items', 'spb.spbAble']);

        $pdf = Pdf::loadView('pdf.invoice.tanda-terima', [
            'invoice' => $invoice,
            'items' => $this->items($invoice),
        ])->setPaper('a4')->setOptions(['enable_compression' => true]);

        $path = $this->path($invoice, 'tanda-terima');
        Storage::disk('local')->put($path, $pdf->output());

        return $path;
    }

    public function path(Invoice $invoice, string $type): string
    {
        return "invoices/{$invoice->id}/{$this->fileName($invoice, $type)}";
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function items(Invoice $invoice): Collection
    {
        $invoice->loadMissing(['spb.items', 'spb.spbAble']);

        return $this->itemsForSpb($invoice->spb);
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function itemsForSpb(Spb $spb): Collection
    {
        $spb->loadMissing(['items', 'spbAble']);

        $source = $spb->spbAble;

        if ($source instanceof WipOrder) {
            $source->loadMissing('salesOrder.quotation.items');
        }

        if ($source instanceof PurchaseOrder) {
            $source->loadMissing('items.katalog');
        }

        $unmatchedItems = $spb->items->filter(fn (SpbItem $item): bool => $this->sourceItem($source, $item) === null);

        if ($unmatchedItems->isNotEmpty()) {
            $labels = $unmatchedItems->map(fn (SpbItem $item): string => trim(($item->part_no ? "{$item->part_no} - " : '').$item->deskripsi))->implode(', ');

            throw new RuntimeException("Item SPB tidak dapat dicocokkan dengan dokumen sumber: {$labels}.");
        }

        return $spb->items->map(function (SpbItem $item) use ($source): array {
            $sourceItem = $this->sourceItem($source, $item);
            $hargaSatuan = (float) $sourceItem->harga_satuan;
            $hppSatuan = match (true) {
                $sourceItem instanceof QuotationItem => (float) $sourceItem->hpp_satuan,
                $sourceItem instanceof PurchaseOrderItem && $sourceItem->katalog_id => (float) ($sourceItem->katalog?->hpp ?? 0),
                default => 0.0,
            };
            $jumlah = (float) $item->qty * $hargaSatuan;
            $totalHpp = (float) $item->qty * $hppSatuan;

            return [
                'part_no' => $item->part_no,
                'deskripsi' => $item->deskripsi,
                'qty' => (int) $item->qty,
                'harga_satuan' => $hargaSatuan,
                'jumlah' => $jumlah,
                'hpp_satuan' => $hppSatuan,
                'total_hpp' => $totalHpp,
                'profit' => $jumlah - $totalHpp,
            ];
        });
    }

    private function sourceItem(mixed $source, SpbItem $spbItem): mixed
    {
        if ($source instanceof WipOrder) {
            return $source->salesOrder?->quotation?->items
                ->first(fn (QuotationItem $item): bool => $item->part_no === $spbItem->part_no)
                ?? $source->salesOrder?->quotation?->items
                    ->first(fn (QuotationItem $item): bool => $item->deskripsi === $spbItem->deskripsi);
        }

        if ($source instanceof PurchaseOrder) {
            return $source->items
                ->first(fn (PurchaseOrderItem $item): bool => filled($spbItem->part_no) && $item->katalog?->part_no === $spbItem->part_no)
                ?? $source->items->first(fn (PurchaseOrderItem $item): bool => $item->deskripsi === $spbItem->deskripsi);
        }

        return null;
    }

    private function fileName(Invoice $invoice, string $type): string
    {
        $number = Str::of($invoice->no_dokumen)->replace('/', '-')->slug('-');

        return "{$type}-{$number}.pdf";
    }
}
