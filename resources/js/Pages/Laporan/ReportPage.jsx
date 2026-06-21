import FilterSection from '@/Components/Laporan/FilterSection';
import SummaryCards from '@/Components/Laporan/SummaryCards';
import PageHeader from '@/Components/PageHeader';
import { Button } from '@/Components/ui/button';
import { Select } from '@/Components/ui/select';
import AppLayout from '@/Layouts/AppLayout';
import { formatRupiah } from '@/utils/currency';
import { Head, Link, router, usePage } from '@inertiajs/react';
import { Download, Eye } from 'lucide-react';

const reportTabs = [
    { label: 'Rekapan PO', routeName: 'laporan.rekapan-po', tab: 'rekapan-po' },
    { label: 'WIP', routeName: 'laporan.rekapan-wip', tab: 'rekapan-wip' },
    { label: 'SPB', routeName: 'laporan.rekapan-spb', tab: 'rekapan-spb' },
    { label: 'Invoice', routeName: 'laporan.rekapan-invoice', tab: 'rekapan-invoice' },
    { label: 'PD', routeName: 'laporan.rekapan-pd', tab: 'rekapan-pd' },
    { label: 'Profit', routeName: 'laporan.profit', tab: 'profit' },
    { label: 'Outstanding', routeName: 'laporan.outstanding', tab: 'outstanding' },
];

export function money(value) {
    return formatRupiah(value);
}

export function percent(value) {
    return `${Number(value ?? 0).toLocaleString('id-ID', { maximumFractionDigits: 2 })}%`;
}

const badgeStyles = {
    DRAFT: 'bg-slate-100 text-slate-700 ring-slate-200',
    OPEN: 'bg-blue-50 text-blue-700 ring-blue-200',
    ACTIVE: 'bg-blue-50 text-blue-700 ring-blue-200',
    SHIPPED: 'bg-emerald-50 text-emerald-700 ring-emerald-200',
    APPROVED: 'bg-emerald-50 text-emerald-700 ring-emerald-200',
    LUNAS: 'bg-emerald-50 text-emerald-700 ring-emerald-200',
    PAID: 'bg-emerald-50 text-emerald-700 ring-emerald-200',
    PENDING_APPROVAL: 'bg-amber-50 text-amber-700 ring-amber-200',
    BELUM: 'bg-amber-50 text-amber-700 ring-amber-200',
    SEBAGIAN: 'bg-orange-50 text-orange-700 ring-orange-200',
    BELUM_TERSUPPLY: 'bg-amber-50 text-amber-700 ring-amber-200',
    TERSUPPLY: 'bg-emerald-50 text-emerald-700 ring-emerald-200',
    VOID: 'bg-zinc-800 text-white ring-zinc-800',
    overdue: 'bg-red-50 text-red-700 ring-red-200',
    soon: 'bg-amber-50 text-amber-700 ring-amber-200',
    normal: 'bg-slate-100 text-slate-700 ring-slate-200',
};

export function StatusPill({ value, children }) {
    return (
        <span className={`inline-flex rounded-full px-2 py-1 text-xs font-medium ring-1 ring-inset ${badgeStyles[value] ?? badgeStyles.normal}`}>
            {children ?? value}
        </span>
    );
}

function clean(values) {
    return Object.fromEntries(Object.entries(values ?? {}).filter(([, value]) => value !== null && value !== undefined && value !== ''));
}

