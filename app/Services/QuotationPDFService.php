<?php

namespace App\Services;

use App\Models\Quotation;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QuotationPDFService
{
    public function generate(Quotation $quotation): string
    {
        $quotation->loadMissing(['customer', 'template', 'items', 'approvedBy']);

        $view = $this->resolveView($quotation);
        $verifyUrl = null;
        $qrCode = null;

        if ($quotation->status->value === 'APPROVED' && $quotation->qr_token) {
            $verifyUrl = route('verify.quotation', $quotation->qr_token);
            $qrCode = base64_encode(QrCode::format('svg')->size(120)->margin(1)->generate($verifyUrl));
        }
        $signaturePath = null;

        if ($quotation->status->value === 'APPROVED' && $quotation->approvedBy?->signature_path) {
            $absoluteSignaturePath = storage_path('app/private/'.$quotation->approvedBy->signature_path);

            if (file_exists($absoluteSignaturePath)) {
                $signaturePath = $absoluteSignaturePath;
            }
        }

        $masaBerlaku = $quotation->masa_berlaku
            ? $quotation->masa_berlaku->translatedFormat('d F Y')
            : ($quotation->tgl_quotation?->copy()->addMonths(6)->translatedFormat('d F Y') ?? '-');

        $pdf = Pdf::loadView($view, [
            'quotation' => $quotation,
            'qrCode' => $qrCode ? "data:image/svg+xml;base64,{$qrCode}" : null,
            'verifyUrl' => $verifyUrl,
            'perihal' => $quotation->perihal,
            'metode_pembayaran' => $quotation->metode_pembayaran,
            'masa_berlaku' => $masaBerlaku,
            'signaturePath' => $signaturePath,
        ])->setPaper('a4')->setOptions(['enable_compression' => true]);

        $path = 'quotations/'.$this->fileName($quotation);
        Storage::disk('local')->put($path, $pdf->output());

        return $path;
    }

    public function path(Quotation $quotation): string
    {
        return 'quotations/'.$this->fileName($quotation);
    }

    private function resolveView(Quotation $quotation): string
    {
        $bladeFile = $quotation->template?->blade_file;

        if ($bladeFile) {
            $view = str($bladeFile)
                ->replace('/', '.')
                ->replace('\\', '.')
                ->replace('.blade.php', '')
                ->replace('.php', '')
                ->trim('.')
                ->toString();

            if (view()->exists($view)) {
                return $view;
            }
        }

        return 'pdf.quotation.default';
    }

    private function fileName(Quotation $quotation): string
    {
        $number = Str::of($quotation->no_quotation)->replace('/', '-')->slug('-');

        return "{$quotation->id}-{$number}.pdf";
    }
}
