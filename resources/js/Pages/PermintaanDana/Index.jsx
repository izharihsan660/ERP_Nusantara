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
    APPROVED: 'bg-sky-50 text-sky-700 ring-sky-200',
    REJECTED: 'bg-red-50 text-red-700 ring-red-200',
    PAID: 'bg-emerald-50 text-emerald-700 ring-emerald-200',
    VOID: 'bg-zinc-800 text-white ring-zinc-800',
};

function StatusBadge({ status, label }) {
    return (
        <span className={`inline-flex rounded-full px-2 py-1 text-xs font-medium ring-1 ring-inset ${statusStyles[status] ?? statusStyles.DRAFT}`}>
            {label ?? status}
        </span>
    );
}

export default function Index({ permintaanDana, filters, statuses }) {
    const permissions = usePage().props.auth.user.permissions ?? [];
    const columns = [
        { key: 'no_pd', label: 'No. PD', sortable: true },
        { key: 'tujuan', label: 'Tujuan', sortable: true },
        { key: 'nominal', label: 'Nominal', sortable: true, render: (row) => formatRupiah(row.nominal) },
        { key: 'status', label: 'Status', sortable: true, render: (row) => <StatusBadge status={row.status} label={row.status_label} /> },
        { key: 'created_by', label: 'Dibuat oleh' },
        { key: 'plan_pembayaran', label: 'Plan Pembayaran', sortable: true },
        {
            key: 'actions',
            label: 'Aksi',
            render: (row) => (
                <Button asChild size="icon" variant="outline" title="Lihat detail">
                    <Link href={route('permintaan-dana.show', row.id)}><Eye className="h-4 w-4" /></Link>
                </Button>
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
