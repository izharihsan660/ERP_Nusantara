import Modal from '@/Components/Modal';
import InputLabel from '@/Components/Form/InputLabel';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Select } from '@/Components/ui/select';
import { Textarea } from '@/Components/ui/textarea';
import { useForm, usePage } from '@inertiajs/react';
import { Ban, Download, Plus, Trash2 } from 'lucide-react';
import { useMemo, useState } from 'react';

const statusStyles = {
    DRAFT: 'bg-slate-100 text-slate-700 ring-slate-200',
    SHIPPED: 'bg-emerald-50 text-emerald-700 ring-emerald-200',
    VOID: 'bg-zinc-800 text-white ring-zinc-800',
};

const emptyItem = {
    part_no: '',
    deskripsi: '',
    qty: 1,
    satuan: '',
    berat: 0,
    volume: 0,
    dimensi: '',
    sku: '',
};

function today() {
    return new Date().toISOString().slice(0, 10);
}

function StatusBadge({ status, label }) {
    return (
        <span className={`inline-flex rounded-full px-2.5 py-1 text-xs font-medium ring-1 ring-inset ${statusStyles[status] ?? statusStyles.DRAFT}`}>
            {label ?? status}
        </span>
    );
}

function FieldError({ message }) {
    return message ? <div className="mt-1 text-sm text-red-600">{message}</div> : null;
}

function FormRow({ label, error, required = false, optional = false, conditionalNote = '', children }) {
    return (
        <div>
            <InputLabel label={label} required={required} optional={optional} conditionalNote={conditionalNote} />
            <div className="mt-1">{children}</div>
            <FieldError message={error} />
        </div>
    );
}

function normalizeItems(items) {
    if (!items?.length) {
        return [{ ...emptyItem }];
    }

    return items.map((item) => ({
        part_no: item.part_no ?? '',
        deskripsi: item.deskripsi ?? '',
        qty: item.qty ?? 1,
        satuan: item.satuan ?? '',
        berat: 0,
        volume: 0,
        dimensi: '',
        sku: item.sku ?? '',
    }));
}

