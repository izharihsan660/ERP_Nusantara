import ConfirmDialog from '@/Components/ConfirmDialog';
import DataTable from '@/Components/Data/DataTable';
import PageHeader from '@/Components/PageHeader';
import StatusBadge from '@/Components/StatusBadge';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Select } from '@/Components/ui/select';
import AppLayout from '@/Layouts/AppLayout';
import { formatRupiah } from '@/utils/currency';
import { Head, Link, router, useForm, usePage } from '@inertiajs/react';
import { FileUp, Pencil, Plus, Power } from 'lucide-react';
import { useState } from 'react';

export default function Index({ items, filters, categories }) {
    const permissions = usePage().props.auth.user.permissions ?? [];
    const [target, setTarget] = useState(null);
    const importForm = useForm({ file: null });

    const columns = [
        { key: 'part_no', label: 'Part No', sortable: true },
        { key: 'nama_barang', label: 'Nama Barang', sortable: true },
        { key: 'kategori', label: 'Kategori', sortable: true },
        { key: 'hpp', label: 'HPP', sortable: true, render: (row) => formatRupiah(row.hpp) },
        { key: 'harga_jual_default', label: 'Harga Jual', sortable: true, render: (row) => formatRupiah(row.harga_jual_default) },
        { key: 'is_active', label: 'Status', render: (row) => <StatusBadge value={row.is_active} /> },
        {
            key: 'actions',
            label: 'Aksi',
            render: (row) => (
                <div className="flex gap-2">
                    {permissions.includes('Katalog ubah') && <Button asChild size="icon" variant="outline"><Link href={route('katalog.edit', row.id)}><Pencil className="h-4 w-4" /></Link></Button>}
                    {permissions.includes('Katalog hapus') && row.is_active && <Button size="icon" variant="destructive" onClick={() => setTarget(row)}><Power className="h-4 w-4" /></Button>}
                </div>
            ),
        },
    ];

    const submitImport = (event) => {
        event.preventDefault();
        importForm.post(route('katalog.import'), { forceFormData: true, onSuccess: () => importForm.reset() });
    };

    return (
        <AppLayout title="Katalog">
            <Head title="Katalog" />
            <PageHeader
                title="Katalog"
                description="Master barang dari RMA dan vendor lain."
                actions={(
                    <>
                        {permissions.includes('Katalog import') && (
                            <form onSubmit={submitImport} className="flex gap-2">
                                <Input type="file" accept=".xlsx,.xls,.csv" onChange={(e) => importForm.setData('file', e.target.files[0])} className="max-w-56" />
                                <Button type="submit" variant="secondary" disabled={importForm.processing}><FileUp className="h-4 w-4" />Import</Button>
                            </form>
                        )}
                        {permissions.includes('Katalog tambah') && <Button asChild><Link href={route('katalog.create')}><Plus className="h-4 w-4" />Tambah</Link></Button>}
                    </>
                )}
            />
            <DataTable
                data={items}
                columns={columns}
                filters={filters}
                routeName="katalog.index"
                filterSlot={(
                    <>
                        <Select value={filters.kategori ?? ''} onChange={(e) => router.get(route('katalog.index'), { ...filters, kategori: e.target.value, page: 1 }, { preserveState: true, replace: true })} className="w-44">
                            <option value="">Semua Kategori</option>
                            {categories.map((category) => <option key={category} value={category}>{category}</option>)}
                        </Select>
                        <Select value={filters.status ?? 'all'} onChange={(e) => router.get(route('katalog.index'), { ...filters, status: e.target.value, page: 1 }, { preserveState: true, replace: true })} className="w-36">
                            <option value="all">Semua Status</option>
                            <option value="active">Aktif</option>
                            <option value="inactive">Nonaktif</option>
                        </Select>
                    </>
                )}
            />
            <ConfirmDialog show={!!target} title="Nonaktifkan barang" description={`${target?.nama_barang ?? ''} akan dinonaktifkan.`} confirmText="Nonaktifkan" onCancel={() => setTarget(null)} onConfirm={() => router.delete(route('katalog.destroy', target.id), { onFinish: () => setTarget(null) })} />
        </AppLayout>
    );
}
