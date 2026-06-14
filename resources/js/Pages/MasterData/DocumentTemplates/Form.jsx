import FormField from '@/Components/FormField';
import { Checkbox } from '@/Components/ui/checkbox';
import { Label } from '@/Components/ui/label';
import FormShell from '@/Pages/MasterData/Shared/FormShell';
import { useForm } from '@inertiajs/react';

export default function Form({ template, documentTypes }) {
    const isEdit = Boolean(template);
    const { data, setData, post, put, processing, errors } = useForm({
        nama_template: template?.nama_template ?? '',
        kode_template: template?.kode_template ?? '',
        tipe_dokumen: template?.tipe_dokumen ?? 'QUOTATION',
        blade_file: template?.blade_file ?? '',
        is_default: template?.is_default ?? false,
        keterangan: template?.keterangan ?? '',
    });
    const submit = (event) => {
        event.preventDefault();
        isEdit ? put(route('document-templates.update', template.id)) : post(route('document-templates.store'));
    };
    return (
        <FormShell title={isEdit ? 'Edit Template Dokumen' : 'Tambah Template Dokumen'} description="Daftarkan nama template dan file blade yang akan dipakai." backRoute="document-templates.index" processing={processing} onSubmit={submit}>
            <FormField label="Kode Template" name="kode_template" value={data.kode_template} onChange={(e) => setData('kode_template', e.target.value)} error={errors.kode_template} />
            <FormField label="Nama Template" name="nama_template" value={data.nama_template} onChange={(e) => setData('nama_template', e.target.value)} error={errors.nama_template} />
            <FormField label="Tipe Dokumen" name="tipe_dokumen" type="select" value={data.tipe_dokumen} options={documentTypes} onChange={(e) => setData('tipe_dokumen', e.target.value)} error={errors.tipe_dokumen} />
            <FormField label="Blade File" name="blade_file" value={data.blade_file} onChange={(e) => setData('blade_file', e.target.value)} error={errors.blade_file} />
            <FormField label="Keterangan" name="keterangan" type="textarea" value={data.keterangan} onChange={(e) => setData('keterangan', e.target.value)} error={errors.keterangan} />
            <div className="flex items-center gap-2">
                <Checkbox checked={data.is_default} onChange={(e) => setData('is_default', e.target.checked)} />
                <Label>Default untuk tipe dokumen</Label>
            </div>
        </FormShell>
    );
}
