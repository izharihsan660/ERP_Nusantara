<?php

namespace App\Http\Controllers\Transaction;

use App\Enums\DocumentType;
use App\Enums\PDStatus;
use App\Enums\QuotationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Quotation\RejectQuotationRequest;
use App\Http\Requests\Quotation\StoreQuotationRequest;
use App\Http\Requests\Quotation\VoidQuotationRequest;
use App\Models\Customer;
use App\Models\DocumentTemplate;
use App\Models\Invoice;
use App\Models\Katalog;
use App\Models\PermintaanDana;
use App\Models\PurchaseOrder;
use App\Models\Quotation;
use App\Models\SalesOrder;
use App\Models\Spb;
use App\Models\WipOrder;
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
        $quotation->load([
            'customer',
            'template',
            'items.katalog',
            'createdBy:id,name',
            'approvedBy:id,name',
            'voidedBy:id,name',
            'salesOrder.wipOrders.createdBy:id,name',
            'salesOrder.wipOrders.spb.customer:id,nama_customer',
            'salesOrder.wipOrders.spb.site:id,nama_site,alamat',
            'salesOrder.wipOrders.spb.items:id,spb_id,qty',
            'salesOrder.wipOrders.spb.invoice',
        ]);

        return Inertia::render('Quotation/Show', [
            'quotation' => [
                'id' => $quotation->id,
                'no_quotation' => $quotation->no_quotation,
                'tgl_quotation' => $quotation->tgl_quotation?->format('Y-m-d'),
                'revisi' => $quotation->revisi,
                'status' => $quotation->status->value,
                'status_label' => $quotation->status->label(),
                'catatan' => $quotation->catatan,
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
                'sales_order' => $quotation->salesOrder ? $this->salesOrderData($quotation->salesOrder) : null,
            ],
            'sites' => $quotation->customer
                ? $quotation->customer->sites()
                    ->orderBy('nama_site')
                    ->get(['id', 'customer_id', 'nama_site', 'alamat'])
                    ->map(fn ($site): array => [
                        'id' => $site->id,
                        'customer_id' => $site->customer_id,
                        'label' => $site->nama_site,
                        'alamat' => $site->alamat,
                    ])
                : [],
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

        $purchaseOrder = $quotation ? null : PurchaseOrder::query()
            ->with(['vendor:id,nama_vendor', 'approvedBy:id,name'])
            ->approved()
            ->where('qr_token', $token)
            ->first();

        $permintaanDana = ($quotation || $purchaseOrder) ? null : PermintaanDana::query()
            ->with(['approvedBy:id,name'])
            ->whereIn('status', [PDStatus::Approved->value, PDStatus::Paid->value])
            ->where('qr_token', $token)
            ->first();

        $document = null;

        if ($quotation) {
            $document = [
                'jenis_dokumen' => 'Quotation',
                'nomor_label' => 'No. Quotation',
                'nomor' => $quotation->no_quotation,
                'pihak_label' => 'Customer',
                'pihak' => $quotation->customer?->nama_customer,
                'tanggal_label' => 'Tanggal Terbit',
                'tanggal' => $quotation->tgl_quotation?->format('Y-m-d'),
                'approved_by' => $quotation->approvedBy?->name,
                'approved_at' => $quotation->approved_at?->format('Y-m-d H:i'),
            ];
        }

        if ($purchaseOrder) {
            $document = [
                'jenis_dokumen' => 'Purchase Order',
                'nomor_label' => 'No. PO',
                'nomor' => $purchaseOrder->no_purchase_order,
                'pihak_label' => 'Vendor',
                'pihak' => $purchaseOrder->vendor?->nama_vendor,
                'tanggal_label' => 'Tanggal PO',
                'tanggal' => $purchaseOrder->tgl_po?->format('Y-m-d'),
                'approved_by' => $purchaseOrder->approvedBy?->name,
                'approved_at' => $purchaseOrder->approved_at?->format('Y-m-d H:i'),
            ];
        }

        if ($permintaanDana) {
            $document = [
                'jenis_dokumen' => 'Permintaan Dana',
                'nomor_label' => 'No. PD',
                'nomor' => $permintaanDana->no_pd,
                'pihak_label' => 'Kategori',
                'pihak' => $permintaanDana->kategori->label(),
                'tanggal_label' => 'Nominal',
                'tanggal' => 'Rp '.number_format((float) $permintaanDana->nominal, 2, ',', '.'),
                'approved_by' => $permintaanDana->approvedBy?->name,
                'approved_at' => $permintaanDana->approved_at?->format('Y-m-d H:i'),
            ];
        }

        return Inertia::render('Verify', [
            'valid' => (bool) $document,
            'document' => $document,
            'quotation' => $document,
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

    /**
     * @return array<string, mixed>
     */
    private function salesOrderData(SalesOrder $salesOrder): array
    {
        return [
            'id' => $salesOrder->id,
            'no_po_customer' => $salesOrder->no_po_customer,
            'no_pr_customer' => $salesOrder->no_pr_customer,
            'tgl_po' => $salesOrder->tgl_po?->format('Y-m-d'),
            'metode_pembayaran' => $salesOrder->metode_pembayaran->value,
            'metode_pembayaran_label' => $salesOrder->metode_pembayaran->label(),
            'top_hari' => $salesOrder->top_hari,
            'tgl_jatuh_tempo' => $salesOrder->getTglJatuhTempo()?->format('Y-m-d'),
            'status' => $salesOrder->status->value,
            'status_label' => $salesOrder->status->label(),
            'alasan_void' => $salesOrder->alasan_void,
            'is_voidable' => $salesOrder->isVoidable(),
            'wip_orders' => $salesOrder->wipOrders->map(fn (WipOrder $wip): array => [
                'id' => $wip->id,
                'no_wip' => $wip->no_wip,
                'tipe_order' => $wip->tipe_order->value,
                'tipe_order_label' => $wip->tipe_order->label(),
                'nama_ekspedisi' => $wip->nama_ekspedisi,
                'status_supply' => $wip->status_supply->value,
                'status_supply_label' => $wip->status_supply->label(),
                'tersupply_at' => $wip->tersupply_at?->format('Y-m-d H:i'),
                'status' => $wip->status->value,
                'status_label' => $wip->status->label(),
                'alasan_void' => $wip->alasan_void,
                'is_voidable' => $wip->isVoidable(),
                'spb' => $wip->spb->map(fn (Spb $spb): array => $this->spbData($spb))->values(),
            ])->values(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function spbData(Spb $spb): array
    {
        return [
            'id' => $spb->id,
            'no_spb' => $spb->no_spb,
            'tgl_spb' => $spb->tgl_spb?->format('Y-m-d'),
            'referensi_tipe' => $spb->referensi_tipe->value,
            'no_referensi' => $spb->no_referensi,
            'nama_ekspedisi' => $spb->nama_ekspedisi,
            'status' => $spb->status->value,
            'status_label' => $spb->status->label(),
            'items_count' => $spb->items->count(),
            'items_qty' => $spb->items->sum('qty'),
            'is_voidable' => $spb->isVoidable(),
            'is_parsial' => $spb->isParsial(),
            'site' => $spb->site?->only(['id', 'nama_site', 'alamat']),
            'customer' => $spb->customer?->only(['id', 'nama_customer']),
            'invoice' => $spb->invoice ? $this->invoiceData($spb->invoice) : null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function invoiceData(Invoice $invoice): array
    {
        return [
            'id' => $invoice->id,
            'no_dokumen' => $invoice->no_dokumen,
            'tipe_dokumen' => $invoice->tipe_dokumen->value,
            'tipe_dokumen_label' => $invoice->tipe_dokumen->label(),
            'tgl_dokumen' => $invoice->tgl_dokumen?->format('Y-m-d'),
            'no_faktur_pajak' => $invoice->no_faktur_pajak,
            'total_nilai' => $invoice->total_nilai,
            'total_hpp' => $invoice->total_hpp,
            'total_profit' => $invoice->total_profit,
            'metode_pembayaran' => $invoice->metode_pembayaran->value,
            'metode_pembayaran_label' => $invoice->metode_pembayaran->label(),
            'top_hari' => $invoice->top_hari,
            'tgl_jatuh_tempo' => $invoice->tgl_jatuh_tempo?->format('Y-m-d'),
            'is_jatuh_tempo_h7' => $invoice->isJatuhTempoH7(),
            'status_pembayaran' => $invoice->status_pembayaran->value,
            'status_pembayaran_label' => $invoice->status_pembayaran->label(),
            'tgl_bayar' => $invoice->tgl_bayar?->format('Y-m-d'),
            'jumlah_bayar' => $invoice->jumlah_bayar,
            'file_ttd_gabungan' => $invoice->file_ttd_gabungan,
            'status' => $invoice->status->value,
            'status_label' => $invoice->status->label(),
            'alasan_void' => $invoice->alasan_void,
            'is_voidable' => $invoice->isVoidable(),
        ];
    }
}
