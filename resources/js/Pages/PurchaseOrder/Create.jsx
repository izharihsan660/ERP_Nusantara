import InputError from '@/Components/InputError';
import InputLabel from '@/Components/Form/InputLabel';
import PageHeader from '@/Components/PageHeader';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Select } from '@/Components/ui/select';
import { Textarea } from '@/Components/ui/textarea';
import AppLayout from '@/Layouts/AppLayout';
import { formatRupiah, formatRupiahInput, parseRupiah } from '@/utils/currency';
import { Head, Link, useForm } from '@inertiajs/react';
import { Plus, Save, Send, Trash2 } from 'lucide-react';
import { useMemo, useState } from 'react';

function today() {
    return new Date().toISOString().slice(0, 10);
}

const emptyItem = { katalog_id: '', deskripsi: '', satuan: '', qty: 1, harga_satuan: 0 };

export default function Create({ customers, vendors, katalog }) {
    const [customerSearch, setCustomerSearch] = useState('');
    const [vendorSearch, setVendorSearch] = useState('');
    const [katalogSearch, setKatalogSearch] = useState('');
    const { data, setData, post, transform, processing, errors } = useForm({
        customer_id: '',
        vendor_id: '',
        tgl_po: today(),
        no_pr_customer: '',
        no_po_customer: '',
        catatan: '',
        items: [{ ...emptyItem }],
        submit: false,
    });

    const filteredCustomers = useMemo(() => customers.filter((customer) => customer.label.toLowerCase().includes(customerSearch.toLowerCase())), [customers, customerSearch]);
    const filteredVendors = useMemo(() => vendors.filter((vendor) => vendor.label.toLowerCase().includes(vendorSearch.toLowerCase())), [vendors, vendorSearch]);
    const filteredKatalog = useMemo(() => katalog.filter((item) => item.label.toLowerCase().includes(katalogSearch.toLowerCase())), [katalog, katalogSearch]);

    const updateItem = (index, field, value) => {
        const items = [...data.items];
        items[index] = { ...items[index], [field]: value };
        setData('items', items);
    };

    const setKatalog = (index, katalogId) => {
        const selected = katalog.find((item) => String(item.id) === String(katalogId));
        const items = [...data.items];
        items[index] = selected
            ? {
                ...items[index],
                katalog_id: selected.id,
                deskripsi: selected.deskripsi,
                satuan: selected.satuan,
                harga_satuan: selected.harga_satuan,
            }
            : { ...emptyItem };
        setData('items', items);
    };

    const addItem = () => setData('items', [...data.items, { ...emptyItem }]);
    const removeItem = (index) => setData('items', data.items.filter((_, itemIndex) => itemIndex !== index));
    const total = data.items.reduce((carry, item) => carry + Number(item.qty ?? 0) * Number(item.harga_satuan ?? 0), 0);

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
        post(route('purchase-orders.store'));
    };

    return (
        <AppLayout title="Buat Purchase Order">
            <Head title="Buat Purchase Order" />
            <PageHeader
                title="Buat Purchase Order"
                description="Susun draft purchase order untuk vendor eksternal."
                actions={<Button asChild variant="outline"><Link href={route('purchase-orders.index')}>Kembali</Link></Button>}
            />

            <form onSubmit={(event) => submit(event, false)} className="space-y-6">
                <section className="rounded-lg border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                    <div className="grid gap-4 lg:grid-cols-3">
                        <div>
                            <InputLabel label="Customer" required />
                            <Input className="mt-1" value={customerSearch} onChange={(e) => setCustomerSearch(e.target.value)} placeholder="Cari customer..." />
                            <Select className="mt-2" value={data.customer_id} onChange={(e) => setData('customer_id', e.target.value)}>
                                <option value="">Pilih customer...</option>
                                {filteredCustomers.map((customer) => <option key={customer.id} value={customer.id}>{customer.label}</option>)}
                            </Select>
                            <InputError message={errors.customer_id} className="mt-2" />
                        </div>
                        <div>
                            <InputLabel label="Vendor" required />
                            <Input className="mt-1" value={vendorSearch} onChange={(e) => setVendorSearch(e.target.value)} placeholder="Cari vendor..." />
                            <Select className="mt-2" value={data.vendor_id} onChange={(e) => setData('vendor_id', e.target.value)}>
                                <option value="">Pilih vendor...</option>
                                {filteredVendors.map((vendor) => <option key={vendor.id} value={vendor.id}>{vendor.label}</option>)}
                            </Select>
                            <InputError message={errors.vendor_id} className="mt-2" />
                        </div>
                        <div>
                            <InputLabel label="Tanggal PO" required />
                            <Input className="mt-1" type="date" value={data.tgl_po} onChange={(e) => setData('tgl_po', e.target.value)} />
                            <InputError message={errors.tgl_po} className="mt-2" />
                        </div>
                        <div>
                            <InputLabel label="No. PR Customer" optional />
                            <Input className="mt-1" value={data.no_pr_customer} onChange={(e) => setData('no_pr_customer', e.target.value)} />
                            <InputError message={errors.no_pr_customer} className="mt-2" />
                        </div>
                        <div>
                            <InputLabel label="No. PO Customer" optional />
                            <Input className="mt-1" value={data.no_po_customer} onChange={(e) => setData('no_po_customer', e.target.value)} />
                            <InputError message={errors.no_po_customer} className="mt-2" />
                        </div>
                        <div className="lg:col-span-2">
                            <InputLabel label="Catatan" optional />
                            <Textarea className="mt-1" value={data.catatan} onChange={(e) => setData('catatan', e.target.value)} />
                            <InputError message={errors.catatan} className="mt-2" />
                        </div>
                    </div>
                </section>

                <section className="rounded-lg border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-950">
                    <div className="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 p-4 dark:border-slate-800">
                        <div>
                            <h2 className="font-semibold">Item <span className="text-red-600">*</span></h2>
                            <InputError message={errors.items} className="mt-1" />
                        </div>
                        <div className="flex flex-wrap items-center gap-2">
                            <Input value={katalogSearch} onChange={(e) => setKatalogSearch(e.target.value)} placeholder="Cari katalog..." className="w-56" />
                            <Button type="button" variant="secondary" onClick={addItem}><Plus className="h-4 w-4" />Tambah Item</Button>
                        </div>
                    </div>
                    <div className="overflow-x-auto">
                        <table className="min-w-full table-fixed divide-y divide-slate-200 text-sm dark:divide-slate-800">
                            <thead className="bg-slate-50 dark:bg-slate-900">
                                <tr>
                                    <th className="px-3 py-3 text-left">Katalog</th>
                                    <th className="px-3 py-3 text-left"><InputLabel label="Deskripsi" required className="text-xs" /></th>
                                    <th className="px-3 py-3 text-left"><InputLabel label="Qty" required className="text-xs" /></th>
                                    <th className="px-3 py-3 text-left">Satuan</th>
                                    <th className="px-3 py-3 text-left"><InputLabel label="Harga Satuan" required className="text-xs" /></th>
                                    <th className="px-3 py-3 text-right">Jumlah</th>
                                    <th className="px-3 py-3" />
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-100 dark:divide-slate-900">
                                {data.items.map((item, index) => {
                                    const jumlah = Number(item.qty ?? 0) * Number(item.harga_satuan ?? 0);

                                    return (
                                        <tr key={index}>
                                            <td className="min-w-64 px-3 py-3">
                                                <Select value={item.katalog_id} onChange={(e) => setKatalog(index, e.target.value)}>
                                                    <option value="">Pilih katalog...</option>
                                                    {filteredKatalog.map((option) => <option key={option.id} value={option.id}>{option.label}</option>)}
                                                </Select>
                                            </td>
                                            <td className="min-w-64 px-3 py-3">
                                                <Input value={item.deskripsi} onChange={(e) => updateItem(index, 'deskripsi', e.target.value)} />
                                                <InputError message={errors[`items.${index}.deskripsi`]} className="mt-2" />
                                            </td>
                                            <td className="w-24 px-3 py-3">
                                                <Input type="number" min="1" value={item.qty} onChange={(e) => updateItem(index, 'qty', e.target.value)} />
                                                <InputError message={errors[`items.${index}.qty`]} className="mt-2" />
                                            </td>
                                            <td className="w-28 px-3 py-3">
                                                <Input value={item.satuan ?? ''} onChange={(e) => updateItem(index, 'satuan', e.target.value)} />
                                                <InputError message={errors[`items.${index}.satuan`]} className="mt-2" />
                                            </td>
                                            <td className="w-40 px-3 py-3">
                                                <Input inputMode="numeric" value={formatRupiahInput(item.harga_satuan)} onChange={(e) => updateItem(index, 'harga_satuan', parseRupiah(e.target.value))} />
                                                <InputError message={errors[`items.${index}.harga_satuan`]} className="mt-2" />
                                            </td>
                                            <td className="whitespace-nowrap px-3 py-3 text-right">{formatRupiah(jumlah)}</td>
                                            <td className="px-3 py-3">
                                                <Button type="button" size="icon" variant="ghost" onClick={() => removeItem(index)} disabled={data.items.length === 1}>
                                                    <Trash2 className="h-4 w-4" />
                                                </Button>
                                            </td>
                                        </tr>
                                    );
                                })}
                            </tbody>
                            <tfoot className="bg-slate-50 font-semibold dark:bg-slate-900">
                                <tr>
                                    <td className="px-3 py-3" colSpan="5">Total Keseluruhan</td>
                                    <td className="px-3 py-3 text-right">{formatRupiah(total)}</td>
                                    <td />
                                </tr>
                            </tfoot>
                        </table>
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
