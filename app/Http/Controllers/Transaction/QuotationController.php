<?php

namespace App\Http\Controllers\Transaction;

use App\Enums\DocumentType;
use App\Enums\QuotationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Quotation\RejectQuotationRequest;
use App\Http\Requests\Quotation\StoreQuotationRequest;
use App\Http\Requests\Quotation\VoidQuotationRequest;
use App\Models\Customer;
use App\Models\DocumentTemplate;
use App\Models\Katalog;
use App\Models\Quotation;
use App\Services\QuotationPDFService;
use App\Services\QuotationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class QuotationController extends Controller
{
    public function __construct(
        private readonly QuotationService $quotationService,
        private readonly QuotationPDFService $quotationPDFService,
    ) {}

    public function index(Request $request): Response
    {
        return Inertia::render('Quotation/Index', [
            'quotations' => $this->quotationService->paginate($request->query())->through(fn (Quotation $quotation): array => [
                'id' => $quotation->id,
                'no_quotation' => $quotation->no_quotation,
                'customer' => $quotation->customer?->nama_customer,
                'tgl_quotation' => $quotation->tgl_quotation?->format('Y-m-d'),
                'revisi' => $quotation->revisi,
                'total' => $quotation->items->sum('jumlah'),
                'status' => $quotation->status->value,
                'status_label' => $quotation->status->label(),
            ]),
            'filters' => $request->only(['search', 'customer_id', 'status', 'date_from', 'date_to', 'sort', 'direction', 'per_page']),
            'customers' => $this->customers(),
            'statuses' => QuotationStatus::options(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Quotation/Create', [
            'customers' => Customer::query()
                ->active()
                ->with('quotationTemplate:id,nama_template,blade_file')
                ->orderBy('nama_customer')
                ->get(['id', 'kode_customer', 'nama_customer', 'template_quotation_id'])
                ->map(fn (Customer $customer): array => [
                    'id' => $customer->id,
                    'label' => "{$customer->kode_customer} - {$customer->nama_customer}",
                    'template_id' => $customer->template_quotation_id,
                    'template' => $customer->quotationTemplate ? [
                        'id' => $customer->quotationTemplate->id,
                        'nama_template' => $customer->quotationTemplate->nama_template,
                        'blade_file' => $customer->quotationTemplate->blade_file,
                    ] : null,
                ]),
            'templates' => DocumentTemplate::query()
                ->where('tipe_dokumen', DocumentType::Quotation->value)
                ->orderByDesc('is_default')
                ->orderBy('nama_template')
                ->get(['id', 'nama_template', 'blade_file'])
                ->map(fn (DocumentTemplate $template): array => [
                    'id' => $template->id,
                    'label' => $template->nama_template,
                    'blade_file' => $template->blade_file,
                ]),
            'katalog' => Katalog::query()
                ->active()
                ->orderBy('part_no')
                ->get(['id', 'part_no', 'nama_barang', 'satuan', 'hpp', 'harga_jual_default'])
                ->map(fn (Katalog $item): array => [
                    'id' => $item->id,
                    'label' => "{$item->part_no} - {$item->nama_barang}",
                    'part_no' => $item->part_no,
                    'deskripsi' => $item->nama_barang,
                    'satuan' => $item->satuan,
                    'hpp' => $item->hpp,
                    'harga_jual_default' => $item->harga_jual_default,
                ]),
        ]);
    }

    public function store(StoreQuotationRequest $request): RedirectResponse
    {
        $quotation = $this->quotationService->create($request->validated(), $request->user());

        if ($request->boolean('submit')) {
            $this->quotationService->submit($quotation, $request->user());
        }

        return to_route('quotations.show', $quotation)->with('success', 'Quotation berhasil dibuat.');
    }

    public function show(Quotation $quotation): Response
    {
        $quotation->load(['customer', 'template', 'items.katalog', 'createdBy:id,name', 'approvedBy:id,name', 'voidedBy:id,name']);

        return Inertia::render('Quotation/Show', [
            'quotation' => [
                'id' => $quotation->id,
                'no_quotation' => $quotation->no_quotation,
                'tgl_quotation' => $quotation->tgl_quotation?->format('Y-m-d'),
                'revisi' => $quotation->revisi,
                'status' => $quotation->status->value,
                'status_label' => $quotation->status->label(),
                'catatan_rejection' => $quotation->catatan_rejection,
                'alasan_void' => $quotation->alasan_void,
                'approved_at' => $quotation->approved_at?->format('Y-m-d H:i'),
                'voided_at' => $quotation->voided_at?->format('Y-m-d H:i'),
                'customer' => $quotation->customer?->only(['id', 'kode_customer', 'nama_customer']),
                'template' => $quotation->template?->only(['id', 'nama_template', 'blade_file']),
                'created_by' => $quotation->createdBy?->only(['id', 'name']),
                'approved_by' => $quotation->approvedBy?->only(['id', 'name']),
                'voided_by' => $quotation->voidedBy?->only(['id', 'name']),
                'items' => $quotation->items->map(fn ($item): array => [
                    'id' => $item->id,
                    'part_no' => $item->part_no,
                    'deskripsi' => $item->deskripsi,
                    'qty' => $item->qty,
                    'satuan' => $item->satuan,
                    'harga_satuan' => $item->harga_satuan,
                    'hpp_satuan' => $item->hpp_satuan,
                    'jumlah' => $item->jumlah,
                    'profit' => $item->profit,
                ]),
                'total' => $quotation->total,
                'total_hpp' => $quotation->total_hpp,
                'total_profit' => $quotation->total_profit,
            ],
        ]);
    }

    public function submit(Request $request, Quotation $quotation): RedirectResponse
    {
        $this->quotationService->submit($quotation, $request->user());

        return back()->with('success', 'Quotation berhasil disubmit ke Manager.');
    }

    public function approve(Request $request, Quotation $quotation): RedirectResponse
    {
        $this->quotationService->approve($quotation, $request->user());

        return back()->with('success', 'Quotation berhasil diapprove.');
    }

    public function reject(RejectQuotationRequest $request, Quotation $quotation): RedirectResponse
    {
        $this->quotationService->reject($quotation, $request->validated('catatan_rejection'), $request->user());

        return back()->with('success', 'Quotation berhasil direject.');
    }

    public function void(VoidQuotationRequest $request, Quotation $quotation): RedirectResponse
    {
        $this->quotationService->void($quotation, $request->validated('alasan_void'), $request->user());

        return to_route('quotations.show', $quotation)->with('success', 'Quotation berhasil divoid.');
    }

    public function download(Quotation $quotation): BinaryFileResponse
    {
        abort_unless($quotation->status === QuotationStatus::Approved, 403);

        $path = $this->quotationPDFService->path($quotation);

        if (! Storage::disk('local')->exists($path)) {
            $path = $this->quotationPDFService->generate($quotation);
        }

        $fileName = str_replace('/', '-', $quotation->no_quotation).'.pdf';

        return response()->file(Storage::disk('local')->path($path), [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$fileName.'"',
        ]);
    }

    public function duplicate(Request $request, Quotation $quotation): RedirectResponse
    {
        $newQuotation = $this->quotationService->duplicate($quotation, $request->user());

        return to_route('quotations.show', $newQuotation)->with('success', 'Quotation berhasil diduplikasi sebagai draft baru.');
    }

    public function verify(string $token): Response
    {
        $quotation = Quotation::query()
            ->with(['customer:id,nama_customer', 'approvedBy:id,name'])
            ->approved()
            ->where('qr_token', $token)
            ->first();

        return Inertia::render('Verify', [
            'valid' => (bool) $quotation,
            'quotation' => $quotation ? [
                'jenis_dokumen' => 'Quotation',
                'no_quotation' => $quotation->no_quotation,
                'customer' => $quotation->customer?->nama_customer,
                'tgl_quotation' => $quotation->tgl_quotation?->format('Y-m-d'),
                'approved_by' => $quotation->approvedBy?->name,
                'approved_at' => $quotation->approved_at?->format('Y-m-d H:i'),
            ] : null,
        ]);
    }

    private function customers(): array
    {
        return Customer::query()
            ->active()
            ->orderBy('nama_customer')
            ->get(['id', 'nama_customer'])
            ->map(fn (Customer $customer): array => [
                'id' => $customer->id,
                'label' => $customer->nama_customer,
            ])
            ->all();
    }
}
