<?php

namespace App\Actions\DocumentTemplate;

use App\Actions\ActivityLog\RecordActivity;
use App\Models\DocumentTemplate;
use Illuminate\Http\Request;

class CreateDocumentTemplate
{
    public function __construct(private readonly RecordActivity $recordActivity) {}

    public function handle(array $data, Request $request): DocumentTemplate
    {
        $template = DocumentTemplate::create($data);

        $this->recordActivity->handle('Template Dokumen tambah', $template, "Menambah template {$template->kode_template}", $request);

        return $template;
    }
}
