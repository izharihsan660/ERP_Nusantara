<?php

namespace App\Http\Controllers;

use App\Enums\InvoiceStatus;
use App\Enums\PDStatus;
use App\Enums\QuotationStatus;
use App\Enums\StatusPembayaran;
use App\Enums\StatusSupply;
use App\Models\ActivityLog;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Katalog;
use App\Models\PermintaanDana;
use App\Models\PurchaseOrder;
use App\Models\Quotation;
use App\Models\SalesOrder;
use App\Models\Spb;
use App\Models\User;
use App\Models\WipOrder;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $role = $this->dashboardRole($request);

        return Inertia::render('Dashboard/Index', [
            'dashboard' => match ($role) {
                'superadmin' => $this->superadminDashboard(),
                'manager' => $this->managerDashboard(),
                'finance' => $this->financeDashboard(),
                'procurement' => $this->procurementDashboard(),
                'gudang' => $this->gudangDashboard(),
                default => $this->salesDashboard($request),
            },
        ]);
    }

    private function dashboardRole(Request $request): string
    {
        $user = $request->user();

        foreach (['Superadmin', 'Manager', 'Finance', 'Procurement', 'Gudang', 'Sales'] as $role) {
            if ($user->hasRole($role)) {
                return str($role)->lower()->value();
            }
        }

        return 'sales';
    }

    /**
     * @return array<string, mixed>
     */
    private function superadminDashboard(): array
    {
        [$start, $end] = $this->currentMonth();

        return [
            'role' => 'superadmin',
            'cards' => [
                ['label' => 'Total user aktif', 'value' => User::query()->where('is_active', true)->count(), 'type' => 'number'],
                ['label' => 'Total customer', 'value' => Customer::query()->count(), 'type' => 'number'],
                ['label' => 'Total katalog barang', 'value' => Katalog::query()->count(), 'type' => 'number'],
                ['label' => 'Total transaksi bulan ini', 'value' => $this->monthlyTransactionCount($start, $end), 'type' => 'number'],
            ],
            'activities' => ActivityLog::query()
                ->with('user:id,name')
                ->latest('created_at')
                ->limit(10)
                ->get()
                ->map(fn (ActivityLog $log): array => [
                    'id' => $log->id,
                    'user' => $log->user?->name ?? '-',
                    'aksi' => $log->action,
                    'dokumen' => trim($log->model_type.' #'.$log->model_id),
                    'waktu' => $log->created_at?->format('Y-m-d H:i'),
                ]),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function salesDashboard(Request $request): array
    {
        [$start, $end] = $this->currentMonth();
        $userId = $request->user()->id;

        return [
            'role' => 'sales',
            'cards' => [
                ['label' => 'Total Quotation bulan ini', 'value' => Quotation::query()->where('created_by', $userId)->whereBetween('tgl_quotation', [$start, $end])->count(), 'type' => 'number'],
                ['label' => 'Quotation pending approval', 'value' => Quotation::query()->where('created_by', $userId)->where('status', QuotationStatus::PendingApproval)->count(), 'type' => 'number'],
                ['label' => 'Total Purchase Order bulan ini', 'value' => PurchaseOrder::query()->where('created_by', $userId)->whereBetween('tgl_po', [$start, $end])->count(), 'type' => 'number'],
                ['label' => 'WIP belum tersupply', 'value' => WipOrder::query()->whereHas('salesOrder', fn (Builder $query) => $query->where('created_by', $userId))->where('status_supply', StatusSupply::BelumTersupply)->count(), 'type' => 'number'],
            ],
            'quotations' => Quotation::query()
                ->with(['customer:id,nama_customer', 'items:id,quotation_id,jumlah'])
                ->where('created_by', $userId)
                ->latest('tgl_quotation')
                ->limit(5)
                ->get()
                ->map(fn (Quotation $quotation): array => [
                    'id' => $quotation->id,
                    'no_quotation' => $quotation->no_quotation,
                    'customer' => $quotation->customer?->nama_customer ?? '-',
                    'total' => $quotation->total,
                    'status' => $quotation->status->value,
                    'status_label' => $quotation->status->label(),
                ]),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function gudangDashboard(): array
    {
        [$start, $end] = $this->currentMonth();

        return [
            'role' => 'gudang',
            'cards' => [
                ['label' => 'WIP belum tersupply', 'value' => WipOrder::query()->where('status_supply', StatusSupply::BelumTersupply)->count(), 'type' => 'number'],
                ['label' => 'SPB dibuat bulan ini', 'value' => Spb::query()->whereBetween('tgl_spb', [$start, $end])->count(), 'type' => 'number'],
            ],
            'wip' => WipOrder::query()
                ->with(['salesOrder.quotation.customer:id,nama_customer'])
                ->where('status_supply', StatusSupply::BelumTersupply)
                ->latest('created_at')
                ->limit(10)
                ->get()
                ->map(fn (WipOrder $wip): array => [
                    'id' => $wip->id,
                    'no_wip' => $wip->no_wip,
                    'tipe' => $wip->tipe_order->label(),
                    'quotation' => $wip->salesOrder?->quotation?->no_quotation ?? '-',
                    'customer' => $wip->salesOrder?->quotation?->customer?->nama_customer ?? '-',
                    'tanggal' => $wip->created_at?->format('Y-m-d'),
                ]),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function financeDashboard(): array
    {
        [$start, $end] = $this->currentMonth();
        $dueStart = now()->toDateString();
        $dueEnd = now()->addDays(7)->toDateString();

        return [
            'role' => 'finance',
            'cards' => [
                ['label' => 'Invoice outstanding', 'value' => $this->invoiceOutstandingQuery()->count(), 'type' => 'number'],
                ['label' => 'Total tagihan bulan ini', 'value' => (float) Invoice::query()->where('status', InvoiceStatus::Active)->whereBetween('tgl_dokumen', [$start, $end])->sum('total_nilai'), 'type' => 'money'],
                ['label' => 'Invoice jatuh tempo H-7', 'value' => $this->invoiceOutstandingQuery()->whereBetween('tgl_jatuh_tempo', [$dueStart, $dueEnd])->count(), 'type' => 'number'],
                ['label' => 'Total sudah lunas bulan ini', 'value' => (float) Invoice::query()->where('status', InvoiceStatus::Active)->where('status_pembayaran', StatusPembayaran::Lunas)->whereBetween('tgl_bayar', [$start, $end])->sum('total_nilai'), 'type' => 'money'],
            ],
            'due_invoices' => $this->invoiceOutstandingQuery()
                ->with('customer:id,nama_customer')
                ->whereBetween('tgl_jatuh_tempo', [$dueStart, $dueEnd])
                ->orderBy('tgl_jatuh_tempo')
                ->limit(10)
                ->get()
                ->map(fn (Invoice $invoice): array => $this->invoiceDashboardRow($invoice)),
            'outstanding_invoices' => $this->invoiceOutstandingQuery()
                ->with('customer:id,nama_customer')
                ->oldest('tgl_jatuh_tempo')
                ->limit(10)
                ->get()
                ->map(fn (Invoice $invoice): array => $this->invoiceDashboardRow($invoice)),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function procurementDashboard(): array
    {
        [$start, $end] = $this->currentMonth();

        return [
            'role' => 'procurement',
            'cards' => [
                ['label' => 'PD pending approval', 'value' => PermintaanDana::query()->where('status', PDStatus::PendingApproval)->count(), 'type' => 'number'],
                ['label' => 'PD approved belum dibayar', 'value' => PermintaanDana::query()->where('status', PDStatus::Approved)->count(), 'type' => 'number'],
                ['label' => 'Total PD bulan ini', 'value' => (float) PermintaanDana::query()->whereBetween('tgl_pd', [$start, $end])->sum('nominal'), 'type' => 'money'],
            ],
            'pd' => PermintaanDana::query()
                ->latest('tgl_pd')
                ->limit(10)
                ->get()
                ->map(fn (PermintaanDana $pd): array => [
                    'id' => $pd->id,
                    'no_pd' => $pd->no_pd,
                    'kategori' => $pd->kategori->label(),
                    'nominal' => (float) $pd->nominal,
                    'status' => $pd->status->value,
                    'status_label' => $pd->status->label(),
                ]),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function managerDashboard(): array
    {
        [$start, $end] = $this->currentMonth();

        return [
            'role' => 'manager',
            'cards' => [
                ['label' => 'Total PO aktif bulan ini', 'value' => SalesOrder::query()->whereBetween('tgl_po', [$start, $end])->count(), 'type' => 'number'],
                ['label' => 'Total profit bulan ini', 'value' => (float) Invoice::query()->where('status', InvoiceStatus::Active)->whereBetween('tgl_dokumen', [$start, $end])->sum('total_profit'), 'type' => 'money'],
                ['label' => 'Outstanding pembayaran', 'value' => (float) $this->invoiceOutstandingQuery()->get()->sum(fn (Invoice $invoice): float => max((float) $invoice->total_nilai - (float) $invoice->jumlah_bayar, 0)), 'type' => 'money'],
                ['label' => 'PD menunggu approval', 'value' => PermintaanDana::query()->where('status', PDStatus::PendingApproval)->count(), 'type' => 'number'],
            ],
            'sales_trend' => $this->quotationMonthlySeries('total'),
            'profit_trend' => $this->quotationMonthlySeries('profit'),
            'pd_pending' => PermintaanDana::query()
                ->with('createdBy:id,name')
                ->where('status', PDStatus::PendingApproval)
                ->latest('tgl_pd')
                ->limit(10)
                ->get()
                ->map(fn (PermintaanDana $pd): array => [
                    'id' => $pd->id,
                    'no_pd' => $pd->no_pd,
                    'kategori' => $pd->kategori->label(),
                    'nominal' => (float) $pd->nominal,
                    'dibuat_oleh' => $pd->createdBy?->name ?? '-',
                ]),
        ];
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function currentMonth(): array
    {
        return [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()];
    }

    private function monthlyTransactionCount(string $start, string $end): int
    {
        return Quotation::query()->whereBetween('created_at', [$start, $end])->count()
            + SalesOrder::query()->whereBetween('created_at', [$start, $end])->count()
            + PurchaseOrder::query()->whereBetween('created_at', [$start, $end])->count()
            + WipOrder::query()->whereBetween('created_at', [$start, $end])->count()
            + Spb::query()->whereBetween('created_at', [$start, $end])->count()
            + Invoice::query()->whereBetween('created_at', [$start, $end])->count()
            + PermintaanDana::query()->whereBetween('created_at', [$start, $end])->count();
    }

    private function invoiceOutstandingQuery(): Builder
    {
        return Invoice::query()
            ->where('status', InvoiceStatus::Active)
            ->where('status_pembayaran', '!=', StatusPembayaran::Lunas);
    }

    /**
     * @return array<string, mixed>
     */
    private function invoiceDashboardRow(Invoice $invoice): array
    {
        return [
            'id' => $invoice->id,
            'no_invoice' => $invoice->no_dokumen,
            'customer' => $invoice->customer?->nama_customer ?? '-',
            'total' => (float) $invoice->total_nilai,
            'status' => $invoice->status_pembayaran->value,
            'status_label' => $invoice->status_pembayaran->label(),
            'jatuh_tempo' => $invoice->tgl_jatuh_tempo?->format('Y-m-d') ?? '-',
        ];
    }

    /**
     * @return array<int, array{month: string, value: float}>
     */
    private function quotationMonthlySeries(string $field): array
    {
        $start = CarbonImmutable::now()->startOfMonth()->subMonths(11);
        $quotations = Quotation::query()
            ->with('items:id,quotation_id,jumlah,profit')
            ->whereDate('tgl_quotation', '>=', $start->toDateString())
            ->get()
            ->groupBy(fn (Quotation $quotation): string => $quotation->tgl_quotation?->format('Y-m') ?? '');

        return collect(range(0, 11))
            ->map(function (int $offset) use ($start, $quotations, $field): array {
                $month = $start->addMonths($offset);
                $key = $month->format('Y-m');
                $items = $quotations->get($key, collect());

                $value = $items->sum(fn (Quotation $quotation): float => $field === 'profit' ? $quotation->total_profit : $quotation->total);

                return [
                    'month' => $month->format('M Y'),
                    'value' => (float) $value,
                ];
            })
            ->values()
            ->all();
    }
}
