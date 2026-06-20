import ConfirmDialog from '@/Components/ConfirmDialog';
import DataTable from '@/Components/Data/DataTable';
import PageHeader from '@/Components/PageHeader';
import { Button } from '@/Components/ui/button';
import AppLayout from '@/Layouts/AppLayout';
import { Head, Link, router, usePage } from '@inertiajs/react';
import { Pencil, Plus, Trash2 } from 'lucide-react';
import { useState } from 'react';

export default function Index({ roles, filters }) {
    const permissions = usePage().props.auth.user.permissions ?? [];
    const [target, setTarget] = useState(null);
    const columns = [
        { key: 'name', label: 'Nama Jabatan' },
        { key: 'users_count', label: 'Jumlah User' },
        { key: 'permissions', label: 'Permission', render: (row) => `${row.permissions?.length ?? 0} permission` },
        {
            key: 'actions',
            label: 'Aksi',
            render: (row) => (
                <div className="flex gap-2">
                    {permissions.includes('ubah_jabatan') && <Button asChild size="icon" variant="outline"><Link href={route('roles.edit', row.id)}><Pencil className="h-4 w-4" /></Link></Button>}
                    {permissions.includes('hapus_jabatan') && row.name !== 'Superadmin' && <Button size="icon" variant="destructive" onClick={() => setTarget(row)}><Trash2 className="h-4 w-4" /></Button>}
                </div>
            ),
        },
    ];
    return (
        <AppLayout title="Jabatan">
            <Head title="Jabatan" />
            <PageHeader title="Jabatan" description="Kelola role dinamis dan permission granular." actions={permissions.includes('tambah_jabatan') && <Button asChild><Link href={route('roles.create')}><Plus className="h-4 w-4" />Tambah</Link></Button>} />
            <DataTable data={roles} columns={columns} filters={filters} routeName="roles.index" />
            <ConfirmDialog show={!!target} title="Hapus jabatan" description={`${target?.name ?? ''} akan dihapus.`} confirmText="Hapus" onCancel={() => setTarget(null)} onConfirm={() => router.delete(route('roles.destroy', target.id), { onFinish: () => setTarget(null) })} />
        </AppLayout>
    );
}
