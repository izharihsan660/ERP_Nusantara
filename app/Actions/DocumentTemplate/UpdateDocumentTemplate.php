<?php

namespace App\Actions\DocumentTemplate;

use App\Actions\ActivityLog\RecordActivity;
use App\Models\DocumentTemplate;
use Illuminate\Http\Request;

class UpdateDocumentTemplate
{
    public function __construct(private readonly RecordActivity $recordActivity) {}

    public function handle(DocumentTemplate $template, array $data, Request $request): DocumentTemplate
    {
        $template->update($data);

        $this->recordActivity->handle('updated_template', $template, "Mengubah template {$template->kode_template}", $request);

        return $template->refresh();
    }
}
