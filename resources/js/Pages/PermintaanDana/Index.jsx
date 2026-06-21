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

export default function Index({ permintaanDana, filters, statuses }) {
    const permissions = usePage().props.auth.user.permissions ?? [];
    const columns = [
        { key: 'no_pd', label: 'No. PD', sortable: true },
        { key: 'tujuan', label: 'Tujuan', sortable: true },
        { key: 'nominal', label: 'Total', sortable: true, render: (row) => formatRupiah(row.nominal ?? row.total) },
        { key: 'status', label: 'Status', sortable: true, render: (row) => <StatusBadge value={row.status}>{row.status_label}</StatusBadge> },
        { key: 'created_by', label: 'Dibuat oleh' },
        { key: 'plan_pembayaran', label: 'Tanggal', sortable: true },
        {
            key: 'actions',
            label: 'Aksi',
            render: (row) => (
                <div className="flex justify-end gap-2">
                    <IconButton href={route('permintaan-dana.show', row.id)} title="Lihat detail" icon={Eye} />
                    <Button asChild size="icon" variant="outline" title="Download PDF" aria-label="Download PDF">
                        <a href={route('permintaan-dana.download', row.id)}><Download className="h-4 w-4" /></a>
                    </Button>
                </div>
            ),
        },
    ];

    return (
        <AppLayout title="Permintaan Dana">
            <Head title="Permintaan Dana" />
            <PageHeader
                title="Permintaan Dana"
                description="Kelola draft, approval, dan realisasi permintaan pencairan dana internal."
                actions={permissions.includes('buat_pd') && (
                    <Button asChild>
                        <Link href={route('permintaan-dana.create')}><FilePlus2 className="h-4 w-4" />Buat PD</Link>
                    </Button>
                )}
                emptyText="Belum ada permintaan dana"
            />
            <DataTable
                data={permintaanDana}
                columns={columns}
                filters={filters}
                routeName="permintaan-dana.index"
                filterSlot={(
                    <>
                        <Select value={filters.status ?? ''} onChange={(e) => router.get(route('permintaan-dana.index'), { ...filters, status: e.target.value, page: 1 }, { preserveState: true, replace: true })} className="w-full sm:w-48">
                            <option value="">Semua Status</option>
                            {statuses.map((status) => <option key={status.value} value={status.value}>{status.label}</option>)}
                        </Select>
                        <Input type="date" value={filters.date_from ?? ''} onChange={(e) => router.get(route('permintaan-dana.index'), { ...filters, date_from: e.target.value, page: 1 }, { preserveState: true, replace: true })} className="w-full sm:w-40" />
                        <Input type="date" value={filters.date_to ?? ''} onChange={(e) => router.get(route('permintaan-dana.index'), { ...filters, date_to: e.target.value, page: 1 }, { preserveState: true, replace: true })} className="w-full sm:w-40" />
                    </>
                )}
            />
        </AppLayout>
    );
}