export default function ReportPage({
    title,
    description,
    data,
    filters,
    routeName,
    exportType,
    fields,
    summaryItems,
    columns,
    chart,
}) {
    const rows = data?.data ?? [];
    const isConsolidated = routeName === 'laporan.index';
    const exportQuery = new URLSearchParams(clean({ ...filters, tab: isConsolidated ? exportType : undefined })).toString();
    const exportUrl = `${route('laporan.export', exportType)}${exportQuery ? `?${exportQuery}` : ''}`;
    const currentUrl = usePage().url;

    return (
        <AppLayout title={title}>
            <Head title={title} />
            <PageHeader
                title={title}
                description={description}
                actions={(
                    <Button asChild>
                        <a href={exportUrl}><Download className="h-4 w-4" />Export Excel</a>
                    </Button>
                )}
            />

            <div className="mb-6 overflow-x-auto border-b border-[hsl(var(--border))]">
                <div className="flex min-w-max gap-6">
                    {reportTabs.map((tab) => {
                        const isActive = isConsolidated ? tab.tab === exportType : tab.routeName === routeName;

                        return (
                            <Link
                                key={tab.routeName}
                                href={isConsolidated ? route('laporan.index', { tab: tab.tab }) : route(tab.routeName)}
                                className={`border-b-2 px-1 pb-3 text-sm font-medium transition ${isActive ? 'border-[hsl(var(--primary))] text-[hsl(var(--foreground))]' : 'border-transparent text-[hsl(var(--muted-foreground))] hover:text-[hsl(var(--foreground))]'}`}
                            >
                                {tab.label}
                            </Link>
                        );
                    })}
                </div>
            </div>

            <FilterSection routeName={routeName} filters={filters} fields={fields} />
            <SummaryCards items={summaryItems} />
            {chart}

            <div className="overflow-hidden rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] shadow-sm">
                <div className="overflow-x-auto">
                    <table className="min-w-full table-fixed divide-y divide-[hsl(var(--border))] text-sm">
                        <thead className="bg-[hsl(var(--muted))]/60">
                            <tr>
                                {columns.map((column) => (
                                    <th key={column.key} className="whitespace-nowrap px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-[hsl(var(--muted-foreground))]">
                                        {column.label}
                                    </th>
                                ))}
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-[hsl(var(--border))]">
                            {rows.length === 0 && (
                                <tr>
                                    <td colSpan={columns.length} className="px-4 py-12 text-center text-[hsl(var(--muted-foreground))]">Data belum tersedia.</td>
                                </tr>
                            )}
                            {rows.map((row) => (
                                <tr key={row.id} className="hover:bg-[hsl(var(--muted))]/35">
                                    {columns.map((column) => (
                                        <td key={column.key} className="whitespace-nowrap px-4 py-3 text-[hsl(var(--foreground))]">
                                            {column.render ? column.render(row) : row[column.key]}
                                        </td>
                                    ))}
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>

                <div className="flex flex-col gap-3 border-t border-[hsl(var(--border))] p-4 text-sm text-[hsl(var(--muted-foreground))] sm:flex-row sm:items-center sm:justify-between">
                    <div>Menampilkan {data?.from ?? 0}-{data?.to ?? 0} dari {data?.total ?? 0}</div>
                    <div className="flex flex-wrap gap-1">
                        {(data?.links ?? []).map((link, index) => (
                            <Link
                                key={`${link.label}-${index}`}
                                href={link.url ?? currentUrl}
                                preserveScroll
                                preserveState
                                className={`rounded-md px-3 py-1.5 ${link.active ? 'bg-slate-950 text-white dark:bg-white dark:text-slate-950' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800'} ${!link.url ? 'pointer-events-none opacity-40' : ''}`}
                                dangerouslySetInnerHTML={{ __html: link.label }}
                            />
                        ))}
                    </div>
                    <Select
                        value={filters?.per_page ?? 10}
                        onChange={(event) => router.get(route(routeName), { ...filters, per_page: event.target.value, page: 1 }, { preserveState: true, replace: true })}
                        className="w-24"
                    >
                        {[10, 25, 50].map((value) => <option key={value} value={value}>{value}</option>)}
                    </Select>
                </div>
            </div>
        </AppLayout>
    );
}

export function DetailButton({ href }) {
    return (
        <Button asChild size="icon" variant="outline" title="Lihat detail">
            <Link href={href}><Eye className="h-4 w-4" /></Link>
        </Button>
    );
}
