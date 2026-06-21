import InputError from '@/Components/InputError';
import InputLabel from '@/Components/Form/InputLabel';
import KatalogAutocomplete from '@/Components/Form/KatalogAutocomplete';
import PageHeader from '@/Components/PageHeader';
import { LoadingButtonContent } from '@/Components/UiPolish';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Select } from '@/Components/ui/select';
import { Textarea } from '@/Components/ui/textarea';
import AppLayout from '@/Layouts/AppLayout';
import { formatRupiah, formatRupiahInput, parseRupiah } from '@/utils/currency';
import { Head, Link, useForm } from '@inertiajs/react';
import { Plus, Save, Send, Trash2 } from 'lucide-react';

function today() {
    return new Date().toISOString().slice(0, 10);
}

const emptyItem = { katalog_id: '', part_no: '', deskripsi: '', satuan: '', qty: 1, harga_satuan: 0, hpp_satuan: 0 };

export default function Create({ customers, templates }) {
    const { data, setData, post, transform, processing, errors } = useForm({
        customer_id: '',
        template_id: '',
        tgl_quotation: today(),
        catatan: '',
        items: [{ ...emptyItem }],
        submit: false,
    });

    const selectedCustomer = customers.find((customer) => String(customer.id) === String(data.customer_id));
    const selectedTemplate = templates.find((template) => String(template.id) === String(data.template_id));
    const setCustomer = (customerId) => {
        const customer = customers.find((item) => String(item.id) === String(customerId));
        setData((values) => ({
            ...values,
            customer_id: customerId,
            template_id: customer?.template_id ?? values.template_id,
        }));
    };

    const updateItem = (index, field, value) => {
        const items = [...data.items];
        items[index] = { ...items[index], [field]: value };
        setData('items', items);
    };

    const setKatalog = (index, selected) => {
        const items = [...data.items];
        items[index] = selected
            ? {
                ...items[index],
                katalog_id: selected.id,
                part_no: selected.part_no,
                deskripsi: selected.nama_barang,
                satuan: selected.satuan,
                harga_satuan: selected.harga_jual_default,
                hpp_satuan: selected.hpp,
            }
            : { ...emptyItem };
        setData('items', items);
    };

    const addItem = () => setData('items', [...data.items, { ...emptyItem }]);
    const removeItem = (index) => setData('items', data.items.filter((_, itemIndex) => itemIndex !== index));

    const totals = data.items.reduce((carry, item) => {
        const qty = Number(item.qty ?? 0);
        const harga = Number(item.harga_satuan ?? 0);
        const hpp = Number(item.hpp_satuan ?? 0);

        return {
            total: carry.total + qty * harga,
            totalHpp: carry.totalHpp + qty * hpp,
            profit: carry.profit + qty * (harga - hpp),
        };
    }, { total: 0, totalHpp: 0, profit: 0 });

    const submit = (event, submitToManager = false) => {
        event.preventDefault();
        transform((payload) => ({
            ...payload,
            submit: submitToManager,
            items: payload.items.map((item) => ({
                ...item,
                harga_satuan: parseRupiah(item.harga_satuan),
            })),
        }));
        post(route('quotations.store'));
    };

    return (
        <AppLayout title="Buat Quotation">
            <Head title="Buat Quotation" />
            <PageHeader
                title="Buat Quotation"
                description="Susun draft penawaran dari master katalog."
                actions={<Button asChild variant="outline"><Link href={route('quotations.index')}>Kembali</Link></Button>}
            />

            <form onSubmit={(event) => submit(event, false)} className="space-y-6">
                <section className="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] p-6 shadow-sm">
                    <h2 className="mb-4 text-base font-semibold text-[hsl(var(--foreground))]">Info Quotation</h2>
                    <div className="grid gap-4 lg:grid-cols-3">
                        <div>
                            <InputLabel label="Customer" required />
                            <Select className="mt-1" value={data.customer_id} onChange={(e) => setCustomer(e.target.value)}>
                                <option value="">Pilih customer...</option>
                                {customers.map((customer) => <option key={customer.id} value={customer.id}>{customer.label}</option>)}
                            </Select>
                            <InputError message={errors.customer_id} className="mt-2" />
                        </div>
                        <div>
                            <InputLabel label="Tanggal Quotation" required />
                            <Input className="mt-1" type="date" value={data.tgl_quotation} onChange={(e) => setData('tgl_quotation', e.target.value)} />
                            <InputError message={errors.tgl_quotation} className="mt-2" />
                        </div>
                        <div>
                            <InputLabel label="Template" required />
                            <div className="mt-1 rounded-md border border-[hsl(var(--border))] bg-[hsl(var(--muted))]/40 px-3 py-2 text-sm font-medium text-[hsl(var(--foreground))]">
                                {selectedTemplate ? selectedTemplate.label : 'Template otomatis dari customer'}
                            </div>
                            <p className="mt-2 text-xs text-[hsl(var(--muted-foreground))]">
                                {selectedCustomer?.template ? `Default customer: ${selectedCustomer.template.nama_template}` : 'Pilih customer untuk mengisi template default.'}
                            </p>
                            <InputError message={errors.template_id} className="mt-2" />
                        </div>
                        <div className="lg:col-span-3">
                            <InputLabel label="Catatan" optional />
                            <Textarea className="mt-1" value={data.catatan} onChange={(e) => setData('catatan', e.target.value)} />
                            <InputError message={errors.catatan} className="mt-2" />
                        </div>
                    </div>
                    {selectedTemplate && (
                        <div className="mt-4 rounded-md bg-[hsl(var(--muted))]/60 px-3 py-2 text-sm text-[hsl(var(--muted-foreground))]">
                            Template aktif: {selectedTemplate.label} ({selectedTemplate.blade_file})
                        </div>
                    )}
                </section>

                <section className="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] p-6 shadow-sm">
                    <div className="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h2 className="text-base font-semibold text-[hsl(var(--foreground))]">Item Barang <span className="text-red-600">*</span></h2>
                            <InputError message={errors.items} className="mt-1" />
                        </div>
                        <Button type="button" variant="secondary" onClick={addItem}><Plus className="h-4 w-4" />Tambah Item</Button>
                    </div>
                    <div className="overflow-x-auto rounded-lg border border-[hsl(var(--border))]">
                        <table className="min-w-full table-fixed divide-y divide-slate-200 text-sm dark:divide-slate-800">
                            <thead className="bg-slate-50 dark:bg-slate-900">
                                <tr>
                                    <th className="px-3 py-3 text-left"><InputLabel label="Katalog" required className="text-xs" /></th>
                                    <th className="px-3 py-3 text-left">Part No</th>
                                    <th className="px-3 py-3 text-left">Deskripsi</th>
                                    <th className="px-3 py-3 text-left"><InputLabel label="Qty" required className="text-xs" /></th>
                                    <th className="px-3 py-3 text-left">Satuan</th>
                                    <th className="px-3 py-3 text-left"><InputLabel label="Harga Jual" required className="text-xs" /></th>
                                    <th className="px-3 py-3 text-left">HPP</th>
                                    <th className="px-3 py-3 text-right">Total</th>
                                    <th className="px-3 py-3 text-right">Profit</th>
                                    <th className="px-3 py-3" />
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-100 dark:divide-slate-900">
                                {data.items.map((item, index) => {
                                    const total = Number(item.qty ?? 0) * Number(item.harga_satuan ?? 0);
                                    const profit = total - Number(item.qty ?? 0) * Number(item.hpp_satuan ?? 0);

                                    return (
                                        <tr key={index}>
                                            <td className="min-w-64 px-3 py-3">
                                                <KatalogAutocomplete value={item} onSelect={(selected) => setKatalog(index, selected)} />
                                                <InputError message={errors[`items.${index}.katalog_id`]} className="mt-2" />
                                            </td>
                                            <td className="px-3 py-3"><Input value={item.part_no} readOnly /></td>
                                            <td className="min-w-56 px-3 py-3"><Input value={item.deskripsi} readOnly /></td>
                                            <td className="w-24 px-3 py-3">
                                                <Input type="number" min="1" value={item.qty} onChange={(e) => updateItem(index, 'qty', e.target.value)} />
                                                <InputError message={errors[`items.${index}.qty`]} className="mt-2" />
                                            </td>
                                            <td className="w-28 px-3 py-3"><Input value={item.satuan ?? ''} readOnly /></td>
                                            <td className="w-36 px-3 py-3">
                                                <Input inputMode="numeric" value={formatRupiahInput(item.harga_satuan)} onChange={(e) => updateItem(index, 'harga_satuan', parseRupiah(e.target.value))} />
                                                <InputError message={errors[`items.${index}.harga_satuan`]} className="mt-2" />
                                            </td>
                                            <td className="w-36 px-3 py-3"><Input value={formatRupiahInput(item.hpp_satuan)} readOnly /></td>
                                            <td className="whitespace-nowrap px-3 py-3 text-right">{formatRupiah(total)}</td>
                                            <td className="whitespace-nowrap px-3 py-3 text-right">{formatRupiah(profit)}</td>
                                            <td className="px-3 py-3">
                                                <Button type="button" size="icon" variant="ghost" onClick={() => removeItem(index)} disabled={data.items.length === 1}>
                                                    <Trash2 className="h-4 w-4" />
                                                </Button>
                                            </td>
                                        </tr>
                                    );
                                })}
                            </tbody>
                        </table>
                    </div>
                    <div className="mt-4 grid gap-3 text-sm sm:grid-cols-3">
                        <div className="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--muted))]/35 p-4"><span className="text-[hsl(var(--muted-foreground))]">Total</span><strong className="mt-1 block text-[hsl(var(--foreground))]">{formatRupiah(totals.total)}</strong></div>
                        <div className="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--muted))]/35 p-4"><span className="text-[hsl(var(--muted-foreground))]">HPP</span><strong className="mt-1 block text-[hsl(var(--foreground))]">{formatRupiah(totals.totalHpp)}</strong></div>
                        <div className="rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-emerald-700 dark:border-emerald-900 dark:bg-emerald-950 dark:text-emerald-300"><span>Profit</span><strong className="mt-1 block">{formatRupiah(totals.profit)}</strong></div>
                    </div>
                </section>

                <div className="sticky bottom-4 z-10 flex flex-col justify-end gap-2 rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))]/95 p-3 shadow-lg backdrop-blur sm:flex-row">
                    <Button type="submit" variant="secondary" disabled={processing}><Save className="h-4 w-4" />Simpan Draft</Button>
                    <Button type="button" disabled={processing} onClick={(event) => submit(event, true)}><Send className="h-4 w-4" /><LoadingButtonContent loading={processing}>Submit ke Manager</LoadingButtonContent></Button>
                </div>
            </form>
        </AppLayout>
    );
}
