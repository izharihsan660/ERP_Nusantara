import Modal from '@/Components/Modal';
import ConfirmDialog from '@/Components/ConfirmDialog';
import FormErrorSummary from '@/Components/FormErrorSummary';
import InputLabel from '@/Components/Form/InputLabel';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Select } from '@/Components/ui/select';
import { Textarea } from '@/Components/ui/textarea';
import { useForm, usePage } from '@inertiajs/react';
import { Ban, Download, Plus } from 'lucide-react';
import { useMemo, useState } from 'react';

const statusStyles = {
    DRAFT: 'bg-slate-100 text-slate-700 ring-slate-200',
    SHIPPED: 'bg-emerald-50 text-emerald-700 ring-emerald-200',
    VOID: 'bg-zinc-800 text-white ring-zinc-800',
};

const emptyItem = {
    katalog_id: null,
    part_no: '',
    deskripsi: '',
    qty_kirim: 0,
    qty_sisa: 0,
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
        katalog_id: item.katalog_id ?? null,
        part_no: item.part_no ?? '',
        deskripsi: item.deskripsi ?? '',
        qty_dipesan: item.qty_dipesan ?? item.qty ?? 0,
        qty_terkirim: item.qty_terkirim ?? 0,
        qty_sisa: item.qty_sisa ?? item.qty ?? 0,
        qty_kirim: item.qty_sisa ?? item.qty ?? 0,
        berat: 0,
        volume: 0,
        dimensi: '',
        sku: item.sku ?? '',
    }));
}

