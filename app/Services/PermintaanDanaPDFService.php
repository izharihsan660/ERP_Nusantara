<?php

namespace App\Services;

use App\Models\PermintaanDana;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class PermintaanDanaPDFService
{
    public function generate(PermintaanDana $permintaanDana): string
    {
        $permintaanDana->loadMissing(['createdBy', 'approvedBy']);

        $verifyUrl = route('verify.quotation', $permintaanDana->qr_token);
        $qrCode = base64_encode(QrCode::format('svg')->size(120)->margin(1)->generate($verifyUrl));

        $pdf = Pdf::loadView('pdf.pd.default', [
            'permintaanDana' => $permintaanDana,
            'qrCode' => "data:image/svg+xml;base64,{$qrCode}",
            'verifyUrl' => $verifyUrl,
        ])->setPaper('a4')->setOptions(['enable_compression' => true]);

        $path = $this->path($permintaanDana);
        Storage::disk('local')->put($path, $pdf->output());

        return $path;
    }

    public function path(PermintaanDana $permintaanDana): string
    {
        return 'pd/'.$this->fileName($permintaanDana);
    }

    private function fileName(PermintaanDana $permintaanDana): string
    {
        $number = Str::of($permintaanDana->no_pd)->replace('/', '-')->slug('-');

        return "{$permintaanDana->id}-{$number}.pdf";
    }
}
