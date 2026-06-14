import DataTable from '@/Components/Data/DataTable';
import PageHeader from '@/Components/PageHeader';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Select } from '@/Components/ui/select';
import AppLayout from '@/Layouts/AppLayout';
import { Head, Link, router, usePage } from '@inertiajs/react';
import { Eye, FilePlus2, Ban } from 'lucide-react';

const statusStyles = {
    DRAFT: 'bg-slate-100 text-slate-700 ring-slate-200',
    PENDING_APPROVAL: 'bg-amber-50 text-amber-700 ring-amber-200',
    APPROVED: 'bg-emerald-50 text-emerald-700 ring-emerald-200',
    REJECTED: 'bg-red-50 text-red-700 ring-red-200',
    VOID: 'bg-zinc-800 text-white ring-zinc-800',
};

function StatusBadge({ status, label }) {
    return (
        <span className={`inline-flex rounded-full px-2 py-1 text-xs font-medium ring-1 ring-inset ${statusStyles[status] ?? statusStyles.DRAFT}`}>
            {label ?? status}
        </span>
    );
}

function money(value) {
    return Number(value ?? 0).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

export default function Index({ quotations, filters, customers, statuses }) {
    const permissions = usePage().props.auth.user.permissions ?? [];
    const columns = [
        { key: 'no_quotation', label: 'No. Quotation', sortable: true },
        { key: 'customer', label: 'Customer' },
        { key: 'tgl_quotation', label: 'Tanggal', sortable: true },
        { key: 'revisi', label: 'Revisi', sortable: true },
        { key: 'total', label: 'Total', render: (row) => money(row.total) },
        { key: 'status', label: 'Status', sortable: true, render: (row) => <StatusBadge status={row.status} label={row.status_label} /> },
        {
            key: 'actions',
            label: 'Aksi',
            render: (row) => (
                <div className="flex gap-2">
                    <Button asChild size="icon" variant="outline" title="Lihat detail">
                        <Link href={route('quotations.show', row.id)}><Eye className="h-4 w-4" /></Link>
                    </Button>
                    {permissions.includes('Quotation void') && row.status !== 'VOID' && (
                        <Button asChild size="icon" variant="destructive" title="Void quotation">
                            <Link href={route('quotations.show', row.id)}><Ban className="h-4 w-4" /></Link>
                        </Button>
                    )}
                </div>
            ),
        },
    ];

    return (
        <AppLayout title="Quotation">
            <Head title="Quotation" />
            <PageHeader
                title="Quotation"
                description="Kelola penawaran harga dan approval dokumen."
                actions={permissions.includes('Quotation buat') && (
                    <Button asChild>
                        <Link href={route('quotations.create')}><FilePlus2 className="h-4 w-4" />Buat Quotation</Link>
                    </Button>
                )}
            />
            <DataTable
                data={quotations}
                columns={columns}
                filters={filters}
                routeName="quotations.index"
                filterSlot={(
                    <>
                        <Select value={filters.customer_id ?? ''} onChange={(e) => router.get(route('quotations.index'), { ...filters, customer_id: e.target.value, page: 1 }, { preserveState: true, replace: true })} className="w-48">
                            <option value="">Semua Customer</option>
                            {customers.map((customer) => <option key={customer.id} value={customer.id}>{customer.label}</option>)}
                        </Select>
                        <Select value={filters.status ?? ''} onChange={(e) => router.get(route('quotations.index'), { ...filters, status: e.target.value, page: 1 }, { preserveState: true, replace: true })} className="w-48">
                            <option value="">Semua Status</option>
                            {statuses.map((status) => <option key={status.value} value={status.value}>{status.label}</option>)}
                        </Select>
                        <Input type="date" value={filters.date_from ?? ''} onChange={(e) => router.get(route('quotations.index'), { ...filters, date_from: e.target.value, page: 1 }, { preserveState: true, replace: true })} className="w-40" />
                        <Input type="date" value={filters.date_to ?? ''} onChange={(e) => router.get(route('quotations.index'), { ...filters, date_to: e.target.value, page: 1 }, { preserveState: true, replace: true })} className="w-40" />
                    </>
                )}
            />
        </AppLayout>
    );
}
