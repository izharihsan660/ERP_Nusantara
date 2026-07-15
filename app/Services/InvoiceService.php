<?php

namespace App\Services;

use App\Actions\ActivityLog\RecordActivity;
use App\Enums\InvoiceStatus;
use App\Enums\MetodePembayaran;
use App\Enums\SpbStatus;
use App\Enums\StatusPembayaran;
use App\Enums\TipeDokumen;
use App\Helpers\FileCompressionHelper;
use App\Models\Invoice;
use App\Models\Spb;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Jurosh\PDFMerge\PDFMerger;
use RuntimeException;

class InvoiceService
{
    public function __construct(
        private readonly DocumentNumberService $documentNumberService,
        private readonly InvoicePDFService $invoicePDFService,
        private readonly RecordActivity $recordActivity,
    ) {}

    public function create(array $data, Spb $spb, User $user): Invoice
    {
        return DB::transaction(function () use ($data, $spb, $user): Invoice {
            $spb = Spb::query()->whereKey($spb->id)->lockForUpdate()->firstOrFail();
            $spb->loadMissing(['customer', 'invoice', 'items', 'spbAble']);

            if (! in_array($spb->status, [SpbStatus::Shipped, SpbStatus::Draft], true)) {
                throw ValidationException::withMessages(['spb_id' => 'Invoice/Nota hanya bisa dibuat dari SPB Draft atau Shipped.']);
            }

            if ($spb->invoice()->where('status', '!=', InvoiceStatus::Void->value)->exists()) {
                throw ValidationException::withMessages(['spb_id' => 'SPB ini sudah memiliki Invoice/Nota aktif.']);
            }

            $date = Carbon::parse($data['tgl_dokumen']);
            $metodePembayaran = MetodePembayaran::from($data['metode_pembayaran']);
            $topHari = $metodePembayaran === MetodePembayaran::TOP ? (int) $data['top_hari'] : null;
            try {
                $totals = $this->calculateTotals($spb);
            } catch (RuntimeException $exception) {
                throw ValidationException::withMessages(['items' => $exception->getMessage()]);
            }

            $invoice = Invoice::create([
                'no_dokumen' => $this->documentNumberService->generateInvoiceNumber($date),
                'tipe_dokumen' => $metodePembayaran === MetodePembayaran::TOP ? TipeDokumen::Invoice : TipeDokumen::NotaPenjualan,
                'tgl_dokumen' => $date,
                'spb_id' => $spb->id,
                'customer_id' => $spb->customer_id,
                'no_faktur_pajak' => $data['no_faktur_pajak'] ?? null,
                'total_nilai' => $totals['total_nilai'],
                'ppn' => $totals['ppn'],
                'grand_total' => $totals['grand_total'],
                'total_hpp' => $totals['total_hpp'],
                'total_profit' => $totals['total_profit'],
                'metode_pembayaran' => $metodePembayaran,
                'top_hari' => $topHari,
                'tgl_jatuh_tempo' => $metodePembayaran === MetodePembayaran::TOP ? $date->copy()->addDays($topHari) : null,
                'status_pembayaran' => StatusPembayaran::Belum,
                'jumlah_bayar' => 0,
                'status' => InvoiceStatus::Active,
                'created_by' => $user->id,
            ]);

            $this->invoicePDFService->generateAll($invoice);
            $this->recordActivity->handle('created_invoice', $invoice, "{$user->name} membuat Invoice/Nota {$invoice->no_dokumen}");

            return $invoice->refresh()->load(['customer', 'spb.items', 'createdBy']);
        });
    }

    public function updatePembayaran(Invoice $invoice, array $data, User $user): Invoice
    {
        if ($invoice->status === InvoiceStatus::Void) {
            throw ValidationException::withMessages(['status' => 'Invoice/Nota yang sudah Void tidak bisa diubah pembayarannya.']);
        }

        return DB::transaction(function () use ($invoice, $data, $user): Invoice {
            $pembayaranKaliIni = (float) $data['jumlah_bayar'];

            if ($pembayaranKaliIni <= 0) {
                throw ValidationException::withMessages(['jumlah_bayar' => 'Jumlah pembayaran harus lebih dari 0.']);
            }

            $jumlahBayar = (float) $invoice->jumlah_bayar + $pembayaranKaliIni;
            $grandTotal = (float) $invoice->grand_total;

            if ($jumlahBayar > $grandTotal) {
                $sisaTagihan = max($grandTotal - (float) $invoice->jumlah_bayar, 0);
                throw ValidationException::withMessages(['jumlah_bayar' => 'Pembayaran melebihi sisa tagihan. Maksimal pembayaran kali ini Rp '.number_format($sisaTagihan, 0, ',', '.').'.']);
            }

            $invoice->update([
                'tgl_bayar' => $data['tgl_bayar'],
                'jumlah_bayar' => $jumlahBayar,
                'status_pembayaran' => match (true) {
                    $jumlahBayar >= $grandTotal => StatusPembayaran::Lunas,
                    $jumlahBayar > 0 => StatusPembayaran::Sebagian,
                    default => StatusPembayaran::Belum,
                },
            ]);

            foreach (($data['documents'] ?? []) as $index => $document) {
                $file = $document['file'] ?? null;

                if (! $file instanceof UploadedFile) {
                    throw ValidationException::withMessages(["documents.{$index}.file" => 'File dokumen pembayaran tidak valid.']);
                }

                $path = $this->storeCompressedFile($file, 'invoice-payment-documents');

                $invoice->paymentDocuments()->create([
                    'tipe_dokumen' => $document['tipe_dokumen'],
                    'file_path' => $path,
                    'nama_file' => str($file->getClientOriginalName())->limit(100, '')->toString(),
                ]);
            }

            $this->recordActivity->handle('updated_pembayaran_invoice', $invoice, "{$user->name} update pembayaran Invoice/Nota {$invoice->no_dokumen}");

            return $invoice->refresh()->load('paymentDocuments');
        });
    }

