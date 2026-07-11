<?php

namespace App\Services;

use App\Enums\DocumentType;
use App\Enums\QuotationStatus;
use App\Models\DocumentTemplate;
use App\Models\Quotation;
use BaconQrCode\Common\ErrorCorrectionLevel;
use BaconQrCode\Encoder\Encoder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpWord\TemplateProcessor;

class QuotationPDFService
{
    public function __construct(private readonly GotenbergService $gotenbergService) {}

    public function generate(Quotation $quotation): string
    {
        $quotation->loadMissing(['customer', 'template', 'items', 'approvedBy']);

        $template = $this->resolveTemplate($quotation);
        $templatePath = $this->templatePath($template);
        $docxTempPath = $this->tempPath('quotation_', '.docx');
        $signatureTempPath = null;
        $qrTempPath = null;

        copy($templatePath, $docxTempPath);

        try {
            $processor = new TemplateProcessor($docxTempPath);
            $subtotal = (float) $quotation->items->sum('jumlah');
            $ppn = round($subtotal * 0.11);
            $grandTotal = $subtotal + $ppn;
            $documentDate = ($quotation->approved_at ?? $quotation->tgl_quotation)?->copy()->locale('id');
            $validUntil = ($quotation->masa_berlaku ?? $quotation->tgl_quotation?->copy()->addDays(14))?->locale('id');

            $processor->setValue('no_quotation', $this->escape($quotation->no_quotation));
            $processor->setValue('tanggal', $this->escape($documentDate?->translatedFormat('d F Y') ?? '-'));
            $processor->setValue('tgl_quotation', $this->escape($quotation->tgl_quotation?->translatedFormat('d F Y') ?? '-'));
            $processor->setValue('customer_name', $this->escape($quotation->customer?->nama_customer ?? '-'));
            $processor->setValue('customer_alamat', $this->escape($quotation->customer?->alamat ?: '-'));
            $processor->setValue('customer_kota', $this->escape($quotation->customer?->kota ?: '-'));
            $processor->setValue('revisi', (string) ($quotation->revisi ?? 0));
            $processor->setValue('masa_berlaku', $this->escape($validUntil?->translatedFormat('d F Y') ?? '-'));
            $processor->setValue('perihal', $this->escape($quotation->perihal ?? ''));
            $processor->setValue('metode_pembayaran', $this->escape($quotation->metode_pembayaran ?? '-'));
            $processor->setValue('subtotal', $this->formatRupiah($subtotal));
            $processor->setValue('ppn', $this->formatRupiah($ppn));
            $processor->setValue('grand_total', $this->formatRupiah($grandTotal));

            $items = $quotation->items->values();
            if ($items->isNotEmpty()) {
                $processor->cloneRow('item_no', $items->count());

                foreach ($items as $index => $item) {
                    $row = $index + 1;

                    $processor->setValue("item_no#{$row}", (string) $row);
                    $processor->setValue("item_part_no#{$row}", $this->escape($item->part_no ?? '-'));
                    $processor->setValue("item_deskripsi#{$row}", $this->escape($item->deskripsi ?? '-'));
                    $processor->setValue("item_qty#{$row}", (string) $item->qty);
                    $processor->setValue("item_satuan#{$row}", $this->escape($item->satuan ?? 'PCS'));
                    $processor->setValue("item_harga#{$row}", $this->formatRupiah((float) $item->harga_satuan));
                    $processor->setValue("item_total#{$row}", $this->formatRupiah((float) $item->jumlah));
                    $processor->setValue("item_status#{$row}", $this->escape($item->status ?? ''));
                }
            } else {
                $processor->cloneRow('item_no', 1);
                $processor->setValue('item_no#1', '0');
                $processor->setValue('item_part_no#1', '-');
                $processor->setValue('item_deskripsi#1', '-');
                $processor->setValue('item_qty#1', '0');
                $processor->setValue('item_satuan#1', '-');
                $processor->setValue('item_harga#1', '0');
                $processor->setValue('item_total#1', '0');
                $processor->setValue('item_status#1', '-');
            }

            $signer = $quotation->approvedBy;

            $processor->setValue('nama_manager', $this->escape($signer?->name ?? '-'));
            $processor->setValue('email_manager', $this->escape($signer?->email ?? '-'));

            $signaturePath = $signer?->signature_path
                ? storage_path('app/private/'.$signer->signature_path)
                : null;

            if ($quotation->status === QuotationStatus::Approved && $signaturePath && file_exists($signaturePath)) {
                $signatureTempPath = $this->processSignatureImage($signaturePath);

                if ($signatureTempPath) {
                    $processor->setImageValue('TTD', [
                        'path' => $signatureTempPath,
                        'width' => 100,
                        'height' => 40,
                        'ratio' => true,
                    ]);
                } else {
                    $processor->setValue('TTD', '');
                }
            } else {
                $processor->setValue('TTD', '');
            }

            if ($quotation->status === QuotationStatus::Approved && $quotation->qr_token) {
                $qrTempPath = $this->tempPath('qr_', '.png');
                $qrGenerated = $this->generateQrPng(url('/verify/'.$quotation->qr_token), $qrTempPath);

                if ($qrGenerated && file_exists($qrTempPath)) {
                    $processor->setImageValue('QR', [
                        'path' => $qrTempPath,
                        'width' => 25,
                        'height' => 25,
                        'ratio' => false,
                    ]);
                } else {
                    $processor->setValue('QR', '');
                }
            } else {
                $processor->setValue('QR', '');
            }

            $processor->saveAs($docxTempPath);

            $pdfContent = $this->gotenbergService->convertDocxToPdf($docxTempPath);
            $path = 'quotations/'.$this->fileName($quotation);
            Storage::disk('local')->put($path, $pdfContent);

            return $path;
        } finally {
            $this->deleteTempFile($docxTempPath);
            $this->deleteTempFile($signatureTempPath);
            $this->deleteTempFile($qrTempPath);
        }
    }