export default function SpbSection({
    spbList = [],
    sourceOptions = [],
    sourceItems = [],
    customer = null,
    customers = [],
    sites = [],
    showCustomerField = false,
}) {
    const permissions = usePage().props.auth.user.permissions ?? [];
    const canCreate = permissions.includes('buat_spb');
    const canDownload = permissions.includes('download_pdf_spb');
    const canVoid = permissions.includes('void_spb');
    const [modal, setModal] = useState(null);
    const [confirm, setConfirm] = useState({ isOpen: false, title: '', message: '', variant: 'danger', onConfirm: () => {} });
    const [siteSearch, setSiteSearch] = useState('');
    const [selectedSpb, setSelectedSpb] = useState(null);
    const initialSourceItems = sourceOptions[0]?.source_items ?? sourceItems;
    const form = useForm({
        source_id: sourceOptions[0]?.id ?? '',
        tgl_spb: today(),
        customer_id: customer?.id ?? '',
        site_id: '',
        nama_ekspedisi: '',
        etd: '',
        eta: '',
        catatan: '',
        items: normalizeItems(initialSourceItems),
    });
    const voidForm = useForm({ alasan_void: '' });

    const selectedCustomerId = form.data.customer_id || customer?.id;
    const hasSourceItems = (sourceOptions[0]?.source_items ?? sourceItems)?.length > 0;
    const filteredSites = useMemo(() => sites
        .filter((site) => !selectedCustomerId || String(site.customer_id) === String(selectedCustomerId))
        .filter((site) => site.label.toLowerCase().includes(siteSearch.toLowerCase())), [sites, selectedCustomerId, siteSearch]);
    const selectedSource = sourceOptions.find((source) => String(source.id) === String(form.data.source_id));

    const handleSourceChange = (sourceId) => {
        form.setData('source_id', sourceId);
        const source = sourceOptions.find((s) => String(s.id) === String(sourceId));
        if (source && source.source_items) {
            const items = normalizeItems(source.source_items);
            form.setData('items', items);
        }
    };

    const openCreate = () => {
        const initialSource = sourceOptions[0];
        const initialItems = initialSource?.source_items
            ? normalizeItems(initialSource.source_items)
            : normalizeItems(sourceItems);

        if (!initialSource || !hasSourceItems) {
            return;
        }

        form.clearErrors();
        form.setData({
            source_id: sourceOptions[0]?.id ?? '',
            tgl_spb: today(),
            customer_id: customer?.id ?? '',
            site_id: '',
            nama_ekspedisi: '',
            etd: '',
            eta: '',
            catatan: '',
            items: initialItems,
        });
        setSiteSearch('');
        setModal('create');
    };

    const updateItem = (index, field, value) => {
        const items = [...form.data.items];
        const item = { ...items[index], [field]: value };

        items[index] = item;
        form.setData('items', items);
    };

    const totalQtyKirim = form.data.items.reduce((sum, item) => sum + (parseInt(item.qty_kirim) || 0), 0);
    const allItemsSent = form.data.items.every((item) => (item.qty_sisa || 0) === 0);

    const submitCreate = (event) => {
        event.preventDefault();
        form.clearErrors();

        if (!selectedSource || !form.data.items.length) {
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

        setConfirm({
            isOpen: true,
            title: 'Void SPB',
            message: 'SPB akan di-void. Status WIP akan dikembalikan ke Belum Tersupply. Lanjutkan?',
            variant: 'danger',
            confirmLabel: 'Ya, Void',
            onConfirm: () => voidForm.post(route('spb.void', selectedSpb.id), {
                preserveScroll: true,
                onSuccess: () => {
                    voidForm.reset();
                    setSelectedSpb(null);
                    setModal(null);
                },
            }),
        });
    };

    return (
        <section className="rounded-lg border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-950">
            <div className="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 p-4 dark:border-slate-800">
                <h2 className="font-semibold text-slate-950 dark:text-white">Pengiriman (SPB)</h2>
                {canCreate && sourceOptions.length > 0 && (
                    <Button type="button" variant="secondary" onClick={openCreate} disabled={!hasSourceItems}>
                        <Plus className="h-4 w-4" />Buat SPB
                    </Button>
                )}
            </div>
            <div className="overflow-x-auto">
                <table className="min-w-full table-fixed divide-y divide-slate-200 text-sm dark:divide-slate-800">
                    <thead className="bg-slate-50 dark:bg-slate-900">
                        <tr>
                            <th className="w-48 px-4 py-3 text-left">No. SPB</th>
                            <th className="w-32 px-4 py-3 text-left">Tanggal</th>
                            <th className="w-48 px-4 py-3 text-left">Ref (PR/PO)</th>
                            <th className="w-64 px-4 py-3 text-left">Items</th>
                            <th className="w-32 px-4 py-3 text-left">Status</th>
                            <th className="w-40 px-4 py-3 text-right">Aksi</th>
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
                                <td className="px-4 py-3"><div className="truncate" title={`${spb.referensi_tipe} - ${spb.no_referensi}`}>{spb.referensi_tipe} - {spb.no_referensi}</div></td>
                                <td className="px-4 py-3">
                                    <div>{spb.items_count} item | {spb.items_qty} qty dikirim</div>
                                    {spb.total_dipesan > 0 && (
                                        <div className="mt-1 text-xs text-slate-500">
                                            Total terkirim: {spb.total_terkirim} / {spb.total_dipesan} qty
                                        </div>
                                    )}
                                </td>
                                <td className="px-4 py-3"><StatusBadge status={spb.status} label={spb.status_label} /></td>
                                <td className="px-4 py-3">
                                    <div className="flex flex-col justify-end gap-2 sm:flex-row">
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
                    <FormErrorSummary
                        errors={form.errors}
                        renderedKeys={[
                            'source_id',
                            'customer_id',
                            'tgl_spb',
                            'site_id',
                            'nama_ekspedisi',
                            'etd',
                            'eta',
                            'catatan',
                            'items',
                            'items.*.qty_kirim',
                        ]}
                    />
                    <div className="mt-5 grid gap-4 md:grid-cols-2">
                        {sourceOptions.length > 1 && (
                            <FormRow label="Sumber SPB" required error={form.errors.source_id}>
                                <Select value={form.data.source_id} onChange={(e) => handleSourceChange(e.target.value)}>
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

                    <div className="mt-6 space-y-3">
                        <div className="flex items-center justify-between">
                            <InputLabel label="Daftar Barang dari Sumber Dokumen" required />
                            {allItemsSent && (
                                <span className="text-sm font-medium text-amber-600">Semua item sudah terkirim penuh</span>
                            )}
                        </div>
                        <FieldError message={form.errors.items} />
                        <div className="overflow-x-auto rounded-lg border border-slate-200 dark:border-slate-800">
                            <table className="min-w-full table-fixed border-collapse text-sm">
                                <thead className="bg-slate-50 dark:bg-slate-900">
                                    <tr>
                                        <th className="w-40 px-3 py-2 text-left text-xs font-medium text-slate-700 dark:text-slate-300">Part No</th>
                                        <th className="w-64 px-3 py-2 text-left text-xs font-medium text-slate-700 dark:text-slate-300">Nama Barang</th>
                                        <th className="w-24 px-3 py-2 text-right text-xs font-medium text-slate-700 dark:text-slate-300">Dipesan</th>
                                        <th className="w-24 px-3 py-2 text-right text-xs font-medium text-slate-700 dark:text-slate-300">Terkirim</th>
                                        <th className="w-24 px-3 py-2 text-right text-xs font-medium text-slate-700 dark:text-slate-300">Sisa</th>
                                        <th className="w-40 px-3 py-2 text-left text-xs font-medium text-slate-700 dark:text-slate-300">Kirim Sekarang</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-slate-100 dark:divide-slate-800">
                                    {form.data.items.map((item, index) => (
                                        <tr key={index} className={(item.qty_sisa || 0) === 0 ? 'bg-slate-50 dark:bg-slate-900/50' : ''}>
                                            <td className="px-3 py-2">
                                                <div className="text-sm font-medium text-slate-900 dark:text-white">{item.part_no || '-'}</div>
                                            </td>
                                            <td className="px-3 py-2">
                                                <div className="text-sm text-slate-700 dark:text-slate-300">{item.deskripsi || '-'}</div>
                                            </td>
                                            <td className="px-3 py-2 text-right">
                                                <div className="text-sm text-slate-700 dark:text-slate-300">{item.qty_dipesan || 0}</div>
                                            </td>
                                            <td className="px-3 py-2 text-right">
                                                <div className="text-sm text-slate-700 dark:text-slate-300">{item.qty_terkirim || 0}</div>
                                            </td>
                                            <td className="px-3 py-2 text-right">
                                                <div className={`text-sm font-medium ${(item.qty_sisa || 0) === 0 ? 'text-slate-400' : 'text-emerald-600 dark:text-emerald-400'}`}>
                                                    {item.qty_sisa || 0}
                                                </div>
                                            </td>
                                            <td className="w-36 px-3 py-2">
                                                <Input
                                                    type="number"
                                                    min="0"
                                                    max={item.qty_sisa || 0}
                                                    value={item.qty_kirim || 0}
                                                    onChange={(e) => updateItem(index, 'qty_kirim', e.target.value)}
                                                    disabled={(item.qty_sisa || 0) === 0}
                                                    className={parseInt(item.qty_kirim || 0) > (item.qty_sisa || 0) ? 'border-red-500 focus:border-red-500 focus:ring-red-200' : ''}
                                                />
                                                <FieldError message={form.errors[`items.${index}.qty_kirim`]} />
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                        <div className="flex items-center justify-between rounded-lg bg-slate-50 px-4 py-3 text-sm dark:bg-slate-900">
                            <div className="text-slate-600 dark:text-slate-400">
                                Minimal 1 item harus memiliki qty kirim &gt; 0
                            </div>
                            <div className="font-medium text-slate-900 dark:text-white">
                                Total Kirim: <span className="text-emerald-600 dark:text-emerald-400">{totalQtyKirim}</span> qty
                            </div>
                        </div>
                    </div>

                    <div className="mt-6 flex flex-col justify-end gap-2 sm:flex-row">
                        <Button type="button" variant="outline" onClick={() => setModal(null)} disabled={form.processing}>Batal</Button>
                        <Button type="submit" disabled={form.processing || !selectedSource || allItemsSent || totalQtyKirim === 0}>
                            Simpan SPB
                        </Button>
                    </div>
                </form>
            </Modal>

            <Modal show={modal === 'void'} onClose={() => setModal(null)} maxWidth="md">
                <form onSubmit={submitVoid} className="p-6">
                    <h2 className="text-lg font-semibold text-slate-950 dark:text-white">Void SPB {selectedSpb?.no_spb ?? ''}</h2>
                    <FormErrorSummary errors={voidForm.errors} renderedKeys={['alasan_void']} />
                    <FormRow label="Alasan void" required error={voidForm.errors.alasan_void}>
                        <Textarea value={voidForm.data.alasan_void} onChange={(e) => voidForm.setData('alasan_void', e.target.value)} />
                    </FormRow>
                    <div className="mt-6 flex flex-col justify-end gap-2 sm:flex-row">
                        <Button type="button" variant="outline" onClick={() => setModal(null)} disabled={voidForm.processing}>Batal</Button>
                        <Button type="submit" variant="destructive" disabled={voidForm.processing}>Proses</Button>
                    </div>
                </form>
            </Modal>
            <ConfirmDialog
                {...confirm}
                onCancel={() => setConfirm((prev) => ({ ...prev, isOpen: false }))}
            />
        </section>
    );
}
