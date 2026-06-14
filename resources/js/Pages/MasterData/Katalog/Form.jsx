import FormField from '@/Components/FormField';
import { Checkbox } from '@/Components/ui/checkbox';
import { Label } from '@/Components/ui/label';
import FormShell from '@/Pages/MasterData/Shared/FormShell';
import { useForm } from '@inertiajs/react';

export default function Form({ item }) {
    const isEdit = Boolean(item);
    const { data, setData, post, put, processing, errors } = useForm({
        part_no: item?.part_no ?? '',
        nama_barang: item?.nama_barang ?? '',
        spesifikasi: item?.spesifikasi ?? '',
        satuan: item?.satuan ?? '',
        hpp: item?.hpp ?? 0,
        harga_jual_default: item?.harga_jual_default ?? 0,
        kategori: item?.kategori ?? '',
        is_active: item?.is_active ?? true,
    });

    const submit = (event) => {
        event.preventDefault();
        isEdit ? put(route('katalog.update', item.id)) : post(route('katalog.store'));
    };

    return (
        <FormShell title={isEdit ? 'Edit Barang' : 'Tambah Barang'} description="Kelola part number, spesifikasi, dan harga default." backRoute="katalog.index" processing={processing} onSubmit={submit}>
            <FormField label="Part No" name="part_no" value={data.part_no} onChange={(e) => setData('part_no', e.target.value)} error={errors.part_no} />
            <FormField label="Nama Barang" name="nama_barang" value={data.nama_barang} onChange={(e) => setData('nama_barang', e.target.value)} error={errors.nama_barang} />
            <FormField label="Satuan" name="satuan" value={data.satuan} onChange={(e) => setData('satuan', e.target.value)} error={errors.satuan} />
            <FormField label="Kategori" name="kategori" value={data.kategori} onChange={(e) => setData('kategori', e.target.value)} error={errors.kategori} />
            <FormField label="HPP" name="hpp" type="number" step="0.01" value={data.hpp} onChange={(e) => setData('hpp', e.target.value)} error={errors.hpp} />
            <FormField label="Harga Jual Default" name="harga_jual_default" type="number" step="0.01" value={data.harga_jual_default} onChange={(e) => setData('harga_jual_default', e.target.value)} error={errors.harga_jual_default} />
            <FormField label="Spesifikasi" name="spesifikasi" type="textarea" value={data.spesifikasi} onChange={(e) => setData('spesifikasi', e.target.value)} error={errors.spesifikasi} />
            <div className="flex items-center gap-2">
                <Checkbox checked={data.is_active} onChange={(e) => setData('is_active', e.target.checked)} />
                <Label>Aktif</Label>
            </div>
        </FormShell>
    );
}
