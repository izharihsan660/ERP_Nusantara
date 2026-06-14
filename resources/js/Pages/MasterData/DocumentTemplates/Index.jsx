import ConfirmDialog from '@/Components/ConfirmDialog';
import DataTable from '@/Components/Data/DataTable';
import PageHeader from '@/Components/PageHeader';
import StatusBadge from '@/Components/StatusBadge';
import { Button } from '@/Components/ui/button';
import { Select } from '@/Components/ui/select';
import AppLayout from '@/Layouts/AppLayout';
import { Head, Link, router, usePage } from '@inertiajs/react';
import { Pencil, Plus, Trash2 } from 'lucide-react';
import { useState } from 'react';

export default function Index({ templates, filters, documentTypes }) {
    const permissions = usePage().props.auth.user.permissions ?? [];
    const [target, setTarget] = useState(null);
    const label = (value) => documentTypes.find((type) => type.value === value)?.label ?? value;
    const columns = [
        { key: 'kode_template', label: 'Kode', sortable: true },
        { key: 'nama_template', label: 'Nama Template', sortable: true },
        { key: 'tipe_dokumen', label: 'Tipe', sortable: true, render: (row) => label(row.tipe_dokumen) },
        { key: 'blade_file', label: 'Blade File' },
        { key: 'is_default', label: 'Default', render: (row) => <StatusBadge value={row.is_default ? 'default' : 'inactive'}>{row.is_default ? 'Default' : 'Manual'}</StatusBadge> },
        {
            key: 'actions',
            label: 'Aksi',
            render: (row) => (
                <div className="flex gap-2">
                    {permissions.includes('Template Dokumen ubah') && <Button asChild size="icon" variant="outline"><Link href={route('document-templates.edit', row.id)}><Pencil className="h-4 w-4" /></Link></Button>}
                    {permissions.includes('Template Dokumen hapus') && <Button size="icon" variant="destructive" onClick={() => setTarget(row)}><Trash2 className="h-4 w-4" /></Button>}
                </div>
            ),
        },
    ];
    return (
        <AppLayout title="Template Dokumen">
            <Head title="Template Dokumen" />
            <PageHeader title="Template Dokumen" description="Template blade untuk output dokumen transaksi." actions={permissions.includes('Template Dokumen tambah') && <Button asChild><Link href={route('document-templates.create')}><Plus className="h-4 w-4" />Tambah</Link></Button>} />
            <DataTable data={templates} columns={columns} filters={filters} routeName="document-templates.index" filterSlot={(
                <Select value={filters.tipe_dokumen ?? ''} onChange={(e) => router.get(route('document-templates.index'), { ...filters, tipe_dokumen: e.target.value, page: 1 }, { preserveState: true, replace: true })} className="w-48">
                    <option value="">Semua Tipe</option>
                    {documentTypes.map((type) => <option key={type.value} value={type.value}>{type.label}</option>)}
                </Select>
            )} />
            <ConfirmDialog show={!!target} title="Hapus template" description={`${target?.nama_template ?? ''} akan dihapus secara soft delete.`} confirmText="Hapus" onCancel={() => setTarget(null)} onConfirm={() => router.delete(route('document-templates.destroy', target.id), { onFinish: () => setTarget(null) })} />
        </AppLayout>
    );
}