    public function path(Quotation $quotation): ?string
    {
        return $quotation->generated_pdf_path;
    }

    private function resolveTemplate(Quotation $quotation): DocumentTemplate
    {
        $template = $quotation->template;

        if (! $template?->docx_path) {
            $template = DocumentTemplate::query()
                ->where('tipe_dokumen', DocumentType::Quotation->value)
                ->where('is_default', true)
                ->first();
        }

        if (! $template?->docx_path) {
            throw new \RuntimeException('Template .docx quotation belum tersedia. Upload template .docx terlebih dahulu.');
        }

        return $template;
    }

    private function templatePath(DocumentTemplate $template): string
    {
        if (! Storage::disk('local')->exists($template->docx_path)) {
            throw new \RuntimeException('File template .docx quotation tidak ditemukan di storage. Upload ulang template .docx.');
        }

        return Storage::disk('local')->path($template->docx_path);
    }

    private function processSignatureImage(string $sourcePath): ?string
    {
        if (! file_exists($sourcePath)) {
            return null;
        }

        $info = @getimagesize($sourcePath);

        if (! $info) {
            return null;
        }

        $source = match ($info[2]) {
            IMAGETYPE_PNG => @imagecreatefrompng($sourcePath),
            IMAGETYPE_JPEG => @imagecreatefromjpeg($sourcePath),
            IMAGETYPE_GIF => @imagecreatefromgif($sourcePath),
            default => null,
        };

        if (! $source) {
            return null;
        }

        $originalWidth = imagesx($source);
        $originalHeight = imagesy($source);
        $maxWidth = 400;
        $maxHeight = 150;
        $ratio = min($maxWidth / $originalWidth, $maxHeight / $originalHeight, 1);
        $newWidth = max(1, (int) ($originalWidth * $ratio));
        $newHeight = max(1, (int) ($originalHeight * $ratio));
        $destination = imagecreatetruecolor($newWidth, $newHeight);

        if (! $destination) {
            imagedestroy($source);

            return null;
        }

        imagealphablending($destination, false);
        imagesavealpha($destination, true);

        $transparent = imagecolorallocatealpha($destination, 0, 0, 0, 127);
        imagefilledrectangle($destination, 0, 0, $newWidth, $newHeight, $transparent);
        imagecopyresampled($destination, $source, 0, 0, 0, 0, $newWidth, $newHeight, $originalWidth, $originalHeight);

        $tempPath = $this->tempPath('ttd_', '.png');
        imagepng($destination, $tempPath);

        imagedestroy($source);
        imagedestroy($destination);

        return $tempPath;
    }

    private function generateQrPng(string $content, string $path): bool
    {
        $matrix = Encoder::encode($content, ErrorCorrectionLevel::M(), 'UTF-8')->getMatrix();
        $moduleSize = 4;
        $quietZone = 4;
        $imageSize = ($matrix->getWidth() + ($quietZone * 2)) * $moduleSize;
        $image = imagecreatetruecolor($imageSize, $imageSize);

        if (! $image) {
            return false;
        }

        $white = imagecolorallocate($image, 255, 255, 255);
        $black = imagecolorallocate($image, 0, 0, 0);

        imagefill($image, 0, 0, $white);

        for ($row = 0; $row < $matrix->getHeight(); $row++) {
            for ($column = 0; $column < $matrix->getWidth(); $column++) {
                if ($matrix->get($column, $row) !== 1) {
                    continue;
                }

                $left = ($column + $quietZone) * $moduleSize;
                $top = ($row + $quietZone) * $moduleSize;
                imagefilledrectangle($image, $left, $top, $left + $moduleSize - 1, $top + $moduleSize - 1, $black);
            }
        }

        $saved = imagepng($image, $path);
        imagedestroy($image);

        return $saved;
    }

    private function formatRupiah(float $amount): string
    {
        return 'Rp '.number_format($amount, 0, ',', '.');
    }

    private function fileName(Quotation $quotation): string
    {
        $number = Str::of($quotation->no_quotation)->replace('/', '-')->slug('-');

        return "{$quotation->id}-{$number}.pdf";
    }

    private function tempPath(string $prefix, string $extension): string
    {
        $path = tempnam(sys_get_temp_dir(), $prefix);
        $targetPath = $path.$extension;
        rename($path, $targetPath);

        return $targetPath;
    }

    private function deleteTempFile(?string $path): void
    {
        if ($path && file_exists($path)) {
            unlink($path);
        }
    }

    private function escape(mixed $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_XML1, 'UTF-8');
    }
}
