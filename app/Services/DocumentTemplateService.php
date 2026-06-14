<?php

namespace App\Services;

use App\Actions\DocumentTemplate\CreateDocumentTemplate;
use App\Actions\DocumentTemplate\DeleteDocumentTemplate;
use App\Actions\DocumentTemplate\UpdateDocumentTemplate;
use App\Models\DocumentTemplate;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class DocumentTemplateService
{
    public function __construct(
        private readonly CreateDocumentTemplate $createDocumentTemplate,
        private readonly UpdateDocumentTemplate $updateDocumentTemplate,
        private readonly DeleteDocumentTemplate $deleteDocumentTemplate,
    ) {}

    public function paginate(array $filters): LengthAwarePaginator
    {
        $sort = in_array($filters['sort'] ?? null, ['nama_template', 'kode_template', 'tipe_dokumen', 'created_at'], true)
            ? $filters['sort']
            : 'created_at';

        return DocumentTemplate::query()
            ->when($filters['search'] ?? null, function ($query, string $search): void {
                $query->where('nama_template', 'like', "%{$search}%")
                    ->orWhere('kode_template', 'like', "%{$search}%")
                    ->orWhere('blade_file', 'like', "%{$search}%");
            })
            ->when($filters['tipe_dokumen'] ?? null, fn ($query, string $type) => $query->where('tipe_dokumen', $type))
            ->orderBy($sort, ($filters['direction'] ?? 'desc') === 'asc' ? 'asc' : 'desc')
            ->paginate((int) ($filters['per_page'] ?? 10))
            ->withQueryString();
    }

    public function create(array $data, Request $request): DocumentTemplate
    {
        return $this->createDocumentTemplate->handle($data, $request);
    }

    public function update(DocumentTemplate $template, array $data, Request $request): DocumentTemplate
    {
        return $this->updateDocumentTemplate->handle($template, $data, $request);
    }

    public function delete(DocumentTemplate $template, Request $request): void
    {
        $this->deleteDocumentTemplate->handle($template, $request);
    }
}
