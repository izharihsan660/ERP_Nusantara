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
import { Copy, Download, Eye, FilePlus2 } from 'lucide-react';

export default function Index({ quotations, filters, customers, statuses }) {
    const permissions = usePage().props.auth.user.permissions ?? [];
    const columns = [
        { key: 'no_quotation', label: 'No. Quotation', sortable: true },
        { key: 'customer', label: 'Customer' },
        { key: 'tgl_quotation', label: 'Tanggal', sortable: true },
        { key: 'revisi', label: 'Revisi', sortable: true },
        { key: 'total', label: 'Total', render: (row) => formatRupiah(row.total) },
        { key: 'status', label: 'Status', sortable: true, render: (row) => <StatusBadge value={row.status}>{row.status_label}</StatusBadge> },
        {
            key: 'actions',
            label: 'Aksi',
            render: (row) => (
                <div className="flex justify-end gap-2">
                    <IconButton href={route('quotations.show', row.id)} title="Lihat detail" icon={Eye} />
                    {permissions.includes('download_pdf_quotation') && row.status === 'APPROVED' && (
                        <Button asChild size="icon" variant="outline" title="Download PDF" aria-label="Download PDF">
                            <a href={route('quotations.download', row.id)}><Download className="h-4 w-4" /></a>
                        </Button>
                    )}
                    {permissions.includes('buat_quotation') && (
                        <IconButton href={route('quotations.duplicate', row.id)} method="post" title="Duplikasi" icon={Copy} />
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
                actions={permissions.includes('buat_quotation') && (
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
                        <Select value={filters.customer_id ?? ''} onChange={(e) => router.get(route('quotations.index'), { ...filters, customer_id: e.target.value, page: 1 }, { preserveState: true, replace: true })} className="w-full sm:w-48">
                            <option value="">Semua Customer</option>
                            {customers.map((customer) => <option key={customer.id} value={customer.id}>{customer.label}</option>)}
                        </Select>
                        <Select value={filters.status ?? ''} onChange={(e) => router.get(route('quotations.index'), { ...filters, status: e.target.value, page: 1 }, { preserveState: true, replace: true })} className="w-full sm:w-48">
                            <option value="">Semua Status</option>
                            {statuses.map((status) => <option key={status.value} value={status.value}>{status.label}</option>)}
                        </Select>
                        <Input type="date" value={filters.date_from ?? ''} onChange={(e) => router.get(route('quotations.index'), { ...filters, date_from: e.target.value, page: 1 }, { preserveState: true, replace: true })} className="w-full sm:w-40" />
                        <Input type="date" value={filters.date_to ?? ''} onChange={(e) => router.get(route('quotations.index'), { ...filters, date_to: e.target.value, page: 1 }, { preserveState: true, replace: true })} className="w-full sm:w-40" />
                    </>
                )}
                emptyText="Belum ada quotation"
            />
        </AppLayout>
    );
}