    /**
     * @param  array<string, UploadedFile>  $files
     */
    public function uploadTtd(Invoice $invoice, array $files, User $user): Invoice
    {
        if ($invoice->status === InvoiceStatus::Void) {
            throw ValidationException::withMessages(['status' => 'Invoice/Nota yang sudah Void tidak bisa upload TTD.']);
        }

        $paths = [
            $this->prepareMergeFile($files['file_spb'], 'spb'),
            $this->prepareMergeFile($files['file_invoice'], 'invoice'),
            $this->prepareMergeFile($files['file_tanda_terima'], 'tanda-terima'),
        ];

        $directory = 'ttd-gabungan';
        Storage::disk('local')->makeDirectory($directory);
        $path = $directory.'/'.$invoice->id.'-'.Str::uuid().'.pdf';
        $outputPath = Storage::disk('local')->path($path);

        $merger = new PDFMerger;

        foreach ($paths as $filePath) {
            $merger->addPDF($filePath, 'all');
        }

        $merger->merge('file', $outputPath);

        $invoice->update(['file_ttd_gabungan' => $path]);

        $this->recordActivity->handle('uploaded_ttd_invoice', $invoice, "{$user->name} upload TTD gabungan Invoice/Nota {$invoice->no_dokumen}");

        return $invoice->refresh();
    }

    public function void(Invoice $invoice, string $alasan, User $user): Invoice
    {
        if ($invoice->status === InvoiceStatus::Void) {
            throw ValidationException::withMessages(['status' => 'Invoice/Nota sudah berstatus Void.']);
        }

        if ($invoice->status_pembayaran !== StatusPembayaran::Belum || (float) $invoice->jumlah_bayar > 0) {
            throw ValidationException::withMessages(['status_pembayaran' => 'Invoice/Nota tidak bisa divoid karena sudah memiliki pembayaran.']);
        }

        $invoice->update([
            'status' => InvoiceStatus::Void,
            'voided_by' => $user->id,
            'voided_at' => now(),
            'alasan_void' => $alasan,
        ]);

        $this->recordActivity->handle('voided_invoice', $invoice, "{$user->name} void Invoice/Nota {$invoice->no_dokumen}");

        return $invoice->refresh();
    }

    /**
     * @return array{total_nilai: float, ppn: float, grand_total: float, total_hpp: float, total_profit: float}
     */
    private function calculateTotals(Spb $spb): array
    {
        $totals = $this->invoicePDFService->itemsForSpb($spb)
            ->reduce(function (array $carry, array $item): array {
                $carry['total_nilai'] += $item['jumlah'];
                $carry['total_hpp'] += $item['total_hpp'];
                $carry['total_profit'] += $item['profit'];

                return $carry;
            }, ['total_nilai' => 0.0, 'total_hpp' => 0.0, 'total_profit' => 0.0]);

        $totals['ppn'] = round($totals['total_nilai'] * 0.11);
        $totals['grand_total'] = $totals['total_nilai'] + $totals['ppn'];

        return $totals;
    }

    private function prepareMergeFile(UploadedFile $file, string $prefix): string
    {
        $directory = 'tmp/invoice-upload';
        Storage::disk('local')->makeDirectory($directory);
        $storedPath = $file->store($directory, 'local');
        $absolutePath = Storage::disk('local')->path($storedPath);
        FileCompressionHelper::compress($absolutePath);

        if (strtolower($file->getClientOriginalExtension()) === 'pdf') {
            return $absolutePath;
        }

        $data = base64_encode(file_get_contents($absolutePath));
        $mime = mime_content_type($absolutePath) ?: $file->getMimeType();
        $pdf = Pdf::loadHTML(
            '<html><body style="margin:0"><img src="data:'.$mime.';base64,'.$data.'" style="width:100%;height:auto"></body></html>'
        )->setPaper('a4')->setOptions(['enable_compression' => true]);

        $path = $directory.'/'.$prefix.'-'.Str::uuid().'.pdf';
        Storage::disk('local')->put($path, $pdf->output());

        return Storage::disk('local')->path($path);
    }

    private function storeCompressedFile(UploadedFile $file, string $directory): string
    {
        $path = $file->store($directory, 'local');
        FileCompressionHelper::compress(Storage::disk('local')->path($path));

        return $path;
    }
}
