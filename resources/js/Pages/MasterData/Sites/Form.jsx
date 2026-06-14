import FormField from '@/Components/FormField';
import FormShell from '@/Pages/MasterData/Shared/FormShell';
import { useForm } from '@inertiajs/react';

export default function Form({ site, customers }) {
    const isEdit = Boolean(site);
    const { data, setData, post, put, processing, errors } = useForm({
        nama_site: site?.nama_site ?? '',
        alamat: site?.alamat ?? '',
        customer_id: site?.customer_id ?? '',
        keterangan: site?.keterangan ?? '',
    });
    const submit = (event) => {
        event.preventDefault();
        isEdit ? put(route('sites.update', site.id)) : post(route('sites.store'));
    };
    const customerOptions = customers.map((customer) => ({ id: customer.id, label: `${customer.kode_customer} - ${customer.nama_customer}` }));
    return (
        <FormShell title={isEdit ? 'Edit Site' : 'Tambah Site'} description="Hubungkan site dengan customer pemilik pekerjaan." backRoute="sites.index" processing={processing} onSubmit={submit}>
            <FormField label="Nama Site" name="nama_site" value={data.nama_site} onChange={(e) => setData('nama_site', e.target.value)} error={errors.nama_site} />
            <FormField label="Customer" name="customer_id" type="select" value={data.customer_id} options={customerOptions} onChange={(e) => setData('customer_id', e.target.value)} error={errors.customer_id} />
            <FormField label="Alamat" name="alamat" type="textarea" value={data.alamat} onChange={(e) => setData('alamat', e.target.value)} error={errors.alamat} />
            <FormField label="Keterangan" name="keterangan" type="textarea" value={data.keterangan} onChange={(e) => setData('keterangan', e.target.value)} error={errors.keterangan} />
        </FormShell>
    );
}
