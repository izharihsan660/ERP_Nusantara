<?php

namespace App\Services;

use App\Enums\DocumentType;
use App\Models\DocumentTemplate;
use App\Models\Spb;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;

class SpbPDFService
{
    public function generate(Spb $spb): string
    {
        $spb->loadMissing(['customer', 'site', 'template', 'items', 'createdBy', 'spbAble']);

        $view = $this->viewName($spb);
        $pdf = Pdf::loadView($view, [
            'spb' => $spb,
        ])->setPaper('a4');

        $path = $this->path($spb);
        Storage::disk('local')->put($path, $pdf->output());

        return $path;
    }

    public function path(Spb $spb): string
    {
        return 'spb/'.$this->fileName($spb);
    }

    private function viewName(Spb $spb): string
    {
        $blade = $spb->template?->blade_file
            ?? DocumentTemplate::query()
                ->where('tipe_dokumen', DocumentType::Spb->value)
                ->where('is_default', true)
                ->value('blade_file');

        if ($blade && View::exists($blade)) {
            return $blade;
        }

        return 'pdf.spb.default';
    }

    private function fileName(Spb $spb): string
    {
        $number = Str::of($spb->no_spb)->replace('/', '-')->slug('-');

        return "{$spb->id}-{$number}.pdf";
    }
}
