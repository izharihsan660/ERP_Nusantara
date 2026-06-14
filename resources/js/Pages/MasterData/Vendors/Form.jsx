import FormField from '@/Components/FormField';
import FormShell from '@/Pages/MasterData/Shared/FormShell';
import { useForm } from '@inertiajs/react';

export default function Form({ vendor, vendorTypes }) {
    const isEdit = Boolean(vendor);
    const { data, setData, post, put, processing, errors } = useForm({
        tipe_vendor: vendor?.tipe_vendor ?? 'RMA',
        nama_vendor: vendor?.nama_vendor ?? '',
        alamat: vendor?.alamat ?? '',
        pic_name: vendor?.pic_name ?? '',
        pic_email: vendor?.pic_email ?? '',
        rekening: vendor?.rekening ?? '',
        keterangan: vendor?.keterangan ?? '',
    });
    const submit = (event) => {
        event.preventDefault();
        isEdit ? put(route('vendors.update', vendor.id)) : post(route('vendors.store'));
    };
    return (
        <FormShell title={isEdit ? 'Edit Vendor' : 'Tambah Vendor'} description="Kelola profil vendor dan kontak PIC." backRoute="vendors.index" processing={processing} onSubmit={submit}>
            <FormField label="Tipe Vendor" name="tipe_vendor" type="select" value={data.tipe_vendor} options={vendorTypes} onChange={(e) => setData('tipe_vendor', e.target.value)} error={errors.tipe_vendor} />
            <FormField label="Nama Vendor" name="nama_vendor" value={data.nama_vendor} onChange={(e) => setData('nama_vendor', e.target.value)} error={errors.nama_vendor} />
            <FormField label="PIC Name" name="pic_name" value={data.pic_name} onChange={(e) => setData('pic_name', e.target.value)} error={errors.pic_name} />
            <FormField label="PIC Email" name="pic_email" type="email" value={data.pic_email} onChange={(e) => setData('pic_email', e.target.value)} error={errors.pic_email} />
            <FormField label="Rekening" name="rekening" value={data.rekening} onChange={(e) => setData('rekening', e.target.value)} error={errors.rekening} />
            <FormField label="Alamat" name="alamat" type="textarea" value={data.alamat} onChange={(e) => setData('alamat', e.target.value)} error={errors.alamat} />
            <FormField label="Keterangan" name="keterangan" type="textarea" value={data.keterangan} onChange={(e) => setData('keterangan', e.target.value)} error={errors.keterangan} />
        </FormShell>
    );
}
