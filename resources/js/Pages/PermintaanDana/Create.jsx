import InputError from '@/Components/InputError';
import PageHeader from '@/Components/PageHeader';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Select } from '@/Components/ui/select';
import { Textarea } from '@/Components/ui/textarea';
import AppLayout from '@/Layouts/AppLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import { Save, Send } from 'lucide-react';

function today() {
    return new Date().toISOString().slice(0, 10);
}

function rupiah(value) {
    return `Rp ${Number(value ?? 0).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
}

export default function Create({ categories }) {
    const { data, setData, post, transform, processing, errors } = useForm({
        tgl_pd: today(),
        kategori: '',
        nominal: '',
        keterangan: '',
        referensi_dokumen: '',
        submit: false,
    });

    const submit = (event, submitToManager = false) => {
        event.preventDefault();
        transform((payload) => ({ ...payload, submit: submitToManager }));
        post(route('permintaan-dana.store'));
    };

    return (
        <AppLayout title="Buat Permintaan Dana">
            <Head title="Buat Permintaan Dana" />
            <PageHeader
                title="Buat Permintaan Dana"
                description="Buat draft permintaan pencairan dana internal."
                actions={<Button asChild variant="outline"><Link href={route('permintaan-dana.index')}>Kembali</Link></Button>}
            />

            <form onSubmit={(event) => submit(event, false)} className="space-y-6">
                <section className="rounded-lg border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                    <div className="grid gap-4 lg:grid-cols-2">
                        <div>
                            <Label>Tanggal PD</Label>
                            <Input className="mt-1" type="date" value={data.tgl_pd} onChange={(e) => setData('tgl_pd', e.target.value)} />
                            <InputError message={errors.tgl_pd} className="mt-2" />
                        </div>
                        <div>
                            <Label>Kategori</Label>
                            <Select className="mt-1" value={data.kategori} onChange={(e) => setData('kategori', e.target.value)}>
                                <option value="">Pilih kategori...</option>
                                {categories.map((category) => <option key={category.value} value={category.value}>{category.label}</option>)}
                            </Select>
                            <InputError message={errors.kategori} className="mt-2" />
                        </div>
                        <div>
                            <Label>Nominal</Label>
                            <Input className="mt-1" type="number" min="1" step="0.01" value={data.nominal} onChange={(e) => setData('nominal', e.target.value)} />
                            <div className="mt-1 text-xs text-slate-500">{rupiah(data.nominal)}</div>
                            <InputError message={errors.nominal} className="mt-2" />
                        </div>
                        <div>
                            <Label>Referensi Dokumen</Label>
                            <Input className="mt-1" value={data.referensi_dokumen} onChange={(e) => setData('referensi_dokumen', e.target.value)} placeholder="No. referensi terkait (opsional, misal: WIP 12210)" />
                            <InputError message={errors.referensi_dokumen} className="mt-2" />
                        </div>
                        <div className="lg:col-span-2">
                            <Label>Keterangan</Label>
                            <Textarea className="mt-1 min-h-32" value={data.keterangan} onChange={(e) => setData('keterangan', e.target.value)} />
                            <InputError message={errors.keterangan} className="mt-2" />
                        </div>
                    </div>
                </section>

                <div className="flex justify-end gap-2">
                    <Button type="submit" variant="secondary" disabled={processing}><Save className="h-4 w-4" />Simpan Draft</Button>
                    <Button type="button" disabled={processing} onClick={(event) => submit(event, true)}><Send className="h-4 w-4" />Submit ke Manager</Button>
                </div>
            </form>
        </AppLayout>
    );
}
