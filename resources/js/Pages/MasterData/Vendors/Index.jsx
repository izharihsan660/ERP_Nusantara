import ConfirmDialog from '@/Components/ConfirmDialog';
import DataTable from '@/Components/Data/DataTable';
import PageHeader from '@/Components/PageHeader';
import { Button } from '@/Components/ui/button';
import { Select } from '@/Components/ui/select';
import AppLayout from '@/Layouts/AppLayout';
import { Head, Link, router, usePage } from '@inertiajs/react';
import { Pencil, Plus, Trash2 } from 'lucide-react';
import { useState } from 'react';

export default function Index({ vendors, filters, vendorTypes }) {
    const permissions = usePage().props.auth.user.permissions ?? [];
    const [target, setTarget] = useState(null);
    const label = (value) => vendorTypes.find((type) => type.value === value)?.label ?? value;
    const columns = [
        { key: 'nama_vendor', label: 'Nama Vendor', sortable: true },
        { key: 'tipe_vendor', label: 'Tipe', sortable: true, render: (row) => label(row.tipe_vendor) },
        { key: 'pic_name', label: 'PIC' },
        { key: 'pic_email', label: 'Email' },
        {
            key: 'actions',
            label: 'Aksi',
            render: (row) => (
                <div className="flex gap-2">
                    {permissions.includes('ubah_vendor') && <Button asChild size="icon" variant="outline"><Link href={route('vendors.edit', row.id)}><Pencil className="h-4 w-4" /></Link></Button>}
                    {permissions.includes('hapus_vendor') && <Button size="icon" variant="destructive" onClick={() => setTarget(row)}><Trash2 className="h-4 w-4" /></Button>}
                </div>
            ),
        },
    ];

    return (
        <AppLayout title="Vendor">
            <Head title="Vendor" />
            <PageHeader title="Vendor" description="Master data pemasok dan RMA." actions={permissions.includes('tambah_vendor') && <Button asChild><Link href={route('vendors.create')}><Plus className="h-4 w-4" />Tambah</Link></Button>} />
            <DataTable data={vendors} columns={columns} filters={filters} routeName="vendors.index" filterSlot={(
                <Select value={filters.tipe_vendor ?? ''} onChange={(e) => router.get(route('vendors.index'), { ...filters, tipe_vendor: e.target.value, page: 1 }, { preserveState: true, replace: true })} className="w-44">
                    <option value="">Semua Tipe</option>
                    {vendorTypes.map((type) => <option key={type.value} value={type.value}>{type.label}</option>)}
                </Select>
            )} />
            <ConfirmDialog show={!!target} title="Hapus vendor" description={`${target?.nama_vendor ?? ''} akan dihapus secara soft delete.`} confirmText="Hapus" onCancel={() => setTarget(null)} onConfirm={() => router.delete(route('vendors.destroy', target.id), { onFinish: () => setTarget(null) })} />
        </AppLayout>
    );
}
