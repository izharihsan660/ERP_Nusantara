import FilterSection from '@/Components/Laporan/FilterSection';
import SummaryCards from '@/Components/Laporan/SummaryCards';
import PageHeader from '@/Components/PageHeader';
import { Button } from '@/Components/ui/button';
import { Select } from '@/Components/ui/select';
import AppLayout from '@/Layouts/AppLayout';
import { formatRupiah } from '@/utils/currency';
import { Head, Link, router, usePage } from '@inertiajs/react';
import { Download, Eye } from 'lucide-react';

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
    const exportQuery = new URLSearchParams(clean(filters)).toString();
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

            <FilterSection routeName={routeName} filters={filters} fields={fields} />
            <SummaryCards items={summaryItems} />
            {chart}

            <div className="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-950">
                <div className="overflow-x-auto">
                    <table className="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                        <thead className="bg-slate-50 dark:bg-slate-900">
                            <tr>
                                {columns.map((column) => (
                                    <th key={column.key} className="whitespace-nowrap px-4 py-3 text-left font-medium text-slate-600 dark:text-slate-300">
                                        {column.label}
                                    </th>
                                ))}
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-slate-100 dark:divide-slate-900">
                            {rows.length === 0 && (
                                <tr>
                                    <td colSpan={columns.length} className="px-4 py-10 text-center text-slate-500">Data belum tersedia.</td>
                                </tr>
                            )}
                            {rows.map((row) => (
                                <tr key={row.id} className="hover:bg-slate-50 dark:hover:bg-slate-900/60">
                                    {columns.map((column) => (
                                        <td key={column.key} className="whitespace-nowrap px-4 py-3 text-slate-700 dark:text-slate-200">
                                            {column.render ? column.render(row) : row[column.key]}
                                        </td>
                                    ))}
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>

                <div className="flex flex-col gap-3 border-t border-slate-200 p-4 text-sm text-slate-500 dark:border-slate-800 sm:flex-row sm:items-center sm:justify-between">
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