export default function SpbSection({
    spbList = [],
    sourceOptions = [],
    customer = null,
    customers = [],
    sites = [],
    defaultItems = [],
    showCustomerField = false,
}) {
    const permissions = usePage().props.auth.user.permissions ?? [];
    const canCreate = permissions.includes('buat_spb');
    const canDownload = permissions.includes('download_pdf_spb');
    const canVoid = permissions.includes('void_spb');
    const [modal, setModal] = useState(null);
    const [siteSearch, setSiteSearch] = useState('');
    const [selectedSpb, setSelectedSpb] = useState(null);
    const form = useForm({
        source_id: sourceOptions[0]?.id ?? '',
        tgl_spb: today(),
        customer_id: customer?.id ?? '',
        site_id: '',
        nama_ekspedisi: '',
        etd: '',
        eta: '',
        catatan: '',
        items: normalizeItems(defaultItems),
    });
    const voidForm = useForm({ alasan_void: '' });

    const selectedCustomerId = form.data.customer_id || customer?.id;
    const filteredSites = useMemo(() => sites
        .filter((site) => !selectedCustomerId || String(site.customer_id) === String(selectedCustomerId))
        .filter((site) => site.label.toLowerCase().includes(siteSearch.toLowerCase())), [sites, selectedCustomerId, siteSearch]);
    const selectedSource = sourceOptions.find((source) => String(source.id) === String(form.data.source_id));

    const openCreate = () => {
        form.setData({
            source_id: sourceOptions[0]?.id ?? '',
            tgl_spb: today(),
            customer_id: customer?.id ?? '',
            site_id: '',
            nama_ekspedisi: '',
            etd: '',
            eta: '',
            catatan: '',
            items: normalizeItems(defaultItems),
        });
        setSiteSearch('');
        setModal('create');
    };

    const updateItem = (index, field, value) => {
        const items = [...form.data.items];
        items[index] = { ...items[index], [field]: value };
        form.setData('items', items);
    };

    const addItem = () => form.setData('items', [...form.data.items, { ...emptyItem }]);
    const removeItem = (index) => form.setData('items', form.data.items.filter((_, itemIndex) => itemIndex !== index));

    const submitCreate = (event) => {
        event.preventDefault();

        if (!selectedSource) {
            return;
        }

        form.post(route(selectedSource.route, selectedSource.id), {
            preserveScroll: true,
            onSuccess: () => {
                form.reset();
                setModal(null);
            },
        });
    };

    const submitVoid = (event) => {
        event.preventDefault();

        if (!selectedSpb) {
            return;
        }

        voidForm.post(route('spb.void', selectedSpb.id), {
            preserveScroll: true,
            onSuccess: () => {
                voidForm.reset();
                setSelectedSpb(null);
                setModal(null);
            },
        });
    };

    return (
        <section className="rounded-lg border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-950">
            <div className="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 p-4 dark:border-slate-800">
                <h2 className="font-semibold text-slate-950 dark:text-white">Pengiriman (SPB)</h2>
                {canCreate && sourceOptions.length > 0 && (
                    <Button type="button" variant="secondary" onClick={openCreate}>
                        <Plus className="h-4 w-4" />Buat SPB
                    </Button>
                )}
            </div>
            <div className="overflow-x-auto">
                <table className="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                    <thead className="bg-slate-50 dark:bg-slate-900">
                        <tr>
                            <th className="px-4 py-3 text-left">No. SPB</th>
                            <th className="px-4 py-3 text-left">Tanggal</th>
                            <th className="px-4 py-3 text-left">Ref (PR/PO)</th>
                            <th className="px-4 py-3 text-left">Items</th>
                            <th className="px-4 py-3 text-left">Status</th>
                            <th className="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-slate-100 dark:divide-slate-900">
                        {spbList.length === 0 && (
                            <tr>
                                <td className="px-4 py-6 text-center text-slate-500" colSpan="6">Belum ada SPB.</td>
                            </tr>
                        )}
                        {spbList.map((spb) => (
                            <tr key={spb.id}>
                                <td className="whitespace-nowrap px-4 py-3">
                                    <div className="font-medium">{spb.no_spb}</div>
                                    <div className="text-xs text-slate-500">{spb.customer?.nama_customer ?? ''}</div>
                                </td>
                                <td className="whitespace-nowrap px-4 py-3">{spb.tgl_spb}</td>
                                <td className="px-4 py-3">{spb.referensi_tipe} - {spb.no_referensi}</td>
                                <td className="px-4 py-3">{spb.items_count} baris / {spb.items_qty} qty</td>
                                <td className="px-4 py-3"><StatusBadge status={spb.status} label={spb.status_label} /></td>
                                <td className="px-4 py-3">
                                    <div className="flex justify-end gap-2">
                                        {canDownload && spb.status !== 'VOID' && (
                                            <Button asChild size="sm" variant="secondary">
                                                <a href={route('spb.download', spb.id)}><Download className="h-4 w-4" />PDF</a>
                                            </Button>
                                        )}
                                        {canVoid && spb.is_voidable && (
                                            <Button
                                                type="button"
                                                size="sm"
                                                variant="destructive"
                                                onClick={() => {
                                                    setSelectedSpb(spb);
                                                    setModal('void');
                                                }}
                                            >
                                                <Ban className="h-4 w-4" />Void
                                            </Button>
                                        )}
                                    </div>
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>

            <Modal show={modal === 'create'} onClose={() => setModal(null)} maxWidth="2xl">
                <form onSubmit={submitCreate} className="p-6">
                    <h2 className="text-lg font-semibold text-slate-950 dark:text-white">Buat SPB</h2>
                    <div className="mt-5 grid gap-4 md:grid-cols-2">
                        {sourceOptions.length > 1 && (
                            <FormRow label="Sumber WIP" required error={form.errors.source_id}>
                                <Select value={form.data.source_id} onChange={(e) => form.setData('source_id', e.target.value)}>
                                    {sourceOptions.map((source) => <option key={source.id} value={source.id}>{source.label}</option>)}
                                </Select>
                            </FormRow>
                        )}
                        {showCustomerField && (
                            <FormRow label="Customer" required error={form.errors.customer_id}>
                                <Select
                                    value={form.data.customer_id}
                                    onChange={(e) => form.setData({ ...form.data, customer_id: e.target.value, site_id: '' })}
                                >
                                    <option value="">Pilih customer...</option>
                                    {customers.map((option) => <option key={option.id} value={option.id}>{option.label}</option>)}
                                </Select>
                            </FormRow>
                        )}
                        <FormRow label="Tanggal SPB" required error={form.errors.tgl_spb}>
                            <Input type="date" value={form.data.tgl_spb} onChange={(e) => form.setData('tgl_spb', e.target.value)} />
                        </FormRow>
                        <FormRow label="Site Tujuan" optional error={form.errors.site_id}>
                            <Input className="mb-2" value={siteSearch} onChange={(e) => setSiteSearch(e.target.value)} placeholder="Cari site..." />
                            <Select value={form.data.site_id} onChange={(e) => form.setData('site_id', e.target.value)}>
                                <option value="">Pilih site...</option>
                                {filteredSites.map((site) => <option key={site.id} value={site.id}>{site.label}</option>)}
                            </Select>
                        </FormRow>
                        <FormRow label="Nama Ekspedisi" required error={form.errors.nama_ekspedisi}>
                            <Input value={form.data.nama_ekspedisi} onChange={(e) => form.setData('nama_ekspedisi', e.target.value)} />
                        </FormRow>
                        <FormRow label="ETD" optional error={form.errors.etd}>
                            <Input type="date" value={form.data.etd} onChange={(e) => form.setData('etd', e.target.value)} />
                        </FormRow>
                        <FormRow label="ETA" optional error={form.errors.eta}>
                            <Input type="date" value={form.data.eta} onChange={(e) => form.setData('eta', e.target.value)} />
                        </FormRow>
                        <div className="md:col-span-2">
                            <FormRow label="Catatan" optional error={form.errors.catatan}>
                                <Textarea value={form.data.catatan} onChange={(e) => form.setData('catatan', e.target.value)} />
                            </FormRow>
                        </div>
                    </div>

                    <div className="mt-6 rounded-lg border border-slate-200 dark:border-slate-700">
                        <div className="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 p-3 dark:border-slate-700">
                            <div>
                                <h3 className="font-medium text-slate-950 dark:text-white">Items <span className="text-red-600">*</span></h3>
                                <FieldError message={form.errors.items} />
                            </div>
                            <Button type="button" size="sm" variant="secondary" onClick={addItem}><Plus className="h-4 w-4" />Tambah Item</Button>
                        </div>
                        <div className="overflow-x-auto">
                            <table className="min-w-full text-sm">
                                <thead className="bg-slate-50 dark:bg-slate-900">
                                    <tr>
                                        <th className="px-3 py-2 text-left"><InputLabel label="Part No" required className="text-xs" /></th>
                                        <th className="px-3 py-2 text-left"><InputLabel label="Deskripsi" required className="text-xs" /></th>
                                        <th className="px-3 py-2 text-left"><InputLabel label="Qty" required className="text-xs" /></th>
                                        <th className="px-3 py-2 text-left">Satuan</th>
                                        <th className="px-3 py-2 text-left"><InputLabel label="Berat" optional className="text-xs" /></th>
                                        <th className="px-3 py-2 text-left"><InputLabel label="Volume" optional className="text-xs" /></th>
                                        <th className="px-3 py-2 text-left"><InputLabel label="Dimensi" optional className="text-xs" /></th>
                                        <th className="px-3 py-2 text-left"><InputLabel label="SKU" optional className="text-xs" /></th>
                                        <th className="px-3 py-2" />
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-slate-100 dark:divide-slate-800">
                                    {form.data.items.map((item, index) => (
                                        <tr key={index}>
                                            <td className="min-w-32 px-3 py-2">
                                                <Input value={item.part_no} onChange={(e) => updateItem(index, 'part_no', e.target.value)} />
                                                <FieldError message={form.errors[`items.${index}.part_no`]} />
                                            </td>
                                            <td className="min-w-56 px-3 py-2">
                                                <Input value={item.deskripsi} onChange={(e) => updateItem(index, 'deskripsi', e.target.value)} />
                                                <FieldError message={form.errors[`items.${index}.deskripsi`]} />
                                            </td>
                                            <td className="w-24 px-3 py-2">
                                                <Input type="number" min="1" value={item.qty} onChange={(e) => updateItem(index, 'qty', e.target.value)} />
                                                <FieldError message={form.errors[`items.${index}.qty`]} />
                                            </td>
                                            <td className="w-28 px-3 py-2">
                                                <Input value={item.satuan} onChange={(e) => updateItem(index, 'satuan', e.target.value)} />
                                                <FieldError message={form.errors[`items.${index}.satuan`]} />
                                            </td>
                                            <td className="w-28 px-3 py-2">
                                                <Input type="number" min="0" step="0.01" value={item.berat} onChange={(e) => updateItem(index, 'berat', e.target.value)} />
                                                <FieldError message={form.errors[`items.${index}.berat`]} />
                                            </td>
                                            <td className="w-28 px-3 py-2">
                                                <Input type="number" min="0" step="0.01" value={item.volume} onChange={(e) => updateItem(index, 'volume', e.target.value)} />
                                                <FieldError message={form.errors[`items.${index}.volume`]} />
                                            </td>
                                            <td className="w-36 px-3 py-2"><Input value={item.dimensi} onChange={(e) => updateItem(index, 'dimensi', e.target.value)} /></td>
                                            <td className="w-32 px-3 py-2"><Input value={item.sku} onChange={(e) => updateItem(index, 'sku', e.target.value)} /></td>
                                            <td className="px-3 py-2">
                                                <Button type="button" size="icon" variant="ghost" onClick={() => removeItem(index)} disabled={form.data.items.length === 1}>
                                                    <Trash2 className="h-4 w-4" />
                                                </Button>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div className="mt-6 flex justify-end gap-2">
                        <Button type="button" variant="outline" onClick={() => setModal(null)} disabled={form.processing}>Batal</Button>
                        <Button type="submit" disabled={form.processing || !selectedSource}>Simpan SPB</Button>
                    </div>
                </form>
            </Modal>

            <Modal show={modal === 'void'} onClose={() => setModal(null)} maxWidth="md">
                <form onSubmit={submitVoid} className="p-6">
                    <h2 className="text-lg font-semibold text-slate-950 dark:text-white">Void SPB {selectedSpb?.no_spb ?? ''}</h2>
                    <FormRow label="Alasan void" required error={voidForm.errors.alasan_void}>
                        <Textarea value={voidForm.data.alasan_void} onChange={(e) => voidForm.setData('alasan_void', e.target.value)} />
                    </FormRow>
                    <div className="mt-6 flex justify-end gap-2">
                        <Button type="button" variant="outline" onClick={() => setModal(null)} disabled={voidForm.processing}>Batal</Button>
                        <Button type="submit" variant="destructive" disabled={voidForm.processing}>Proses</Button>
                    </div>
                </form>
            </Modal>
        </section>
    );
}
