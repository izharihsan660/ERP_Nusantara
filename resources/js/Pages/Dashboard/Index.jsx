import SummaryCards from '@/Components/Laporan/SummaryCards';
import PageHeader from '@/Components/PageHeader';
import { Button } from '@/Components/ui/button';
import AppLayout from '@/Layouts/AppLayout';
import { formatRupiah } from '@/utils/currency';
import { Head, Link } from '@inertiajs/react';
import { Bar, BarChart, CartesianGrid, Line, LineChart, ResponsiveContainer, Tooltip, XAxis, YAxis } from 'recharts';
import { Eye } from 'lucide-react';

const badgeStyles = {
    DRAFT: 'bg-slate-100 text-slate-700',
    OPEN: 'bg-sky-100 text-sky-700',
    PENDING_APPROVAL: 'bg-amber-100 text-amber-700',
    BELUM: 'bg-red-100 text-red-700',
    SEBAGIAN: 'bg-amber-100 text-amber-700',
    APPROVED: 'bg-green-100 text-green-700',
    LUNAS: 'bg-green-100 text-green-700',
    PAID: 'bg-emerald-100 text-emerald-700',
    VOID: 'bg-slate-100 text-slate-500 line-through',
};

function StatusPill({ value, children }) {
    return (
        <span className={`inline-flex rounded-full px-2.5 py-1 text-xs font-medium ${badgeStyles[value] ?? 'bg-slate-100 text-slate-700'}`}>
            {children ?? value}
        </span>
    );
}

