<?php

namespace App\Http\Controllers;

use App\Enums\InvoiceStatus;
use App\Enums\MetodePembayaran;
use App\Enums\PDStatus;
use App\Enums\SalesOrderStatus;
use App\Enums\SpbStatus;
use App\Enums\StatusPembayaran;
use App\Enums\StatusSupply;
use App\Enums\TipeDokumen;
use App\Enums\TipeOrder;
use App\Exports\OutstandingExport;
use App\Exports\ProfitExport;
use App\Exports\RekapanInvoiceExport;
use App\Exports\RekapanPdExport;
use App\Exports\RekapanPoExport;
use App\Exports\RekapanSpbExport;
use App\Exports\RekapanWipExport;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\PermintaanDana;
use App\Models\Quotation;
use App\Models\SalesOrder;
use App\Models\Spb;
use App\Models\WipOrder;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class LaporanController extends Controller
{
    public function index(Request $request): Response
    {
        $tab = $request->string('tab', 'rekapan-po')->value();

        return match ($tab) {
            'rekapan-wip' => $this->indexRekapanWip($request, $tab),
            'rekapan-spb' => $this->indexRekapanSpb($request, $tab),
            'rekapan-invoice' => $this->indexRekapanInvoice($request, $tab),
            'rekapan-pd' => $this->indexRekapanPd($request, $tab),
            'profit' => $this->indexProfit($request, $tab),
            'outstanding' => $this->indexOutstanding($request, $tab),
            default => $this->indexRekapanPo($request, 'rekapan-po'),
        };
    }

    public function rekapanPo(Request $request): Response
    {
        $this->authorizeReport($request, 'laporan_rekapan_po');

        $query = $this->rekapanPoQuery($request);
        $rows = $this->rekapanPoRows((clone $query)->get());

        return Inertia::render('Laporan/RekapanPo', [
            ...$this->baseProps($request, 'laporan.rekapan-po', 'rekapan-po'),
            'data' => $query->paginate($this->perPage($request))->withQueryString()->through(fn (SalesOrder $salesOrder): array => $this->rekapanPoRow($salesOrder)),
            'summary' => $this->rekapanPoSummary($rows),
            'customers' => $this->customers(),
            'statuses' => SalesOrderStatus::options(),
            'filterConfig' => ['customer', 'periode', 'status_po'],
        ]);
    }

    public function rekapanWip(Request $request): Response
    {
        $this->authorizeReport($request, 'laporan_rekapan_wip');

        $query = $this->rekapanWipQuery($request);
        $rows = $this->rekapanWipRows((clone $query)->get());

        return Inertia::render('Laporan/RekapanWip', [
            ...$this->baseProps($request, 'laporan.rekapan-wip', 'rekapan-wip'),
            'data' => $query->paginate($this->perPage($request))->withQueryString()->through(fn (WipOrder $wip): array => $this->rekapanWipRow($wip)),
            'summary' => $this->rekapanWipSummary($rows),
            'tipeOptions' => TipeOrder::options(),
            'statusSupplyOptions' => StatusSupply::options(),
            'filterConfig' => ['tipe_order', 'status_supply', 'periode'],
        ]);
    }

    public function rekapanSpb(Request $request): Response
    {
        $this->authorizeReport($request, 'laporan_rekapan_spb');

        $query = $this->rekapanSpbQuery($request);
        $rows = $this->rekapanSpbRows((clone $query)->get());

        return Inertia::render('Laporan/RekapanSpb', [
            ...$this->baseProps($request, 'laporan.rekapan-spb', 'rekapan-spb'),
            'data' => $query->paginate($this->perPage($request))->withQueryString()->through(fn (Spb $spb): array => $this->rekapanSpbRow($spb)),
            'summary' => $this->rekapanSpbSummary($rows),
            'customers' => $this->customers(),
            'statuses' => SpbStatus::options(),
            'filterConfig' => ['customer', 'periode', 'status'],
        ]);
    }

    public function rekapanInvoice(Request $request): Response
    {
        $this->authorizeReport($request, 'laporan_rekapan_invoice');

        $query = $this->rekapanInvoiceQuery($request);
        $rows = $this->rekapanInvoiceRows((clone $query)->get());

        return Inertia::render('Laporan/RekapanInvoice', [
            ...$this->baseProps($request, 'laporan.rekapan-invoice', 'rekapan-invoice'),
            'data' => $query->paginate($this->perPage($request))->withQueryString()->through(fn (Invoice $invoice): array => $this->rekapanInvoiceRow($invoice)),
            'summary' => $this->rekapanInvoiceSummary($rows),
            'customers' => $this->customers(),
            'tipeDokumenOptions' => TipeDokumen::options(),
            'statusPembayaranOptions' => StatusPembayaran::options(),
            'metodeOptions' => MetodePembayaran::options(),
            'filterConfig' => ['customer', 'tipe_dokumen', 'status_pembayaran', 'metode_pembayaran', 'periode'],
        ]);
    }

    public function rekapanPd(Request $request): Response
    {
        $this->authorizeReport($request, 'laporan_rekapan_pd');

        $query = $this->rekapanPdQuery($request);
        $rows = $this->rekapanPdRows((clone $query)->get());

        return Inertia::render('Laporan/RekapanPd', [
            ...$this->baseProps($request, 'laporan.rekapan-pd', 'rekapan-pd'),
            'data' => $query->paginate($this->perPage($request))->withQueryString()->through(fn (PermintaanDana $pd): array => $this->rekapanPdRow($pd)),
            'summary' => $this->rekapanPdSummary($rows),
            'statuses' => PDStatus::options(),
            'filterConfig' => ['status', 'periode'],
        ]);
    }

    public function profit(Request $request): Response
    {
        $this->authorizeReport($request, 'laporan_profit');

        $query = $this->profitQuery($request);
        $rows = $this->profitRows((clone $query)->get());

        return Inertia::render('Laporan/Profit', [
            ...$this->baseProps($request, 'laporan.profit', 'profit'),
            'data' => $query->paginate($this->perPage($request))->withQueryString()->through(fn (Quotation $quotation): array => $this->profitRow($quotation)),
            'summary' => $this->profitSummary($rows),
            'customers' => $this->customers(),
            'chart' => $this->profitChart($rows),
            'filterConfig' => ['customer', 'periode', 'mode_profit'],
        ]);
    }

    public function outstanding(Request $request): Response
    {
        $this->authorizeReport($request, 'laporan_outstanding');

        $query = $this->outstandingQuery($request);
        $rows = $this->outstandingRows((clone $query)->get());

        return Inertia::render('Laporan/Outstanding', [
            ...$this->baseProps($request, 'laporan.outstanding', 'outstanding'),
            'data' => $query->paginate($this->perPage($request))->withQueryString()->through(fn (Invoice $invoice): array => $this->outstandingRow($invoice)),
            'summary' => $this->outstandingSummary($rows),
            'customers' => $this->customers(),
            'metodeOptions' => MetodePembayaran::options(),
            'filterConfig' => ['customer', 'metode_pembayaran', 'periode_jatuh_tempo'],
        ]);
    }

    public function export(Request $request, string $tipe): BinaryFileResponse
    {
        return match ($tipe) {
            'rekapan-po' => $this->downloadRekapanPo($request),
            'rekapan-wip' => $this->downloadRekapanWip($request),
            'rekapan-spb' => $this->downloadRekapanSpb($request),
            'rekapan-invoice' => $this->downloadRekapanInvoice($request),
            'rekapan-pd' => $this->downloadRekapanPd($request),
            'profit' => $this->downloadProfit($request),
            'outstanding' => $this->downloadOutstanding($request),
            default => abort(404),
        };
    }

    private function renderIndex(Request $request, string $activeTab, array $props): Response
    {
        return Inertia::render('Laporan/Index', [
            ...$props,
            ...$this->baseProps($request, 'laporan.index', $activeTab),
            'activeTab' => $activeTab,
        ]);
    }

    private function indexRekapanPo(Request $request, string $tab): Response
    {
        $this->authorizeReport($request, 'laporan_rekapan_po');
        $query = $this->rekapanPoQuery($request);
        $rows = $this->rekapanPoRows((clone $query)->get());

        return $this->renderIndex($request, $tab, [
            'data' => $query->paginate($this->perPage($request))->withQueryString()->through(fn (SalesOrder $salesOrder): array => $this->rekapanPoRow($salesOrder)),
            'summary' => $this->rekapanPoSummary($rows),
            'customers' => $this->customers(),
            'statuses' => SalesOrderStatus::options(),
        ]);
    }

    private function indexRekapanWip(Request $request, string $tab): Response
    {
        $this->authorizeReport($request, 'laporan_rekapan_wip');
        $query = $this->rekapanWipQuery($request);
        $rows = $this->rekapanWipRows((clone $query)->get());

        return $this->renderIndex($request, $tab, [
            'data' => $query->paginate($this->perPage($request))->withQueryString()->through(fn (WipOrder $wip): array => $this->rekapanWipRow($wip)),
            'summary' => $this->rekapanWipSummary($rows),
            'tipeOptions' => TipeOrder::options(),
            'statusSupplyOptions' => StatusSupply::options(),
        ]);
    }

    private function indexRekapanSpb(Request $request, string $tab): Response
    {
        $this->authorizeReport($request, 'laporan_rekapan_spb');
        $query = $this->rekapanSpbQuery($request);
        $rows = $this->rekapanSpbRows((clone $query)->get());

        return $this->renderIndex($request, $tab, [
            'data' => $query->paginate($this->perPage($request))->withQueryString()->through(fn (Spb $spb): array => $this->rekapanSpbRow($spb)),
            'summary' => $this->rekapanSpbSummary($rows),
            'customers' => $this->customers(),
            'statuses' => SpbStatus::options(),
        ]);
    }

    private function indexRekapanInvoice(Request $request, string $tab): Response
    {
        $this->authorizeReport($request, 'laporan_rekapan_invoice');
        $query = $this->rekapanInvoiceQuery($request);
        $rows = $this->rekapanInvoiceRows((clone $query)->get());

        return $this->renderIndex($request, $tab, [
            'data' => $query->paginate($this->perPage($request))->withQueryString()->through(fn (Invoice $invoice): array => $this->rekapanInvoiceRow($invoice)),
            'summary' => $this->rekapanInvoiceSummary($rows),
            'customers' => $this->customers(),
            'tipeDokumenOptions' => TipeDokumen::options(),
            'statusPembayaranOptions' => StatusPembayaran::options(),
            'metodeOptions' => MetodePembayaran::options(),
        ]);
    }

    private function indexRekapanPd(Request $request, string $tab): Response
    {
        $this->authorizeReport($request, 'laporan_rekapan_pd');
        $query = $this->rekapanPdQuery($request);
        $rows = $this->rekapanPdRows((clone $query)->get());

        return $this->renderIndex($request, $tab, [
            'data' => $query->paginate($this->perPage($request))->withQueryString()->through(fn (PermintaanDana $pd): array => $this->rekapanPdRow($pd)),
            'summary' => $this->rekapanPdSummary($rows),
            'statuses' => PDStatus::options(),
        ]);
    }

    private function indexProfit(Request $request, string $tab): Response
    {
        $this->authorizeReport($request, 'laporan_profit');
        $query = $this->profitQuery($request);
        $rows = $this->profitRows((clone $query)->get());

        return $this->renderIndex($request, $tab, [
            'data' => $query->paginate($this->perPage($request))->withQueryString()->through(fn (Quotation $quotation): array => $this->profitRow($quotation)),
            'summary' => $this->profitSummary($rows),
            'customers' => $this->customers(),
            'chart' => $this->profitChart($rows),
        ]);
    }

    private function indexOutstanding(Request $request, string $tab): Response
    {
        $this->authorizeReport($request, 'laporan_outstanding');
        $query = $this->outstandingQuery($request);
        $rows = $this->outstandingRows((clone $query)->get());

        return $this->renderIndex($request, $tab, [
            'data' => $query->paginate($this->perPage($request))->withQueryString()->through(fn (Invoice $invoice): array => $this->outstandingRow($invoice)),
            'summary' => $this->outstandingSummary($rows),
            'customers' => $this->customers(),
            'metodeOptions' => MetodePembayaran::options(),
        ]);
    }

    private function downloadRekapanPo(Request $request): BinaryFileResponse
    {
        $this->authorizeReport($request, 'laporan_rekapan_po');
        $rows = $this->rekapanPoRows($this->rekapanPoQuery($request)->get())->values()->all();

        return Excel::download(new RekapanPoExport($rows, $this->rekapanPoSummary(collect($rows))), $this->fileName('rekapan-po'));
    }

    private function downloadRekapanWip(Request $request): BinaryFileResponse
    {
        $this->authorizeReport($request, 'laporan_rekapan_wip');
        $rows = $this->rekapanWipRows($this->rekapanWipQuery($request)->get())->values()->all();

        return Excel::download(new RekapanWipExport($rows, $this->rekapanWipSummary(collect($rows))), $this->fileName('rekapan-wip'));
    }

    private function downloadRekapanSpb(Request $request): BinaryFileResponse
    {
        $this->authorizeReport($request, 'laporan_rekapan_spb');
        $rows = $this->rekapanSpbRows($this->rekapanSpbQuery($request)->get())->values()->all();

        return Excel::download(new RekapanSpbExport($rows, $this->rekapanSpbSummary(collect($rows))), $this->fileName('rekapan-spb'));
    }

    private function downloadRekapanInvoice(Request $request): BinaryFileResponse
    {
        $this->authorizeReport($request, 'laporan_rekapan_invoice');
        $rows = $this->rekapanInvoiceRows($this->rekapanInvoiceQuery($request)->get())->values()->all();

        return Excel::download(new RekapanInvoiceExport($rows, $this->rekapanInvoiceSummary(collect($rows))), $this->fileName('rekapan-invoice'));
    }

    private function downloadRekapanPd(Request $request): BinaryFileResponse
    {
        $this->authorizeReport($request, 'laporan_rekapan_pd');
        $rows = $this->rekapanPdRows($this->rekapanPdQuery($request)->get())->values()->all();

        return Excel::download(new RekapanPdExport($rows, $this->rekapanPdSummary(collect($rows))), $this->fileName('rekapan-pd'));
    }

    private function downloadProfit(Request $request): BinaryFileResponse
    {
        $this->authorizeReport($request, 'laporan_profit');
        $rows = $this->profitRows($this->profitQuery($request)->get())->values()->all();

        return Excel::download(new ProfitExport($rows, $this->profitSummary(collect($rows))), $this->fileName('profit'));
    }

    private function downloadOutstanding(Request $request): BinaryFileResponse
    {
        $this->authorizeReport($request, 'laporan_outstanding');
        $rows = $this->outstandingRows($this->outstandingQuery($request)->get())->values()->all();

        return Excel::download(new OutstandingExport($rows, $this->outstandingSummary(collect($rows))), $this->fileName('outstanding'));
    }

    private function rekapanPoQuery(Request $request): Builder
    {
        $query = SalesOrder::query()
            ->with(['customer:id,nama_customer', 'quotation.items:id,quotation_id,qty,hpp_satuan,jumlah,profit', 'quotation.customer:id,nama_customer'])
            ->latest('tgl_po');

        $this->scopeSalesOwned($query, $request, 'sales_orders.created_by');
        $this->applyCustomerFilter($query, $request, 'customer_id');
        $this->applyDateRange($query, $request, 'tgl_po');
        $query->when($request->filled('status'), fn (Builder $builder) => $builder->where('status', $request->string('status')->value()));
        $query->when($request->filled('search'), function (Builder $builder) use ($request): void {
            $search = '%'.$request->string('search')->value().'%';
            $builder->where(function (Builder $inner) use ($search): void {
                $inner->where('no_po_customer', 'like', $search)
                    ->orWhereHas('quotation', fn (Builder $quotation) => $quotation->where('no_quotation', 'like', $search))
                    ->orWhereHas('customer', fn (Builder $customer) => $customer->where('nama_customer', 'like', $search));
            });
        });

        return $query;
    }

    private function rekapanWipQuery(Request $request): Builder
    {
        $query = WipOrder::query()
            ->with(['salesOrder.quotation.customer:id,nama_customer'])
            ->latest('created_at');

        $this->scopeSalesOwnedRelation($query, $request, 'salesOrder');
        $this->applyDateRange($query, $request, 'created_at');
        $query->when($request->filled('tipe_order'), fn (Builder $builder) => $builder->where('tipe_order', $request->string('tipe_order')->value()));
        $query->when($request->filled('status_supply'), fn (Builder $builder) => $builder->where('status_supply', $request->string('status_supply')->value()));
        $query->when($request->filled('search'), function (Builder $builder) use ($request): void {
            $search = '%'.$request->string('search')->value().'%';
            $builder->where(function (Builder $inner) use ($search): void {
                $inner->where('no_wip', 'like', $search)
                    ->orWhereHas('salesOrder.quotation', fn (Builder $quotation) => $quotation->where('no_quotation', 'like', $search))
                    ->orWhereHas('salesOrder.customer', fn (Builder $customer) => $customer->where('nama_customer', 'like', $search));
            });
        });

        return $query;
    }

    private function rekapanSpbQuery(Request $request): Builder
    {
        $query = Spb::query()
            ->with(['customer:id,nama_customer', 'site:id,nama_site', 'items:id,spb_id,qty'])
            ->latest('tgl_spb');

        $this->scopeCreatedBy($query, $request, 'spb.created_by');
        $this->applyCustomerFilter($query, $request, 'customer_id');
        $this->applyDateRange($query, $request, 'tgl_spb');
        $query->when($request->filled('status'), fn (Builder $builder) => $builder->where('status', $request->string('status')->value()));
        $query->when($request->filled('search'), function (Builder $builder) use ($request): void {
            $search = '%'.$request->string('search')->value().'%';
            $builder->where(function (Builder $inner) use ($search): void {
                $inner->where('no_spb', 'like', $search)
                    ->orWhere('no_referensi', 'like', $search)
                    ->orWhereHas('customer', fn (Builder $customer) => $customer->where('nama_customer', 'like', $search));
            });
        });

        return $query;
    }

    private function rekapanInvoiceQuery(Request $request): Builder
    {
        $query = Invoice::query()
            ->with('customer:id,nama_customer')
            ->where('status', InvoiceStatus::Active)
            ->latest('tgl_dokumen');

        $this->scopeCreatedBy($query, $request, 'invoices.created_by');
        $this->applyCustomerFilter($query, $request, 'customer_id');
        $this->applyDateRange($query, $request, 'tgl_dokumen');
        $query->when($request->filled('tipe_dokumen'), fn (Builder $builder) => $builder->where('tipe_dokumen', $request->string('tipe_dokumen')->value()));
        $query->when($request->filled('status_pembayaran'), fn (Builder $builder) => $builder->where('status_pembayaran', $request->string('status_pembayaran')->value()));
        $query->when($request->filled('metode_pembayaran'), fn (Builder $builder) => $builder->where('metode_pembayaran', $request->string('metode_pembayaran')->value()));
        $query->when($request->filled('search'), function (Builder $builder) use ($request): void {
            $search = '%'.$request->string('search')->value().'%';
            $builder->where(function (Builder $inner) use ($search): void {
                $inner->where('no_dokumen', 'like', $search)
                    ->orWhere('no_faktur_pajak', 'like', $search)
                    ->orWhereHas('customer', fn (Builder $customer) => $customer->where('nama_customer', 'like', $search));
            });
        });

        return $query;
    }

    private function rekapanPdQuery(Request $request): Builder
    {
        $query = PermintaanDana::query()
            ->with(['createdBy:id,name', 'approvedBy:id,name', 'items'])
            ->latest('plan_pembayaran');

        $this->scopeCreatedBy($query, $request, 'permintaan_dana.created_by');
        $this->applyDateRange($query, $request, 'plan_pembayaran');
        $query->when($request->filled('status'), fn (Builder $builder) => $builder->where('status', $request->string('status')->value()));
        $query->when($request->filled('search'), function (Builder $builder) use ($request): void {
            $search = '%'.$request->string('search')->value().'%';
            $builder->where('no_pd', 'like', $search)
                ->orWhere('referensi_dokumen', 'like', $search);
        });

        return $query;
    }

    private function profitQuery(Request $request): Builder
    {
        $query = Quotation::query()
            ->with(['customer:id,nama_customer', 'items:id,quotation_id,qty,hpp_satuan,jumlah,profit'])
            ->latest('tgl_quotation');

        $this->scopeCreatedBy($query, $request, 'quotations.created_by');
        $this->applyCustomerFilter($query, $request, 'customer_id');
        $this->applyDateRange($query, $request, 'tgl_quotation');
        $query->when($request->filled('search'), function (Builder $builder) use ($request): void {
            $search = '%'.$request->string('search')->value().'%';
            $builder->where(function (Builder $inner) use ($search): void {
                $inner->where('no_quotation', 'like', $search)
                    ->orWhereHas('customer', fn (Builder $customer) => $customer->where('nama_customer', 'like', $search));
            });
        });

        return $query;
    }

    private function outstandingQuery(Request $request): Builder
    {
        $query = Invoice::query()
            ->with('customer:id,nama_customer')
            ->where('status', InvoiceStatus::Active)
            ->where('status_pembayaran', '!=', StatusPembayaran::Lunas)
            ->orderBy('tgl_jatuh_tempo');

        $this->scopeCreatedBy($query, $request, 'invoices.created_by');
        $this->applyCustomerFilter($query, $request, 'customer_id');
        $this->applyDateRange($query, $request, 'tgl_jatuh_tempo');
        $query->when($request->filled('metode_pembayaran'), fn (Builder $builder) => $builder->where('metode_pembayaran', $request->string('metode_pembayaran')->value()));
        $query->when($request->filled('search'), function (Builder $builder) use ($request): void {
            $search = '%'.$request->string('search')->value().'%';
            $builder->where(function (Builder $inner) use ($search): void {
                $inner->where('no_dokumen', 'like', $search)
                    ->orWhereHas('customer', fn (Builder $customer) => $customer->where('nama_customer', 'like', $search));
            });
        });

        return $query;
    }

    /**
     * @param  Collection<int, SalesOrder>  $orders
     * @return Collection<int, array<string, mixed>>
     */
    private function rekapanPoRows(Collection $orders): Collection
    {
        return $orders->map(fn (SalesOrder $salesOrder): array => $this->rekapanPoRow($salesOrder))->values();
    }

    /**
     * @return array<string, mixed>
     */
    private function rekapanPoRow(SalesOrder $salesOrder): array
    {
        return [
            'id' => $salesOrder->id,
            'no_quotation' => $salesOrder->quotation?->no_quotation ?? '-',
            'customer' => $salesOrder->customer?->nama_customer ?? $salesOrder->quotation?->customer?->nama_customer ?? '-',
            'no_po_customer' => $salesOrder->no_po_customer,
            'metode_bayar' => $salesOrder->metode_pembayaran->label(),
            'total_nilai' => $salesOrder->quotation?->total ?? 0,
            'total_hpp' => $salesOrder->quotation?->total_hpp ?? 0,
            'profit' => $salesOrder->quotation?->total_profit ?? 0,
            'status_po' => $salesOrder->status->value,
            'status_po_label' => $salesOrder->status->label(),
            'tanggal_po' => $salesOrder->tgl_po?->format('Y-m-d'),
        ];
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $rows
     * @return array<string, mixed>
     */
    private function rekapanPoSummary(Collection $rows): array
    {
        return [
            'total_po' => $rows->count(),
            'total_nilai' => (float) $rows->sum('total_nilai'),
            'total_profit' => (float) $rows->sum('profit'),
        ];
    }

    private function rekapanWipRows(Collection $wips): Collection
    {
        return $wips->map(fn (WipOrder $wip): array => $this->rekapanWipRow($wip))->values();
    }

    private function rekapanWipRow(WipOrder $wip): array
    {
        return [
            'id' => $wip->id,
            'no_wip' => $wip->no_wip,
            'tipe' => $wip->tipe_order->label(),
            'no_quotation' => $wip->salesOrder?->quotation?->no_quotation ?? '-',
            'customer' => $wip->salesOrder?->quotation?->customer?->nama_customer ?? $wip->salesOrder?->customer?->nama_customer ?? '-',
            'ekspedisi' => $wip->nama_ekspedisi ?? '-',
            'status_supply' => $wip->status_supply->value,
            'status_supply_label' => $wip->status_supply->label(),
            'tanggal_input' => $wip->created_at?->format('Y-m-d'),
            'tanggal_tersupply' => $wip->tersupply_at?->format('Y-m-d H:i'),
        ];
    }

    private function rekapanWipSummary(Collection $rows): array
    {
        return [
            'total_wip' => $rows->count(),
            'belum_tersupply' => $rows->where('status_supply', StatusSupply::BelumTersupply->value)->count(),
            'tersupply' => $rows->where('status_supply', StatusSupply::Tersupply->value)->count(),
        ];
    }

    private function rekapanSpbRows(Collection $spb): Collection
    {
        return $spb->map(fn (Spb $item): array => $this->rekapanSpbRow($item))->values();
    }

    private function rekapanSpbRow(Spb $spb): array
    {
        return [
            'id' => $spb->id,
            'no_spb' => $spb->no_spb,
            'customer' => $spb->customer?->nama_customer ?? '-',
            'site' => $spb->site?->nama_site ?? '-',
            'referensi' => $spb->referensi_tipe->label(),
            'no_referensi' => $spb->no_referensi,
            'ekspedisi' => $spb->nama_ekspedisi,
            'etd' => $spb->etd?->format('Y-m-d'),
            'eta' => $spb->eta?->format('Y-m-d'),
            'status' => $spb->status->value,
            'status_label' => $spb->status->label(),
            'total_item' => (int) $spb->items->sum('qty'),
        ];
    }

    private function rekapanSpbSummary(Collection $rows): array
    {
        return [
            'total_spb' => $rows->count(),
            'total_item_dikirim' => (int) $rows->sum('total_item'),
        ];
    }

    private function rekapanInvoiceRows(Collection $invoices): Collection
    {
        return $invoices->map(fn (Invoice $invoice): array => $this->rekapanInvoiceRow($invoice))->values();
    }

    private function rekapanInvoiceRow(Invoice $invoice): array
    {
        return [
            'id' => $invoice->id,
            'no_dokumen' => $invoice->no_dokumen,
            'tipe' => $invoice->tipe_dokumen->value,
            'tipe_label' => $invoice->tipe_dokumen->label(),
            'customer' => $invoice->customer?->nama_customer ?? '-',
            'no_faktur_pajak' => $invoice->no_faktur_pajak ?? '-',
            'total_nilai' => (float) $invoice->total_nilai,
            'grand_total' => (float) $invoice->grand_total,
            'metode_bayar' => $invoice->metode_pembayaran->label(),
            'jatuh_tempo' => $invoice->tgl_jatuh_tempo?->format('Y-m-d'),
            'status_pembayaran' => $invoice->status_pembayaran->value,
            'status_pembayaran_label' => $invoice->status_pembayaran->label(),
            'tanggal_bayar' => $invoice->tgl_bayar?->format('Y-m-d'),
            'jumlah_bayar' => (float) $invoice->jumlah_bayar,
            'sisa' => max((float) $invoice->grand_total - (float) $invoice->jumlah_bayar, 0),
        ];
    }

    private function rekapanInvoiceSummary(Collection $rows): array
    {
        return [
            'total_tagihan' => (float) $rows->sum('grand_total'),
            'total_lunas' => (float) $rows->where('status_pembayaran', StatusPembayaran::Lunas->value)->sum('grand_total'),
            'total_diterima' => (float) $rows->sum('jumlah_bayar'),
            'total_outstanding' => (float) $rows->where('status_pembayaran', '!=', StatusPembayaran::Lunas->value)->sum('sisa'),
        ];
    }

    private function rekapanPdRows(Collection $pd): Collection
    {
        return $pd->map(fn (PermintaanDana $permintaanDana): array => $this->rekapanPdRow($permintaanDana))->values();
    }

    private function rekapanPdRow(PermintaanDana $pd): array
    {
        return [
            'id' => $pd->id,
            'no_pd' => $pd->no_pd,
            'tujuan' => $pd->tujuan,
            'nominal' => (float) $pd->items->sum('total'),
            'jumlah_realisasi' => (float) ($pd->jumlah_realisasi ?? 0),
            'status' => $pd->status->value,
            'status_label' => $pd->status->label(),
            'dibuat_oleh' => $pd->createdBy?->name ?? '-',
            'diapprove_oleh' => $pd->approvedBy?->name ?? '-',
            'tanggal' => $pd->plan_pembayaran?->format('Y-m-d'),
        ];
    }

    private function rekapanPdSummary(Collection $rows): array
    {
        return [
            'total_pd' => $rows->count(),
            'total_nominal' => (float) $rows->sum('nominal'),
            'total_realisasi' => (float) $rows->sum('jumlah_realisasi'),
        ];
    }

    private function profitRows(Collection $quotations): Collection
    {
        return $quotations->map(fn (Quotation $quotation): array => $this->profitRow($quotation))->values();
    }

    private function profitRow(Quotation $quotation): array
    {
        $total = $quotation->total;
        $profit = $quotation->total_profit;

        return [
            'id' => $quotation->id,
            'no_quotation' => $quotation->no_quotation,
            'customer' => $quotation->customer?->nama_customer ?? '-',
            'total_nilai' => $total,
            'total_hpp' => $quotation->total_hpp,
            'profit' => $profit,
            'margin' => $total > 0 ? round(($profit / $total) * 100, 2) : 0.0,
            'tanggal' => $quotation->tgl_quotation?->format('Y-m-d'),
            'bulan' => $quotation->tgl_quotation?->format('Y-m') ?? '',
        ];
    }

    private function profitSummary(Collection $rows): array
    {
        $totalNilai = (float) $rows->sum('total_nilai');
        $totalProfit = (float) $rows->sum('profit');

        return [
            'total_nilai' => $totalNilai,
            'total_hpp' => (float) $rows->sum('total_hpp'),
            'total_profit' => $totalProfit,
            'rata_rata_margin' => $totalNilai > 0 ? round(($totalProfit / $totalNilai) * 100, 2) : 0.0,
        ];
    }

    private function outstandingRows(Collection $invoices): Collection
    {
        return $invoices->map(fn (Invoice $invoice): array => $this->outstandingRow($invoice))->values();
    }

    private function outstandingRow(Invoice $invoice): array
    {
        $dueDate = $invoice->tgl_jatuh_tempo;
        $hariTersisa = $dueDate ? now()->startOfDay()->diffInDays($dueDate->copy()->startOfDay(), false) : null;

        return [
            'id' => $invoice->id,
            'no_invoice' => $invoice->no_dokumen,
            'customer' => $invoice->customer?->nama_customer ?? '-',
            'total_nilai' => (float) $invoice->total_nilai,
            'sudah_dibayar' => (float) $invoice->jumlah_bayar,
            'sisa' => max((float) $invoice->grand_total - (float) $invoice->jumlah_bayar, 0),
            'metode_bayar' => $invoice->metode_pembayaran->label(),
            'jatuh_tempo' => $dueDate?->format('Y-m-d'),
            'hari_tersisa' => $hariTersisa,
            'due_badge' => $hariTersisa === null ? 'normal' : ($hariTersisa < 0 ? 'overdue' : ($hariTersisa <= 7 ? 'soon' : 'normal')),
        ];
    }

    private function outstandingSummary(Collection $rows): array
    {
        return [
            'total_outstanding' => (float) $rows->sum('sisa'),
            'total_invoice_belum_lunas' => $rows->count(),
        ];
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $rows
     * @return array<int, array{month: string, profit: float}>
     */
    private function profitChart(Collection $rows): array
    {
        $start = CarbonImmutable::now()->startOfMonth()->subMonths(11);
        $grouped = $rows->groupBy('bulan');

        return collect(range(0, 11))
            ->map(function (int $offset) use ($start, $grouped): array {
                $month = $start->addMonths($offset);
                $key = $month->format('Y-m');

                return [
                    'month' => $month->format('M Y'),
                    'profit' => (float) $grouped->get($key, collect())->sum('profit'),
                ];
            })
            ->values()
            ->all();
    }

    private function applyCustomerFilter(Builder $query, Request $request, string $column): void
    {
        $query->when($request->filled('customer_id'), fn (Builder $builder) => $builder->where($column, $request->integer('customer_id')));
    }

    private function applyDateRange(Builder $query, Request $request, string $column): void
    {
        $query->when($request->filled('date_from'), fn (Builder $builder) => $builder->whereDate($column, '>=', $request->date('date_from')->toDateString()));
        $query->when($request->filled('date_to'), fn (Builder $builder) => $builder->whereDate($column, '<=', $request->date('date_to')->toDateString()));
    }

    private function scopeSalesOwned(Builder $query, Request $request, string $column): void
    {
        if ($request->user()->hasRole('Sales') && ! $this->canSeeAll($request)) {
            $query->where($column, $request->user()->id);
        }
    }

    private function scopeSalesOwnedRelation(Builder $query, Request $request, string $relation): void
    {
        if ($request->user()->hasRole('Sales') && ! $this->canSeeAll($request)) {
            $query->whereHas($relation, fn (Builder $builder) => $builder->where('created_by', $request->user()->id));
        }
    }

    private function scopeCreatedBy(Builder $query, Request $request, string $column): void
    {
        if ($request->user()->hasRole('Sales') && ! $this->canSeeAll($request)) {
            $query->where($column, $request->user()->id);
        }
    }

    private function canSeeAll(Request $request): bool
    {
        return $request->user()->hasAnyRole(['Superadmin', 'Manager']);
    }

    private function authorizeReport(Request $request, string $permission): void
    {
        abort_unless($request->user()->can($permission), 403);
    }

    /**
     * @return array<string, mixed>
     */
    private function baseProps(Request $request, string $routeName, string $exportType): array
    {
        return [
            'filters' => $request->only([
                'search',
                'customer_id',
                'status',
                'status_pembayaran',
                'status_supply',
                'tipe_dokumen',
                'tipe_order',
                'metode_pembayaran',
                'kategori',
                'mode',
                'date_from',
                'date_to',
                'per_page',
            ]),
            'routeName' => $routeName,
            'exportType' => $exportType,
        ];
    }

    /**
     * @return array<int, array{id: int, label: string}>
     */
    private function customers(): array
    {
        return Customer::query()
            ->orderBy('nama_customer')
            ->get(['id', 'kode_customer', 'nama_customer'])
            ->map(fn (Customer $customer): array => [
                'id' => $customer->id,
                'label' => "{$customer->kode_customer} - {$customer->nama_customer}",
            ])
            ->all();
    }

    private function perPage(Request $request): int
    {
        return min(max((int) $request->integer('per_page', 10), 5), 100);
    }

    private function fileName(string $name): string
    {
        return $name.'_'.now()->format('Ymd').'.xlsx';
    }
}
