import DataTable from '@/Components/Data/DataTable';
import PageHeader from '@/Components/PageHeader';
import StatusBadge from '@/Components/StatusBadge';
import { IconButton } from '@/Components/UiPolish';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Select } from '@/Components/ui/select';
import AppLayout from '@/Layouts/AppLayout';
import { formatRupiah } from '@/utils/currency';
import { Head, Link, router, usePage } from '@inertiajs/react';
import { Download, Eye, FilePlus2 } from 'lucide-react';

export default function Index({ purchaseOrders, filters, vendors, statuses }) {
    const permissions = usePage().props.auth.user.permissions ?? [];
    const columns = [
        { key: 'no_purchase_order', label: 'No. PO', sortable: true },
        { key: 'customer', label: 'Customer' },
        { key: 'vendor', label: 'Vendor' },
        { key: 'tgl_po', label: 'Tanggal', sortable: true },
        { key: 'total', label: 'Total', render: (row) => formatRupiah(row.total) },
        { key: 'status', label: 'Status', sortable: true, render: (row) => <StatusBadge value={row.status}>{row.status_label}</StatusBadge> },
        {
            key: 'actions',
            label: 'Aksi',
            render: (row) => (
                <div className="flex justify-end gap-2">
                    <IconButton href={route('purchase-orders.show', row.id)} title="Lihat detail" icon={Eye} />
                    {permissions.includes('download_pdf_purchase_order') && row.status === 'APPROVED' && (
                        <Button asChild size="icon" variant="outline" title="Download PDF" aria-label="Download PDF">
                            <a href={route('purchase-orders.download', row.id)}><Download className="h-4 w-4" /></a>
                        </Button>
                    )}
                </div>
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
                emptyText="Belum ada purchase order"
            />
            <DataTable
                data={purchaseOrders}
                columns={columns}
                filters={filters}
                routeName="purchase-orders.index"
                filterSlot={(
                    <>
                        <Select value={filters.vendor_id ?? ''} onChange={(e) => router.get(route('purchase-orders.index'), { ...filters, vendor_id: e.target.value, page: 1 }, { preserveState: true, replace: true })} className="w-full sm:w-48">
                            <option value="">Semua Vendor</option>
                            {vendors.map((vendor) => <option key={vendor.id} value={vendor.id}>{vendor.label}</option>)}
                        </Select>
                        <Select value={filters.status ?? ''} onChange={(e) => router.get(route('purchase-orders.index'), { ...filters, status: e.target.value, page: 1 }, { preserveState: true, replace: true })} className="w-full sm:w-48">
                            <option value="">Semua Status</option>
                            {statuses.map((status) => <option key={status.value} value={status.value}>{status.label}</option>)}
                        </Select>
                        <Input type="date" value={filters.date_from ?? ''} onChange={(e) => router.get(route('purchase-orders.index'), { ...filters, date_from: e.target.value, page: 1 }, { preserveState: true, replace: true })} className="w-full sm:w-40" />
                        <Input type="date" value={filters.date_to ?? ''} onChange={(e) => router.get(route('purchase-orders.index'), { ...filters, date_to: e.target.value, page: 1 }, { preserveState: true, replace: true })} className="w-full sm:w-40" />
                    </>
                )}
            />
        </AppLayout>
    );
}
