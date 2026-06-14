import ConfirmDialog from '@/Components/ConfirmDialog';
import DataTable from '@/Components/Data/DataTable';
import PageHeader from '@/Components/PageHeader';
import StatusBadge from '@/Components/StatusBadge';
import { Button } from '@/Components/ui/button';
import { Select } from '@/Components/ui/select';
import AppLayout from '@/Layouts/AppLayout';
import { Head, Link, router, usePage } from '@inertiajs/react';
import { Pencil, Plus, Power } from 'lucide-react';
import { useState } from 'react';

export default function Index({ customers, filters }) {
    const permissions = usePage().props.auth.user.permissions ?? [];
    const [target, setTarget] = useState(null);

    const columns = [
        { key: 'kode_customer', label: 'Kode', sortable: true },
        { key: 'nama_customer', label: 'Nama Customer', sortable: true },
        { key: 'kota', label: 'Kota', sortable: true },
        { key: 'pic_name', label: 'PIC' },
        { key: 'is_active', label: 'Status', render: (row) => <StatusBadge value={row.is_active} /> },
        {
            key: 'actions',
            label: 'Aksi',
            render: (row) => (
                <div className="flex gap-2">
                    {permissions.includes('Customer ubah') && (
                        <Button asChild size="icon" variant="outline"><Link href={route('customers.edit', row.id)}><Pencil className="h-4 w-4" /></Link></Button>
                    )}
                    {permissions.includes('Customer hapus') && row.is_active && (
                        <Button size="icon" variant="destructive" onClick={() => setTarget(row)}><Power className="h-4 w-4" /></Button>
                    )}
                </div>
            ),
        },
    ];

    return (
        <AppLayout title="Customer">
            <Head title="Customer" />
            <PageHeader
                title="Customer"
                description="Master data pelanggan dan template dokumen default."
                actions={permissions.includes('Customer tambah') && <Button asChild><Link href={route('customers.create')}><Plus className="h-4 w-4" />Tambah</Link></Button>}
            />
            <DataTable
                data={customers}
                columns={columns}
                filters={filters}
                routeName="customers.index"
                filterSlot={(
                    <Select
                        value={filters.status ?? 'all'}
                        onChange={(event) => router.get(route('customers.index'), { ...filters, status: event.target.value, page: 1 }, { preserveState: true, replace: true })}
                        className="w-36"
                    >
                        <option value="all">Semua Status</option>
                        <option value="active">Aktif</option>
                        <option value="inactive">Nonaktif</option>
                    </Select>
                )}
            />
            <ConfirmDialog
                show={!!target}
                title="Nonaktifkan customer"
                description={`Customer ${target?.nama_customer ?? ''} akan dinonaktifkan, bukan dihapus permanen.`}
                confirmText="Nonaktifkan"
                onCancel={() => setTarget(null)}
                onConfirm={() => router.delete(route('customers.destroy', target.id), { onFinish: () => setTarget(null) })}
            />
        </AppLayout>
    );
}
