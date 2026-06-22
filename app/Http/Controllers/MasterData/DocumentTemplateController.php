<?php

namespace App\Http\Controllers\MasterData;

use App\Actions\ActivityLog\RecordActivity;
use App\Enums\DocumentType;
use App\Http\Controllers\Controller;
use App\Http\Requests\DocumentTemplate\StoreDocumentTemplateRequest;
use App\Http\Requests\DocumentTemplate\UpdateDocumentTemplateRequest;
use App\Models\DocumentTemplate;
use App\Services\DocumentTemplateService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DocumentTemplateController extends Controller
{
    public function __construct(
        private readonly DocumentTemplateService $documentTemplateService,
        private readonly RecordActivity $recordActivity,
    ) {}

    public function index(Request $request): Response
    {
        return Inertia::render('MasterData/DocumentTemplates/Index', [
            'templates' => $this->documentTemplateService->paginate($request->query()),
            'filters' => $request->only(['search', 'tipe_dokumen', 'sort', 'direction', 'per_page']),
            'documentTypes' => DocumentType::options(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('MasterData/DocumentTemplates/Form', [
            'template' => null,
            'documentTypes' => DocumentType::options(),
        ]);
    }

    public function store(StoreDocumentTemplateRequest $request): RedirectResponse
    {
        $this->documentTemplateService->create($request->validated(), $request);

        return to_route('document-templates.index')->with('success', 'Template dokumen berhasil dibuat.');
    }

    public function edit(DocumentTemplate $documentTemplate): Response
    {
        return Inertia::render('MasterData/DocumentTemplates/Form', [
            'template' => $documentTemplate,
            'documentTypes' => DocumentType::options(),
        ]);
    }

    public function update(UpdateDocumentTemplateRequest $request, DocumentTemplate $documentTemplate): RedirectResponse
    {
        $this->documentTemplateService->update($documentTemplate, $request->validated(), $request);

        return to_route('document-templates.index')->with('success', 'Template dokumen berhasil diperbarui.');
    }

    public function uploadDocx(Request $request, DocumentTemplate $documentTemplate): RedirectResponse
    {
        abort_unless($request->user()?->can('ubah_template'), 403);

        $request->validate([
            'docx' => ['required', 'file', 'mimes:docx', 'max:10240'],
        ]);

        $path = $request->file('docx')->storeAs(
            'templates',
            $documentTemplate->kode_template.'.docx',
            'local',
        );

        $documentTemplate->update(['docx_path' => $path]);
        $this->recordActivity->handle('uploaded_document_template', $documentTemplate, "Upload template .docx: {$documentTemplate->nama_template}", $request);

        return back()->with('success', 'Template .docx berhasil diupload.');
    }

    public function destroy(Request $request, DocumentTemplate $documentTemplate): RedirectResponse
    {
        abort_unless($request->user()?->can('hapus_template'), 403);

        $this->documentTemplateService->delete($documentTemplate, $request);

        return back()->with('success', 'Template dokumen berhasil dihapus.');
    }
}
