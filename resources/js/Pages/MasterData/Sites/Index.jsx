import ConfirmDialog from '@/Components/ConfirmDialog';
import DataTable from '@/Components/Data/DataTable';
import PageHeader from '@/Components/PageHeader';
import { Button } from '@/Components/ui/button';
import { Select } from '@/Components/ui/select';
import AppLayout from '@/Layouts/AppLayout';
import { Head, Link, router, usePage } from '@inertiajs/react';
import { Pencil, Plus, Trash2 } from 'lucide-react';
import { useState } from 'react';

export default function Index({ sites, filters, customers }) {
    const permissions = usePage().props.auth.user.permissions ?? [];
    const [target, setTarget] = useState(null);
    const columns = [
        { key: 'nama_site', label: 'Nama Site', sortable: true },
        { key: 'customer', label: 'Customer', render: (row) => row.customer?.nama_customer },
        { key: 'alamat', label: 'Alamat' },
        {
            key: 'actions',
            label: 'Aksi',
            render: (row) => (
                <div className="flex gap-2">
                    {permissions.includes('Site ubah') && <Button asChild size="icon" variant="outline"><Link href={route('sites.edit', row.id)}><Pencil className="h-4 w-4" /></Link></Button>}
                    {permissions.includes('Site hapus') && <Button size="icon" variant="destructive" onClick={() => setTarget(row)}><Trash2 className="h-4 w-4" /></Button>}
                </div>
            ),
        },
    ];
    return (
        <AppLayout title="Site">
            <Head title="Site" />
            <PageHeader title="Site" description="Lokasi pekerjaan per customer." actions={permissions.includes('Site tambah') && <Button asChild><Link href={route('sites.create')}><Plus className="h-4 w-4" />Tambah</Link></Button>} />
            <DataTable data={sites} columns={columns} filters={filters} routeName="sites.index" filterSlot={(
                <Select value={filters.customer_id ?? ''} onChange={(e) => router.get(route('sites.index'), { ...filters, customer_id: e.target.value, page: 1 }, { preserveState: true, replace: true })} className="w-56">
                    <option value="">Semua Customer</option>
                    {customers.map((customer) => <option key={customer.id} value={customer.id}>{customer.nama_customer}</option>)}
                </Select>
            )} />
            <ConfirmDialog show={!!target} title="Hapus site" description={`${target?.nama_site ?? ''} akan dihapus secara soft delete.`} confirmText="Hapus" onCancel={() => setTarget(null)} onConfirm={() => router.delete(route('sites.destroy', target.id), { onFinish: () => setTarget(null) })} />
        </AppLayout>
    );
}
