import FormField from '@/Components/FormField';
import { Checkbox } from '@/Components/ui/checkbox';
import { Label } from '@/Components/ui/label';
import FormShell from '@/Pages/MasterData/Shared/FormShell';
import { useForm } from '@inertiajs/react';

export default function Form({ customer, options }) {
    const isEdit = Boolean(customer);
    const { data, setData, post, put, processing, errors } = useForm({
        kode_customer: customer?.kode_customer ?? '',
        nama_customer: customer?.nama_customer ?? '',
        alamat: customer?.alamat ?? '',
        kota: customer?.kota ?? '',
        npwp: customer?.npwp ?? '',
        pic_name: customer?.pic_name ?? '',
        pic_email: customer?.pic_email ?? '',
        pic_phone: customer?.pic_phone ?? '',
        template_quotation_id: customer?.template_quotation_id ?? '',
        template_spb_id: customer?.template_spb_id ?? '',
        is_active: customer?.is_active ?? true,
    });

    const submit = (event) => {
        event.preventDefault();
        isEdit ? put(route('customers.update', customer.id)) : post(route('customers.store'));
    };

    return (
        <FormShell title={isEdit ? 'Edit Customer' : 'Tambah Customer'} description="Lengkapi identitas pelanggan dan PIC." backRoute="customers.index" processing={processing} onSubmit={submit}>
            <FormField label="Kode Customer" name="kode_customer" value={data.kode_customer} onChange={(e) => setData('kode_customer', e.target.value)} error={errors.kode_customer} />
            <FormField label="Nama Customer" name="nama_customer" value={data.nama_customer} onChange={(e) => setData('nama_customer', e.target.value)} error={errors.nama_customer} />
            <FormField label="Kota" name="kota" value={data.kota} onChange={(e) => setData('kota', e.target.value)} error={errors.kota} />
            <FormField label="NPWP" name="npwp" value={data.npwp} onChange={(e) => setData('npwp', e.target.value)} error={errors.npwp} />
            <FormField label="PIC Name" name="pic_name" value={data.pic_name} onChange={(e) => setData('pic_name', e.target.value)} error={errors.pic_name} />
            <FormField label="PIC Email" name="pic_email" type="email" value={data.pic_email} onChange={(e) => setData('pic_email', e.target.value)} error={errors.pic_email} />
            <FormField label="PIC Phone" name="pic_phone" value={data.pic_phone} onChange={(e) => setData('pic_phone', e.target.value)} error={errors.pic_phone} />
            <FormField label="Template Quotation" name="template_quotation_id" type="select" value={data.template_quotation_id} options={options.quotationTemplates} onChange={(e) => setData('template_quotation_id', e.target.value)} error={errors.template_quotation_id} />
            <FormField label="Template SPB" name="template_spb_id" type="select" value={data.template_spb_id} options={options.spbTemplates} onChange={(e) => setData('template_spb_id', e.target.value)} error={errors.template_spb_id} />
            <FormField label="Alamat" name="alamat" type="textarea" value={data.alamat} onChange={(e) => setData('alamat', e.target.value)} error={errors.alamat} />
            <div className="flex items-center gap-2">
                <Checkbox checked={data.is_active} onChange={(e) => setData('is_active', e.target.checked)} />
                <Label>Aktif</Label>
            </div>
        </FormShell>
    );
}
