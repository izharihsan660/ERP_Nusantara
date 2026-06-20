<?php

namespace App\Actions\DocumentTemplate;

use App\Actions\ActivityLog\RecordActivity;
use App\Models\DocumentTemplate;
use Illuminate\Http\Request;

class DeleteDocumentTemplate
{
    public function __construct(private readonly RecordActivity $recordActivity) {}

    public function handle(DocumentTemplate $template, Request $request): void
    {
        $this->recordActivity->handle('deleted_template', $template, "Menghapus template {$template->kode_template}", $request);

        $template->delete();
    }
}
