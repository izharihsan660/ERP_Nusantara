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

export default function Index({ users, filters }) {
    const permissions = usePage().props.auth.user.permissions ?? [];
    const [target, setTarget] = useState(null);
    const columns = [
        { key: 'name', label: 'Nama' },
        { key: 'email', label: 'Email' },
        { key: 'roles', label: 'Jabatan', render: (row) => row.roles?.map((role) => role.name).join(', ') },
        { key: 'is_active', label: 'Status', render: (row) => <StatusBadge value={row.is_active} /> },
        {
            key: 'actions',
            label: 'Aksi',
            render: (row) => (
                <div className="flex gap-2">
                    {permissions.includes('ubah_user') && <Button asChild size="icon" variant="outline"><Link href={route('users.edit', row.id)}><Pencil className="h-4 w-4" /></Link></Button>}
                    {permissions.includes('hapus_user') && row.is_active && <Button size="icon" variant="destructive" onClick={() => setTarget(row)}><Power className="h-4 w-4" /></Button>}
                </div>
            ),
        },
    ];
    return (
        <AppLayout title="User">
            <Head title="User" />
            <PageHeader title="User Management" description="Kelola akun dan assign jabatan." actions={permissions.includes('tambah_user') && <Button asChild><Link href={route('users.create')}><Plus className="h-4 w-4" />Tambah</Link></Button>} />
            <DataTable data={users} columns={columns} filters={filters} routeName="users.index" filterSlot={(
                <Select value={filters.status ?? 'all'} onChange={(e) => router.get(route('users.index'), { ...filters, status: e.target.value, page: 1 }, { preserveState: true, replace: true })} className="w-36">
                    <option value="all">Semua Status</option>
                    <option value="active">Aktif</option>
                    <option value="inactive">Nonaktif</option>
                </Select>
            )} />
            <ConfirmDialog show={!!target} title="Nonaktifkan user" description={`${target?.name ?? ''} akan dinonaktifkan.`} confirmText="Nonaktifkan" onCancel={() => setTarget(null)} onConfirm={() => router.delete(route('users.destroy', target.id), { onFinish: () => setTarget(null) })} />
        </AppLayout>
    );
}