function Table({ columns, rows, emptyText = 'Data belum tersedia.' }) {
    return (
        <div className="overflow-hidden rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))]">
            <div className="overflow-x-auto">
                <table className="min-w-full table-fixed text-sm">
                    <thead className="bg-[hsl(var(--muted))]">
                        <tr>
                            {columns.map((column) => (
                                <th key={column.key} className="whitespace-nowrap px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-[hsl(var(--muted-foreground))]">
                                    {column.label}
                                </th>
                            ))}
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-[hsl(var(--border))]">
                        {rows.length === 0 && (
                            <tr>
                                <td colSpan={columns.length} className="px-4 py-10 text-center text-[hsl(var(--muted-foreground))]">{emptyText}</td>
                            </tr>
                        )}
                        {rows.map((row) => (
                            <tr key={row.id} className="hover:bg-[hsl(var(--accent))]/50">
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
        </div>
    );
}

function ChartPanel({ title, children }) {
    return (
        <div className="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] p-6">
            <h2 className="mb-4 text-sm font-semibold text-[hsl(var(--foreground))]">{title}</h2>
            <div className="h-72">{children}</div>
        </div>
    );
}

function SuperadminDashboard({ dashboard }) {
    return (
        <>
            <SummaryCards items={dashboard.cards} />
            <Table
                rows={dashboard.activities ?? []}
                columns={[
                    { key: 'user', label: 'User' },
                    { key: 'aksi', label: 'Aksi' },
                    { key: 'dokumen', label: 'Dokumen' },
                    { key: 'waktu', label: 'Waktu' },
                ]}
            />
        </>
    );
}

function SalesDashboard({ dashboard }) {
    return (
        <>
            <SummaryCards items={dashboard.cards} />
            <Table
                rows={dashboard.quotations ?? []}
                columns={[
                    { key: 'no_quotation', label: 'No. Quotation' },
                    { key: 'customer', label: 'Customer' },
                    { key: 'total', label: 'Total', render: (row) => formatRupiah(row.total) },
                    { key: 'status', label: 'Status', render: (row) => <StatusPill value={row.status}>{row.status_label}</StatusPill> },
                ]}
            />
        </>
    );
}

function GudangDashboard({ dashboard }) {
    return (
        <>
            <SummaryCards items={dashboard.cards} />
            <Table
                rows={dashboard.wip ?? []}
                columns={[
                    { key: 'no_wip', label: 'No. WIP' },
                    { key: 'tipe', label: 'Tipe' },
                    { key: 'quotation', label: 'Quotation' },
                    { key: 'customer', label: 'Customer' },
                    { key: 'tanggal', label: 'Tanggal' },
                ]}
            />
        </>
    );
}

function FinanceDashboard({ dashboard }) {
    const invoiceColumns = [
        { key: 'no_invoice', label: 'No. Invoice' },
        { key: 'customer', label: 'Customer' },
        { key: 'total', label: 'Total', render: (row) => formatRupiah(row.total) },
        { key: 'jatuh_tempo', label: 'Jatuh Tempo' },
    ];

    return (
        <div className="space-y-6">
            <SummaryCards items={dashboard.cards} />
            <Table rows={dashboard.due_invoices ?? []} columns={invoiceColumns} />
            <Table
                rows={dashboard.outstanding_invoices ?? []}
                columns={[
                    { key: 'no_invoice', label: 'No. Invoice' },
                    { key: 'customer', label: 'Customer' },
                    { key: 'total', label: 'Total', render: (row) => formatRupiah(row.total) },
                    { key: 'status', label: 'Status', render: (row) => <StatusPill value={row.status}>{row.status_label}</StatusPill> },
                    { key: 'aksi', label: 'Aksi', render: () => <Button asChild size="icon" variant="outline" title="Outstanding"><Link href={route('laporan.outstanding')}><Eye className="h-4 w-4" /></Link></Button> },
                ]}
            />
        </div>
    );
}

function ProcurementDashboard({ dashboard }) {
    return (
        <>
            <SummaryCards items={dashboard.cards} />
            <Table
                rows={dashboard.pd ?? []}
                columns={[
                    { key: 'no_pd', label: 'No. PD' },
                    { key: 'kategori', label: 'Kategori' },
                    { key: 'nominal', label: 'Nominal', render: (row) => formatRupiah(row.nominal) },
                    { key: 'status', label: 'Status', render: (row) => <StatusPill value={row.status}>{row.status_label}</StatusPill> },
                ]}
            />
        </>
    );
}

function ManagerDashboard({ dashboard }) {
    return (
        <div className="space-y-6">
            <SummaryCards items={dashboard.cards} />
            <div className="grid gap-6 xl:grid-cols-2">
                <ChartPanel title="Tren Penjualan per Bulan">
                    <ResponsiveContainer width="100%" height="100%">
                        <LineChart data={dashboard.sales_trend ?? []}>
                            <CartesianGrid strokeDasharray="3 3" stroke="hsl(var(--border))" vertical={false} />
                            <XAxis dataKey="month" axisLine={false} tickLine={false} />
                            <YAxis tickFormatter={(value) => `${Number(value) / 1000000}jt`} axisLine={false} tickLine={false} />
                            <Tooltip formatter={(value) => [formatRupiah(value), 'Total Quotation']} contentStyle={{ borderRadius: 8, border: '1px solid hsl(var(--border))' }} />
                            <Line type="monotone" dataKey="value" stroke="#2563eb" strokeWidth={2} dot={false} />
                        </LineChart>
                    </ResponsiveContainer>
                </ChartPanel>
                <ChartPanel title="Profit per Bulan">
                    <ResponsiveContainer width="100%" height="100%">
                        <BarChart data={dashboard.profit_trend ?? []}>
                            <CartesianGrid strokeDasharray="3 3" stroke="hsl(var(--border))" vertical={false} />
                            <XAxis dataKey="month" axisLine={false} tickLine={false} />
                            <YAxis tickFormatter={(value) => `${Number(value) / 1000000}jt`} axisLine={false} tickLine={false} />
                            <Tooltip formatter={(value) => [formatRupiah(value), 'Profit']} contentStyle={{ borderRadius: 8, border: '1px solid hsl(var(--border))' }} />
                            <Bar dataKey="value" fill="#0f766e" radius={[4, 4, 0, 0]} />
                        </BarChart>
                    </ResponsiveContainer>
                </ChartPanel>
            </div>
            <Table
                rows={dashboard.pd_pending ?? []}
                columns={[
                    { key: 'no_pd', label: 'No. PD' },
                    { key: 'kategori', label: 'Kategori' },
                    { key: 'nominal', label: 'Nominal', render: (row) => formatRupiah(row.nominal) },
                    { key: 'dibuat_oleh', label: 'Dibuat oleh' },
                    { key: 'aksi', label: 'Aksi', render: (row) => <Button asChild size="icon" variant="outline" title="Lihat PD"><Link href={route('permintaan-dana.show', row.id)}><Eye className="h-4 w-4" /></Link></Button> },
                ]}
            />
        </div>
    );
}

function todayLabel() {
    return new Intl.DateTimeFormat('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }).format(new Date());
}

export default function Dashboard({ dashboard }) {
    const components = {
        superadmin: SuperadminDashboard,
        sales: SalesDashboard,
        gudang: GudangDashboard,
        finance: FinanceDashboard,
        procurement: ProcurementDashboard,
        manager: ManagerDashboard,
    };
    const RoleDashboard = components[dashboard.role] ?? SalesDashboard;

    return (
        <AppLayout title="Dashboard">
            <Head title="Dashboard" />
            <PageHeader title="Dashboard" description={`Ringkasan operasional hari ini, ${todayLabel()}.`} />
            <RoleDashboard dashboard={dashboard} />
        </AppLayout>
    );
}
