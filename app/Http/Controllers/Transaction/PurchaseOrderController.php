<?php

namespace App\Http\Controllers\Transaction;

use App\Enums\PurchaseOrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\PurchaseOrder\RejectPurchaseOrderRequest;
use App\Http\Requests\PurchaseOrder\StorePurchaseOrderRequest;
use App\Http\Requests\PurchaseOrder\VoidPurchaseOrderRequest;
use App\Models\Customer;
use App\Models\Katalog;
use App\Models\PurchaseOrder;
use App\Models\Site;
use App\Models\Spb;
use App\Models\Vendor;
use App\Services\PurchaseOrderPDFService;
use App\Services\PurchaseOrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PurchaseOrderController extends Controller
{
    public function __construct(
        private readonly PurchaseOrderService $purchaseOrderService,
        private readonly PurchaseOrderPDFService $purchaseOrderPDFService,
    ) {}

    public function index(Request $request): Response
    {
        return Inertia::render('PurchaseOrder/Index', [
            'purchaseOrders' => $this->purchaseOrderService->paginate($request->query())->through(fn (PurchaseOrder $purchaseOrder): array => [
                'id' => $purchaseOrder->id,
                'no_purchase_order' => $purchaseOrder->no_purchase_order,
                'vendor' => $purchaseOrder->vendor?->nama_vendor,
                'tgl_po' => $purchaseOrder->tgl_po?->format('Y-m-d'),
                'total' => $purchaseOrder->items->sum('jumlah'),
                'status' => $purchaseOrder->status->value,
                'status_label' => $purchaseOrder->status->label(),
            ]),
            'filters' => $request->only(['search', 'vendor_id', 'status', 'date_from', 'date_to', 'sort', 'direction', 'per_page']),
            'vendors' => $this->vendors(),
            'statuses' => PurchaseOrderStatus::options(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('PurchaseOrder/Create', [
            'customers' => $this->customers(),
            'vendors' => $this->vendors(),
            'katalog' => Katalog::query()
                ->active()
                ->orderBy('part_no')
                ->get(['id', 'part_no', 'nama_barang', 'satuan', 'harga_jual_default'])
                ->map(fn (Katalog $item): array => [
                    'id' => $item->id,
                    'label' => "{$item->part_no} - {$item->nama_barang}",
                    'part_no' => $item->part_no,
                    'deskripsi' => $item->nama_barang,
                    'satuan' => $item->satuan,
                    'harga_satuan' => $item->harga_jual_default,
                ]),
        ]);
    }

    public function store(StorePurchaseOrderRequest $request): RedirectResponse
    {
        $purchaseOrder = $this->purchaseOrderService->create($request->validated(), $request->user());

        if ($request->boolean('submit')) {
            $this->purchaseOrderService->submit($purchaseOrder, $request->user());
        }

        return to_route('purchase-orders.show', $purchaseOrder)->with('success', 'Purchase Order berhasil dibuat.');
    }

    public function show(PurchaseOrder $purchaseOrder): Response
    {
        $purchaseOrder->load([
            'customer:id,nama_customer',
            'vendor',
            'items.katalog',
            'createdBy:id,name',
            'approvedBy:id,name',
            'voidedBy:id,name',
            'spb.customer:id,nama_customer',
            'spb.site:id,nama_site,alamat',
            'spb.items:id,spb_id,qty',
        ]);

        return Inertia::render('PurchaseOrder/Show', [
            'purchaseOrder' => [
                'id' => $purchaseOrder->id,
                'no_purchase_order' => $purchaseOrder->no_purchase_order,
                'tgl_po' => $purchaseOrder->tgl_po?->format('Y-m-d'),
                'no_pr_customer' => $purchaseOrder->no_pr_customer,
                'no_po_customer' => $purchaseOrder->no_po_customer,
                'status' => $purchaseOrder->status->value,
                'status_label' => $purchaseOrder->status->label(),
                'catatan' => $purchaseOrder->catatan,
                'alasan_void' => $purchaseOrder->alasan_void,
                'approved_at' => $purchaseOrder->approved_at?->format('Y-m-d H:i'),
                'voided_at' => $purchaseOrder->voided_at?->format('Y-m-d H:i'),
                'customer' => $purchaseOrder->customer?->only(['id', 'nama_customer']),
                'vendor' => $purchaseOrder->vendor?->only(['id', 'nama_vendor', 'alamat']),
                'created_by' => $purchaseOrder->createdBy?->only(['id', 'name']),
                'approved_by' => $purchaseOrder->approvedBy?->only(['id', 'name']),
                'voided_by' => $purchaseOrder->voidedBy?->only(['id', 'name']),
                'items' => $purchaseOrder->items->map(fn ($item): array => [
                    'id' => $item->id,
                    'katalog_id' => $item->katalog_id,
                    'part_no' => $item->katalog?->part_no,
                    'deskripsi' => $item->deskripsi,
                    'qty' => $item->qty,
                    'satuan' => $item->satuan,
                    'harga_satuan' => $item->harga_satuan,
                    'jumlah' => $item->jumlah,
                ]),
                'total' => $purchaseOrder->total,
                'spb' => $purchaseOrder->spb->map(fn (Spb $spb): array => $this->spbData($spb))->values(),
            ],
            'sites' => Site::query()
                ->with('customer:id,nama_customer')
                ->orderBy('nama_site')
                ->get(['id', 'customer_id', 'nama_site', 'alamat'])
                ->map(fn (Site $site): array => [
                    'id' => $site->id,
                    'customer_id' => $site->customer_id,
                    'label' => $site->nama_site,
                    'alamat' => $site->alamat,
                ]),
        ]);
    }

    public function submit(Request $request, PurchaseOrder $purchaseOrder): RedirectResponse
    {
        $this->purchaseOrderService->submit($purchaseOrder, $request->user());

        return back()->with('success', 'Purchase Order berhasil disubmit ke Manager.');
    }

    public function approve(Request $request, PurchaseOrder $purchaseOrder): RedirectResponse
    {
        $this->purchaseOrderService->approve($purchaseOrder, $request->user());

        return back()->with('success', 'Purchase Order berhasil diapprove.');
    }

    public function reject(RejectPurchaseOrderRequest $request, PurchaseOrder $purchaseOrder): RedirectResponse
    {
        $this->purchaseOrderService->reject($purchaseOrder, $request->validated('catatan'), $request->user());

        return back()->with('success', 'Purchase Order dikembalikan ke Draft.');
    }

    public function void(VoidPurchaseOrderRequest $request, PurchaseOrder $purchaseOrder): RedirectResponse
    {
        $this->purchaseOrderService->void($purchaseOrder, $request->validated('alasan_void'), $request->user());

        return to_route('purchase-orders.show', $purchaseOrder)->with('success', 'Purchase Order berhasil divoid.');
    }

    public function download(PurchaseOrder $purchaseOrder): BinaryFileResponse
    {
        abort_unless($purchaseOrder->status === PurchaseOrderStatus::Approved, 403);

        $path = $this->purchaseOrderPDFService->path($purchaseOrder);

        if (! Storage::disk('local')->exists($path)) {
            $path = $this->purchaseOrderPDFService->generate($purchaseOrder);
        }

        $fileName = str_replace('/', '-', $purchaseOrder->no_purchase_order).'.pdf';

        return response()->file(Storage::disk('local')->path($path), [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$fileName.'"',
        ]);
    }

    /**
     * @return array<int, array{id: int, label: string}>
     */
    private function customers(): array
    {
        return Customer::query()
            ->active()
            ->orderBy('nama_customer')
            ->get(['id', 'kode_customer', 'nama_customer'])
            ->map(fn (Customer $customer): array => [
                'id' => $customer->id,
                'label' => "{$customer->kode_customer} - {$customer->nama_customer}",
            ])
            ->all();
    }

    /**
     * @return array<int, array{id: int, label: string}>
     */
    private function vendors(): array
    {
        return Vendor::query()
            ->active()
            ->orderBy('nama_vendor')
            ->get(['id', 'nama_vendor'])
            ->map(fn (Vendor $vendor): array => [
                'id' => $vendor->id,
                'label' => $vendor->nama_vendor,
            ])
            ->all();
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
        ];
    }
}
