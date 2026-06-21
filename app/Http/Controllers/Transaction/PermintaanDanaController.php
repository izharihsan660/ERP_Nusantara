<?php

namespace App\Http\Controllers\Transaction;

use App\Enums\PdDocumentKategori;
use App\Enums\PDStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\PermintaanDana\RejectPermintaanDanaRequest;
use App\Http\Requests\PermintaanDana\StorePermintaanDanaRequest;
use App\Http\Requests\PermintaanDana\UploadBuktiRequest;
use App\Http\Requests\PermintaanDana\VoidPermintaanDanaRequest;
use App\Models\PdDocument;
use App\Models\PermintaanDana;
use App\Services\PermintaanDanaPDFService;
use App\Services\PermintaanDanaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PermintaanDanaController extends Controller
{
    public function __construct(
        private readonly PermintaanDanaService $permintaanDanaService,
        private readonly PermintaanDanaPDFService $permintaanDanaPDFService,
    ) {}

    public function index(Request $request): Response
    {
        return Inertia::render('PermintaanDana/Index', [
            'permintaanDana' => $this->permintaanDanaService->paginate($request->query())->through(fn (PermintaanDana $permintaanDana): array => [
                'id' => $permintaanDana->id,
                'no_pd' => $permintaanDana->no_pd,
                'tujuan' => $permintaanDana->tujuan,
                'nominal' => $permintaanDana->items->sum('total'),
                'status' => $permintaanDana->status->value,
                'status_label' => $permintaanDana->status->label(),
                'created_by' => $permintaanDana->createdBy?->name,
                'plan_pembayaran' => $permintaanDana->plan_pembayaran?->format('Y-m-d'),
            ]),
            'filters' => $request->only(['search', 'status', 'date_from', 'date_to', 'sort', 'direction', 'per_page']),
            'statuses' => PDStatus::options(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('PermintaanDana/Create');
    }

    public function store(StorePermintaanDanaRequest $request): RedirectResponse
    {
        $permintaanDana = $this->permintaanDanaService->create($request->validated(), $request->user());

        if ($request->boolean('submit')) {
            $this->permintaanDanaService->submit($permintaanDana, $request->user());
        }

        return to_route('permintaan-dana.show', $permintaanDana)->with('success', 'Permintaan Dana berhasil dibuat.');
    }

    public function show(PermintaanDana $permintaanDana): Response
    {
        $permintaanDana->load(['createdBy:id,name', 'approvedBy:id,name', 'voidedBy:id,name', 'documents', 'items']);

        return Inertia::render('PermintaanDana/Show', [
            'documentCategories' => PdDocumentKategori::options(),
            'permintaanDana' => [
                'id' => $permintaanDana->id,
                'no_pd' => $permintaanDana->no_pd,
                'tujuan' => $permintaanDana->tujuan,
                'rekening_tujuan' => $permintaanDana->rekening_tujuan,
                'bank_tujuan' => $permintaanDana->bank_tujuan,
                'plan_pembayaran' => $permintaanDana->plan_pembayaran?->format('Y-m-d'),
                'nominal' => $permintaanDana->items->sum('total'),
                'keterangan' => $permintaanDana->keterangan,
                'referensi_dokumen' => $permintaanDana->referensi_dokumen,
                'status' => $permintaanDana->status->value,
                'status_label' => $permintaanDana->status->label(),
                'catatan_rejection' => $permintaanDana->catatan_rejection,
                'submitted_at' => $permintaanDana->submitted_at?->format('Y-m-d H:i'),
                'approved_at' => $permintaanDana->approved_at?->format('Y-m-d H:i'),
                'tgl_realisasi' => $permintaanDana->tgl_realisasi?->format('Y-m-d'),
                'jumlah_realisasi' => $permintaanDana->jumlah_realisasi,
                'documents' => $permintaanDana->documents->map(fn (PdDocument $document): array => [
                    'id' => $document->id,
                    'kategori' => $document->kategori->value,
                    'kategori_label' => $document->kategori->label(),
                    'nama_file' => $document->nama_file,
                    'created_at' => $document->created_at?->format('Y-m-d H:i'),
                ])->values(),
                'items' => $permintaanDana->items->map(fn ($item): array => [
                    'id' => $item->id,
                    'no_part' => $item->no_part,
                    'description' => $item->description,
                    'qty' => $item->qty,
                    'harga' => $item->harga,
                    'total' => $item->total,
                    'remarks' => $item->remarks,
                ])->values(),
                'voided_at' => $permintaanDana->voided_at?->format('Y-m-d H:i'),
                'alasan_void' => $permintaanDana->alasan_void,
                'created_at' => $permintaanDana->created_at?->format('Y-m-d H:i'),
                'updated_at' => $permintaanDana->updated_at?->format('Y-m-d H:i'),
                'created_by' => $permintaanDana->createdBy?->only(['id', 'name']),
                'approved_by' => $permintaanDana->approvedBy?->only(['id', 'name']),
                'voided_by' => $permintaanDana->voidedBy?->only(['id', 'name']),
                'is_voidable' => $permintaanDana->isVoidable(),
            ],
        ]);
    }

    public function submit(Request $request, PermintaanDana $permintaanDana): RedirectResponse
    {
        $this->permintaanDanaService->submit($permintaanDana, $request->user());

        return back()->with('success', 'Permintaan Dana berhasil disubmit ke Manager.');
    }

    public function approve(Request $request, PermintaanDana $permintaanDana): RedirectResponse
    {
        $this->permintaanDanaService->approve($permintaanDana, $request->user());

        return back()->with('success', 'Permintaan Dana berhasil diapprove.');
    }

    public function reject(RejectPermintaanDanaRequest $request, PermintaanDana $permintaanDana): RedirectResponse
    {
        $this->permintaanDanaService->reject($permintaanDana, $request->validated('catatan_rejection'), $request->user());

        return back()->with('success', 'Permintaan Dana berhasil direject.');
    }

    public function uploadBukti(UploadBuktiRequest $request, PermintaanDana $permintaanDana): RedirectResponse
    {
        $this->permintaanDanaService->uploadBukti($permintaanDana, $request->validated(), $request->user());

        return back()->with('success', 'Bukti pembayaran berhasil diupload.');
    }

    public function void(VoidPermintaanDanaRequest $request, PermintaanDana $permintaanDana): RedirectResponse
    {
        $this->permintaanDanaService->void($permintaanDana, $request->validated('alasan_void'), $request->user());

        return to_route('permintaan-dana.show', $permintaanDana)->with('success', 'Permintaan Dana berhasil divoid.');
    }

    public function download(Request $request, PermintaanDana $permintaanDana): BinaryFileResponse
    {
        abort_unless(in_array($permintaanDana->status, [PDStatus::Approved, PDStatus::Paid], true), 403);

        $path = $this->permintaanDanaPDFService->path($permintaanDana);

        if (! Storage::disk('local')->exists($path)) {
            $path = $this->permintaanDanaPDFService->generate($permintaanDana);
        }

        $fileName = str_replace('/', '-', $permintaanDana->no_pd).'.pdf';

        return response()->file(Storage::disk('local')->path($path), [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$fileName.'"',
        ]);
    }

    public function downloadDocument(PdDocument $document): BinaryFileResponse
    {
        abort_unless($document->permintaanDana?->status === PDStatus::Paid, 403);
        abort_unless(Storage::disk('local')->exists($document->file_path), 404);

        return response()->download(Storage::disk('local')->path($document->file_path), $document->nama_file);
    }
}
