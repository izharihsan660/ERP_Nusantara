import DataTable from '@/Components/Data/DataTable';
import PageHeader from '@/Components/PageHeader';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Select } from '@/Components/ui/select';
import AppLayout from '@/Layouts/AppLayout';
import { formatRupiah } from '@/utils/currency';
import { Head, Link, router, usePage } from '@inertiajs/react';
import { Eye, FilePlus2 } from 'lucide-react';

const statusStyles = {
    DRAFT: 'bg-slate-100 text-slate-700 ring-slate-200',
    PENDING_APPROVAL: 'bg-amber-50 text-amber-700 ring-amber-200',
    APPROVED: 'bg-emerald-50 text-emerald-700 ring-emerald-200',
    VOID: 'bg-zinc-800 text-white ring-zinc-800',
};

function StatusBadge({ status, label }) {
    return (
        <span className={`inline-flex rounded-full px-2 py-1 text-xs font-medium ring-1 ring-inset ${statusStyles[status] ?? statusStyles.DRAFT}`}>
            {label ?? status}
        </span>
    );
}

export default function Index({ purchaseOrders, filters, vendors, statuses }) {
    const permissions = usePage().props.auth.user.permissions ?? [];
    const columns = [
        { key: 'no_purchase_order', label: 'No. PO', sortable: true },
        { key: 'vendor', label: 'Vendor' },
        { key: 'tgl_po', label: 'Tanggal', sortable: true },
        { key: 'total', label: 'Total', render: (row) => formatRupiah(row.total) },
        { key: 'status', label: 'Status', sortable: true, render: (row) => <StatusBadge status={row.status} label={row.status_label} /> },
        {
            key: 'actions',
            label: 'Aksi',
            render: (row) => (
                <Button asChild size="icon" variant="outline" title="Lihat detail">
                    <Link href={route('purchase-orders.show', row.id)}><Eye className="h-4 w-4" /></Link>
                </Button>
            ),
        },
    ];

    return (
        <AppLayout title="Purchase Order NAJ">
            <Head title="Purchase Order NAJ" />
            <PageHeader
                title="Purchase Order NAJ"
                description="Kelola purchase order NAJ ke vendor eksternal."
                actions={permissions.includes('buat_purchase_order') && (
                    <Button asChild>
                        <Link href={route('purchase-orders.create')}><FilePlus2 className="h-4 w-4" />Buat Purchase Order</Link>
                    </Button>
                )}
            />
            <DataTable
                data={purchaseOrders}
                columns={columns}
                filters={filters}
                routeName="purchase-orders.index"
                filterSlot={(
                    <>
                        <Select value={filters.vendor_id ?? ''} onChange={(e) => router.get(route('purchase-orders.index'), { ...filters, vendor_id: e.target.value, page: 1 }, { preserveState: true, replace: true })} className="w-48">
                            <option value="">Semua Vendor</option>
                            {vendors.map((vendor) => <option key={vendor.id} value={vendor.id}>{vendor.label}</option>)}
                        </Select>
                        <Select value={filters.status ?? ''} onChange={(e) => router.get(route('purchase-orders.index'), { ...filters, status: e.target.value, page: 1 }, { preserveState: true, replace: true })} className="w-48">
                            <option value="">Semua Status</option>
                            {statuses.map((status) => <option key={status.value} value={status.value}>{status.label}</option>)}
                        </Select>
                        <Input type="date" value={filters.date_from ?? ''} onChange={(e) => router.get(route('purchase-orders.index'), { ...filters, date_from: e.target.value, page: 1 }, { preserveState: true, replace: true })} className="w-40" />
                        <Input type="date" value={filters.date_to ?? ''} onChange={(e) => router.get(route('purchase-orders.index'), { ...filters, date_to: e.target.value, page: 1 }, { preserveState: true, replace: true })} className="w-40" />
                    </>
                )}
            />
        </AppLayout>
    );
}
