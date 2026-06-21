import Modal from '@/Components/Modal';
import ConfirmDialog from '@/Components/ConfirmDialog';
import InputLabel from '@/Components/Form/InputLabel';
import PageHeader from '@/Components/PageHeader';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Select } from '@/Components/ui/select';
import { Textarea } from '@/Components/ui/textarea';
import AppLayout from '@/Layouts/AppLayout';
import InvoiceSection from '@/Pages/Shared/InvoiceSection';
import SpbSection from '@/Pages/Shared/SpbSection';
import { formatRupiah } from '@/utils/currency';
import { Head, Link, router, useForm, usePage } from '@inertiajs/react';
import { Ban, Check, Copy, Download, Plus, Send, X } from 'lucide-react';
import { useState, useEffect } from 'react';

const statusStyles = {
    DRAFT: 'bg-slate-100 text-slate-700 ring-slate-200',
    PENDING_APPROVAL: 'bg-amber-50 text-amber-700 ring-amber-200',
    APPROVED: 'bg-emerald-50 text-emerald-700 ring-emerald-200',
    REJECTED: 'bg-red-50 text-red-700 ring-red-200',
    OPEN: 'bg-emerald-50 text-emerald-700 ring-emerald-200',
    COMPLETED: 'bg-blue-50 text-blue-700 ring-blue-200',
    ACTIVE: 'bg-emerald-50 text-emerald-700 ring-emerald-200',
    BELUM_TERSUPPLY: 'bg-amber-50 text-amber-700 ring-amber-200',
    TERSUPPLY: 'bg-emerald-50 text-emerald-700 ring-emerald-200',
    VOID: 'bg-zinc-800 text-white ring-zinc-800',
};

function StatusBadge({ status, label }) {
    return (
        <span className={`inline-flex rounded-full px-2.5 py-1 text-xs font-medium ring-1 ring-inset ${statusStyles[status] ?? statusStyles.DRAFT}`}>
            {label ?? status}
        </span>
    );
}

function Info({ label, value }) {
    return (
        <div>
            <div className="text-xs uppercase text-[hsl(var(--muted-foreground))]">{label}</div>
            <div className="mt-1 font-medium text-slate-900 dark:text-slate-100">{value ?? '-'}</div>
        </div>
    );
}

function ActionModal({ show, title, label, value, error, processing, onChange, onClose, onSubmit }) {
    return (
        <Modal show={show} onClose={onClose} maxWidth="md">
            <form onSubmit={onSubmit} className="p-6">
                <h2 className="text-lg font-semibold text-[hsl(var(--foreground))]">{title}</h2>
                <InputLabel label={label} required className="mt-4" />
                <Textarea className="mt-2" value={value} onChange={(e) => onChange(e.target.value)} />
                {error && <div className="mt-2 text-sm text-red-600">{error}</div>}
                <div className="mt-6 flex flex-col justify-end gap-2 sm:flex-row">
                    <Button type="button" variant="outline" onClick={onClose} disabled={processing}>Batal</Button>
                    <Button type="submit" variant="destructive" disabled={processing}>Proses</Button>
                </div>
            </form>
        </Modal>
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

function SalesOrderModal({ show, form, onClose, onSubmit }) {
    const setMetode = (value) => {
        form.setData({
            ...form.data,
            metode_pembayaran: value,
            top_hari: value === 'TOP' ? form.data.top_hari : '',
        });
    };

    return (
        <Modal show={show} onClose={onClose} maxWidth="2xl">
            <form onSubmit={onSubmit} className="p-6">
                <h2 className="text-lg font-semibold text-[hsl(var(--foreground))]">Input Sales Order</h2>
                <div className="mt-5 grid gap-4 md:grid-cols-2">
                    <FormRow label="No. PO Customer" required error={form.errors.no_po_customer}>
                        <Input value={form.data.no_po_customer} onChange={(e) => form.setData('no_po_customer', e.target.value)} />
                    </FormRow>
                    <FormRow label="No. PR Customer" optional error={form.errors.no_pr_customer}>
                        <Input
                            value={form.data.no_pr_customer}
                            onChange={(e) => form.setData('no_pr_customer', e.target.value)}
                            placeholder="Opsional - khusus jika customer belum keluarkan PO resmi"
                        />
                    </FormRow>
                    <FormRow label="Tanggal PO" required error={form.errors.tgl_po}>
                        <Input type="date" value={form.data.tgl_po} onChange={(e) => form.setData('tgl_po', e.target.value)} />
                    </FormRow>
                    <FormRow label="Metode Pembayaran" required error={form.errors.metode_pembayaran}>
                        <Select value={form.data.metode_pembayaran} onChange={(e) => setMetode(e.target.value)}>
                            <option value="COD">COD</option>
                            <option value="CBD">CBD</option>
                            <option value="TOP">TOP</option>
                        </Select>
                    </FormRow>
                    {form.data.metode_pembayaran === 'TOP' && (
                        <FormRow label="Jangka TOP" conditionalNote="wajib jika metode TOP" error={form.errors.top_hari}>
                            <Input type="number" min="1" value={form.data.top_hari} onChange={(e) => form.setData('top_hari', e.target.value)} />
                        </FormRow>
                    )}
                </div>
                <div className="mt-6 flex flex-col justify-end gap-2 sm:flex-row">
                    <Button type="button" variant="outline" onClick={onClose} disabled={form.processing}>Batal</Button>
                    <Button type="submit" disabled={form.processing}>Simpan</Button>
                </div>
            </form>
        </Modal>
    );
}

function WipOrderModal({ show, form, onClose, onSubmit, source_items = [], quotation }) {
    const [selectedItems, setSelectedItems] = useState([]);

    useEffect(() => {
        if (show && source_items.length > 0) {
            const initialItems = source_items
                .filter(item => item.qty_remaining > 0)
                .map(item => ({
                    quotation_item_id: item.id,
                    qty: item.qty_remaining,
                    selected: true
                }));
            setSelectedItems(initialItems);
        }
    }, [show, source_items]);

    const handleItemToggle = (itemId) => {
        setSelectedItems(prev => 
            prev.map(item => 
                item.quotation_item_id === itemId 
                    ? { ...item, selected: !item.selected }
                    : item
            )
        );
    };

    const handleQtyChange = (itemId, newQty) => {
        setSelectedItems(prev =>
            prev.map(item =>
                item.quotation_item_id === itemId
                    ? { ...item, qty: Math.min(Math.max(0, parseInt(newQty) || 0), getMaxQty(itemId)) }
                    : item
            )
        );
    };

    const getMaxQty = (itemId) => {
        const sourceItem = source_items.find(i => i.id === itemId);
        return sourceItem?.qty_remaining || 0;
    };

    const handleSubmit = (e) => {
        e.preventDefault();
        const items = selectedItems
            .filter(item => item.selected && item.qty > 0)
            .map(({ quotation_item_id, qty }) => {
                const sourceItem = source_items.find((item) => item.id === quotation_item_id);

                return {
                    katalog_id: sourceItem?.katalog_id ?? null,
                    part_no: sourceItem?.part_no ?? '',
                    qty,
                };
            });
        
        if (items.length === 0) {
            alert('Pilih minimal 1 item dengan qty > 0');
            return;
        }

        router.post(route('quotations.wip.store', quotation.id), {
            no_wip: form.data.no_wip,
            tipe_order: form.data.tipe_order,
            nama_ekspedisi: form.data.nama_ekspedisi,
            items: items
        }, {
            onSuccess: () => {
                onClose();
                form.reset();
            }
        });
    };

    const setTipeOrder = (value) => {
        form.setData({
            ...form.data,
            tipe_order: value,
            nama_ekspedisi: value === 'VOR' ? form.data.nama_ekspedisi : '',
        });
    };

    return (
        <Modal show={show} onClose={onClose} maxWidth="3xl">
            <div className="max-h-[90vh] overflow-y-auto">
                <form onSubmit={handleSubmit} className="p-6">
                <h2 className="text-lg font-semibold text-[hsl(var(--foreground))]">Input WIP</h2>
                
                <div className="mt-5 grid gap-4">
                    <FormRow label="No. WIP" required error={form.errors.no_wip}>
                        <Input
                            value={form.data.no_wip}
                            onChange={(e) => form.setData('no_wip', e.target.value)}
                            placeholder="Nomor dari portal RMA"
                        />
                    </FormRow>
                    <FormRow label="Tipe Order" required error={form.errors.tipe_order}>
                        <Select value={form.data.tipe_order} onChange={(e) => setTipeOrder(e.target.value)}>
                            <option value="VOR">VOR</option>
                            <option value="STK">STK</option>
                        </Select>
                    </FormRow>
                    {form.data.tipe_order === 'VOR' && (
                        <FormRow label="Nama Ekspedisi" conditionalNote="wajib jika tipe VOR" error={form.errors.nama_ekspedisi}>
                            <Input value={form.data.nama_ekspedisi} onChange={(e) => form.setData('nama_ekspedisi', e.target.value)} />
                        </FormRow>
                    )}
                </div>

                <div className="mt-6">
                    <h3 className="text-sm font-semibold text-slate-900 dark:text-white mb-3">
                        Pilih Item dari Quotation *
                    </h3>
                    <div className="overflow-x-auto rounded-lg border border-slate-200 dark:border-slate-700">
                        <table className="min-w-full divide-y divide-slate-200 dark:divide-slate-700 text-sm">
                            <thead className="bg-slate-50 dark:bg-slate-800">
                                <tr>
                                    <th className="px-3 py-2 text-center text-xs font-medium text-slate-700 dark:text-slate-300 w-12">✓</th>
                                    <th className="px-3 py-2 text-left text-xs font-medium text-slate-700 dark:text-slate-300">Part No</th>
                                    <th className="px-3 py-2 text-left text-xs font-medium text-slate-700 dark:text-slate-300">Nama Barang</th>
                                    <th className="px-3 py-2 text-right text-xs font-medium text-slate-700 dark:text-slate-300 w-24">Dipesan</th>
                                    <th className="px-3 py-2 text-right text-xs font-medium text-slate-700 dark:text-slate-300 w-24">Di-WIP</th>
                                    <th className="px-3 py-2 text-right text-xs font-medium text-slate-700 dark:text-slate-300 w-24">Sisa</th>
                                    <th className="px-3 py-2 text-right text-xs font-medium text-slate-700 dark:text-slate-300 w-32">Qty WIP Ini</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-200 bg-white dark:divide-slate-700 dark:bg-slate-900">
                                {source_items.map((sourceItem) => {
                                    const selected = selectedItems.find(i => i.quotation_item_id === sourceItem.id);
                                    const isDisabled = sourceItem.qty_remaining <= 0;
                                                                        return (
                                        <tr key={sourceItem.id} className={isDisabled ? 'opacity-50 bg-slate-50 dark:bg-slate-800/50' : ''}>
                                            <td className="px-3 py-2 text-center">
                                                <input type="checkbox" checked={selected?.selected || false} onChange={() => handleItemToggle(sourceItem.id)} disabled={isDisabled} className="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500 disabled:cursor-not-allowed" />
                                            </td>
                                            <td className="px-3 py-2 text-slate-900 dark:text-white font-mono text-xs">{sourceItem.part_no}</td>
                                            <td className="px-3 py-2 text-slate-900 dark:text-white">{sourceItem.deskripsi}</td>
                                            <td className="px-3 py-2 text-right text-slate-900 dark:text-white">{sourceItem.qty}</td>
                                            <td className="px-3 py-2 text-right text-slate-600 dark:text-slate-400">{sourceItem.qty_used || 0}</td>
                                            <td className="px-3 py-2 text-right font-semibold text-slate-900 dark:text-white">{sourceItem.qty_remaining}</td>
                                            <td className="px-3 py-2">
                                                <Input type="number" min="0" max={sourceItem.qty_remaining} value={selected?.qty || 0} onChange={(e) => handleQtyChange(sourceItem.id, e.target.value)} disabled={isDisabled || !selected?.selected} className="text-right text-sm" />
                                            </td>
                                        </tr>
                                    );
                                })}
                            </tbody>
                        </table>
                    </div>
                    {form.errors.items && <p className="mt-1 text-sm text-red-600 dark:text-red-400">{form.errors.items}</p>}
                </div>

                <div className="mt-6 flex flex-col justify-end gap-2 sm:flex-row">
                    <Button type="button" variant="outline" onClick={onClose} disabled={form.processing}>Batal</Button>
                    <Button type="submit" disabled={form.processing}>Simpan</Button>
                </div>
            </form>
            </div>
        </Modal>
    );
}

function ProcessSection({ title, children }) {
    return (
        <section className="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] p-6 shadow-sm">
            <h2 className="font-semibold text-[hsl(var(--foreground))]">{title}</h2>
            <div className="mt-4">{children}</div>
        </section>
    );
}

export default function Show({ quotation, sites }) {
    const permissions = usePage().props.auth.user.permissions ?? [];
    const [modal, setModal] = useState(null);
    const [selectedWip, setSelectedWip] = useState(null);
    const [confirm, setConfirm] = useState({ isOpen: false, title: '', message: '', variant: 'danger', onConfirm: () => {} });
    const rejectForm = useForm({ catatan_rejection: '' });
    const voidForm = useForm({ alasan_void: '' });
    const salesOrderForm = useForm({
        no_po_customer: '',
        no_pr_customer: '',
        tgl_po: '',
        metode_pembayaran: 'COD',
        top_hari: '',
    });
    const wipOrderForm = useForm({
        no_wip: '',
        tipe_order: 'VOR',
        nama_ekspedisi: '',
    });
    const voidSalesOrderForm = useForm({ alasan_void: '' });
    const voidWipOrderForm = useForm({ alasan_void: '' });

    const submitReject = (event) => {
        event.preventDefault();
        setConfirm({
            isOpen: true,
            title: 'Tolak Quotation',
            message: 'Kamu akan menolak quotation ini. Pembuat akan mendapat notifikasi. Lanjutkan?',
            variant: 'warning',
            confirmLabel: 'Ya, Tolak',
            onConfirm: () => rejectForm.post(route('quotations.reject', quotation.id), { onSuccess: () => setModal(null) }),
        });
    };

    const submitVoid = (event) => {
        event.preventDefault();
        setConfirm({
            isOpen: true,
            title: 'Void Quotation',
            message: 'Quotation akan di-void dan tidak bisa diaktifkan kembali. Aksi ini permanen. Lanjutkan?',
            variant: 'danger',
            confirmLabel: 'Ya, Void',
            onConfirm: () => voidForm.post(route('quotations.void', quotation.id), { onSuccess: () => setModal(null) }),
        });
    };

    const submitSalesOrder = (event) => {
        event.preventDefault();
        salesOrderForm.post(route('quotations.sales-orders.store', quotation.id), {
            onSuccess: () => {
                salesOrderForm.reset();
                setModal(null);
            },
        });
    };

    const submitWipOrder = (event) => {
        event.preventDefault();
        wipOrderForm.post(route('sales-orders.wip-orders.store', quotation.sales_order.id), {
            onSuccess: () => {
                wipOrderForm.reset();
                setModal(null);
            },
        });
    };

    const submitVoidSalesOrder = (event) => {
        event.preventDefault();
        setConfirm({
            isOpen: true,
            title: 'Void PO Customer',
            message: 'PO Customer akan di-void. Lanjutkan?',
            variant: 'danger',
            confirmLabel: 'Ya, Void',
            onConfirm: () => voidSalesOrderForm.post(route('sales-orders.void', quotation.sales_order.id), {
                onSuccess: () => {
                    voidSalesOrderForm.reset();
                    setModal(null);
                },
            }),
        });
    };

    const submitVoidWipOrder = (event) => {
        event.preventDefault();
        if (!selectedWip) {
            return;
        }

        setConfirm({
            isOpen: true,
            title: 'Void WIP',
            message: 'WIP akan di-void. Lanjutkan?',
            variant: 'danger',
            confirmLabel: 'Ya, Void',
            onConfirm: () => voidWipOrderForm.post(route('wip-orders.void', selectedWip.id), {
                onSuccess: () => {
                    voidWipOrderForm.reset();
                    setSelectedWip(null);
                    setModal(null);
                },
            }),
        });
    };

    const canApprove = permissions.includes('approve_quotation');
    const canVoid = permissions.includes('void_quotation') && quotation.status !== 'VOID';
    const canCreate = permissions.includes('buat_quotation');
    const canDownload = permissions.includes('download_pdf_quotation');
    const canInputSalesOrder = permissions.includes('input_sales_order');
    const canVoidSalesOrder = permissions.includes('void_sales_order');
    const canInputWIP = permissions.includes('buat_wip');
    const canVoidWIP = permissions.includes('void_wip');
    const spbList = quotation.sales_order?.wip_orders?.flatMap((wip) => wip.spb ?? []) ?? [];
    const wipSourceOptions = quotation.sales_order?.wip_orders
        ?.filter((wip) => wip.status === 'ACTIVE')
        .map((wip) => ({
            id: wip.id,
            label: `${wip.no_wip} - ${wip.tipe_order_label}`,
            route: 'wip-orders.spb.store',
            source_items: wip.source_items ?? [],
        })) ?? [];

    return (
        <AppLayout title="Detail Quotation">
            <Head title={quotation.no_quotation} />
            <PageHeader
                title={quotation.no_quotation}
                description="Pusat transaksi quotation dan proses turunannya."
                actions={(
                    <>
                        <Button asChild variant="outline"><Link href={route('quotations.index')}>Kembali</Link></Button>
                        {quotation.status === 'DRAFT' && canCreate && (
                            <Button type="button" onClick={() => setConfirm({ isOpen: true, title: 'Submit Quotation', message: 'Quotation akan dikirim ke Manager untuk disetujui. Lanjutkan?', variant: 'warning', confirmLabel: 'Ya, Submit', onConfirm: () => router.post(route('quotations.submit', quotation.id)) })}><Send className="h-4 w-4" />Submit ke Manager</Button>
                        )}
                        {quotation.status === 'PENDING_APPROVAL' && canApprove && (
                            <>
                                <Button type="button" onClick={() => setConfirm({ isOpen: true, title: 'Approve Quotation', message: 'Kamu akan menyetujui quotation ini. Dokumen akan di-generate dan dikirim. Lanjutkan?', variant: 'success', confirmLabel: 'Ya, Approve', onConfirm: () => router.post(route('quotations.approve', quotation.id)) })}><Check className="h-4 w-4" />Approve</Button>
                                <Button type="button" variant="destructive" onClick={() => setModal('reject')}><X className="h-4 w-4" />Reject</Button>
                            </>
                        )}
                        {quotation.status === 'APPROVED' && canDownload && (
                            <Button asChild variant="secondary"><a href={route('quotations.download', quotation.id)}><Download className="h-4 w-4" />Download PDF</a></Button>
                        )}
                        {['APPROVED', 'REJECTED'].includes(quotation.status) && canCreate && (
                            <Button type="button" variant="secondary" onClick={() => router.post(route('quotations.duplicate', quotation.id))}><Copy className="h-4 w-4" />Duplikasi</Button>
                        )}
                        {canVoid && (
                            <Button type="button" variant="destructive" onClick={() => setModal('void')}><Ban className="h-4 w-4" />Void</Button>
                        )}
                    </>
                )}
            />

            <div className="space-y-6">
                <section className="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] p-6 shadow-sm">
                    <div className="mb-4 flex items-center justify-between gap-3">
                        <h2 className="font-semibold text-[hsl(var(--foreground))]">Informasi Quotation</h2>
                        <StatusBadge status={quotation.status} label={quotation.status_label} />
                    </div>
                    <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                        <Info label="Customer" value={quotation.customer?.nama_customer} />
                        <Info label="Tanggal" value={quotation.tgl_quotation} />
                        <Info label="Template" value={quotation.template?.nama_template} />
                        <Info label="Dibuat oleh" value={quotation.created_by?.name} />
                        <Info label="Revisi" value={quotation.revisi} />
                        <Info label="Approved oleh" value={quotation.approved_by?.name} />
                        <Info label="Tanggal approve" value={quotation.approved_at} />
                        <Info label="Voided oleh" value={quotation.voided_by?.name} />
                    </div>
                    {quotation.catatan && <div className="mt-4 rounded-md bg-slate-50 p-3 text-sm text-slate-700 dark:bg-slate-900 dark:text-slate-300">Catatan: {quotation.catatan}</div>}
                    {quotation.catatan_rejection && <div className="mt-4 rounded-md bg-red-50 p-3 text-sm text-red-700">Catatan rejection: {quotation.catatan_rejection}</div>}
                    {quotation.alasan_void && <div className="mt-4 rounded-md bg-zinc-100 p-3 text-sm text-zinc-700">Alasan void: {quotation.alasan_void}</div>}
                </section>

                <section className="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] shadow-sm">
                    <div className="border-b border-[hsl(var(--border))] p-6">
                        <h2 className="font-semibold text-[hsl(var(--foreground))]">Barang</h2>
                    </div>
                    <div className="overflow-x-auto">
                        <table className="min-w-full table-fixed divide-y divide-[hsl(var(--border))] text-sm">
                            <thead className="bg-[hsl(var(--muted))]/60">
                                <tr>
                                    <th className="px-4 py-3 text-left">Part No</th>
                                    <th className="px-4 py-3 text-left">Nama</th>
                                    <th className="px-4 py-3 text-right">Qty</th>
                                    <th className="px-4 py-3 text-left">Satuan</th>
                                    <th className="px-4 py-3 text-right">Harga</th>
                                    <th className="px-4 py-3 text-right">Total</th>
                                    <th className="px-4 py-3 text-right">HPP</th>
                                    <th className="px-4 py-3 text-right">Profit</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-[hsl(var(--border))]">
                                {quotation.items.map((item) => (
                                    <tr key={item.id}>
                                        <td className="whitespace-nowrap px-4 py-3">{item.part_no}</td>
                                        <td className="px-4 py-3">{item.deskripsi}</td>
                                        <td className="px-4 py-3 text-right">{item.qty}</td>
                                        <td className="px-4 py-3">{item.satuan}</td>
                                        <td className="px-4 py-3 text-right">{formatRupiah(item.harga_satuan)}</td>
                                        <td className="px-4 py-3 text-right">{formatRupiah(item.jumlah)}</td>
                                        <td className="px-4 py-3 text-right">{formatRupiah(Number(item.qty) * Number(item.hpp_satuan))}</td>
                                        <td className="px-4 py-3 text-right">{formatRupiah(item.profit)}</td>
                                    </tr>
                                ))}
                            </tbody>
                            <tfoot className="bg-slate-50 font-semibold dark:bg-slate-900">
                                <tr>
                                    <td className="px-4 py-3" colSpan="5">Total</td>
                                    <td className="px-4 py-3 text-right">{formatRupiah(quotation.total)}</td>
                                    <td className="px-4 py-3 text-right">{formatRupiah(quotation.total_hpp)}</td>
                                    <td className="px-4 py-3 text-right">{formatRupiah(quotation.total_profit)}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </section>

                {quotation.status === 'APPROVED' && (
                    <ProcessSection title="Sales Order">
                        {!quotation.sales_order ? (
                            <div className="flex flex-wrap items-center justify-between gap-3 rounded-md bg-slate-50 p-4 dark:bg-slate-900">
                                <div className="text-sm text-slate-600 dark:text-slate-300">Belum ada Sales Order untuk quotation ini.</div>
                                {canInputSalesOrder && (
                                    <Button type="button" variant="secondary" onClick={() => setModal('sales-order')}>
                                        <Plus className="h-4 w-4" />Input Sales Order
                                    </Button>
                                )}
                            </div>
                        ) : (
                            <div className="space-y-4">
                                <div className="flex flex-wrap items-start justify-between gap-3">
                                    <div className="grid flex-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
                                        <Info label="No. PO Customer" value={quotation.sales_order.no_po_customer} />
                                        {quotation.sales_order.no_pr_customer && (
                                            <Info label="No. PR" value={quotation.sales_order.no_pr_customer} />
                                        )}
                                        <Info label="Tanggal PO" value={quotation.sales_order.tgl_po} />
                                        <Info label="Metode Bayar" value={quotation.sales_order.metode_pembayaran_label} />
                                        <Info label="Total Nilai" value={formatRupiah(quotation.total)} />
                                        {quotation.sales_order.metode_pembayaran === 'TOP' && (
                                            <>
                                                <Info label="TOP Hari" value={quotation.sales_order.top_hari} />
                                                <Info label="Jatuh Tempo" value={quotation.sales_order.tgl_jatuh_tempo} />
                                            </>
                                        )}
                                        <div>
                                            <div className="text-xs uppercase text-[hsl(var(--muted-foreground))]">Status</div>
                                            <div className="mt-1"><StatusBadge status={quotation.sales_order.status} label={quotation.sales_order.status_label} /></div>
                                        </div>
                                    </div>
                                    {canVoidSalesOrder && quotation.sales_order.is_voidable && (
                                        <Button type="button" variant="destructive" onClick={() => setModal('void-sales-order')}>
                                            <Ban className="h-4 w-4" />Void Sales Order
                                        </Button>
                                    )}
                                </div>
                                {quotation.sales_order.alasan_void && (
                                    <div className="rounded-md bg-zinc-100 p-3 text-sm text-zinc-700">Alasan void: {quotation.sales_order.alasan_void}</div>
                                )}
                            </div>
                        )}
                    </ProcessSection>
                )}

                {quotation.status === 'APPROVED' && quotation.sales_order && (
                    <>
                        <ProcessSection title="WIP">
                            <div className="mb-4 flex flex-wrap items-center justify-between gap-3">
                                <div className="text-sm font-medium text-slate-700 dark:text-slate-200">WIP - Order ke RMA</div>
                                {canInputWIP && quotation.sales_order.status === 'OPEN' && (
                                    <Button type="button" variant="secondary" onClick={() => setModal('wip-order')}>
                                        <Plus className="h-4 w-4" />Input WIP
                                    </Button>
                                )}
                            </div>
                            <div className="overflow-x-auto">
                                <table className="min-w-full table-fixed divide-y divide-[hsl(var(--border))] text-sm">
                                    <thead className="bg-[hsl(var(--muted))]/60">
                                        <tr>
                                            <th className="px-4 py-3 text-left">No. WIP</th>
                                            <th className="px-4 py-3 text-left">Tipe</th>
                                            <th className="px-4 py-3 text-left">Ekspedisi</th>
                                            <th className="px-4 py-3 text-left">Status Supply</th>
                                            <th className="px-4 py-3 text-right">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y divide-[hsl(var(--border))]">
                                        {quotation.sales_order.wip_orders.length === 0 && (
                                            <tr>
                                                <td className="px-4 py-6 text-center text-[hsl(var(--muted-foreground))]" colSpan="5">Belum ada WIP.</td>
                                            </tr>
                                        )}
                                        {quotation.sales_order.wip_orders.map((wip) => (
                                            <tr key={wip.id}>
                                                <td className="whitespace-nowrap px-4 py-3">{wip.no_wip}</td>
                                                <td className="px-4 py-3">{wip.tipe_order_label}</td>
                                                <td className="px-4 py-3">{wip.nama_ekspedisi ?? '-'}</td>
                                                <td className="px-4 py-3"><StatusBadge status={wip.status_supply} label={wip.status_supply_label} /></td>
                                                <td className="px-4 py-3 text-right">
                                                    {canVoidWIP && wip.is_voidable && (
                                                        <Button
                                                            type="button"
                                                            size="sm"
                                                            variant="destructive"
                                                            onClick={() => {
                                                                setSelectedWip(wip);
                                                                setModal('void-wip-order');
                                                            }}
                                                        >
                                                            <Ban className="h-4 w-4" />Void
                                                        </Button>
                                                    )}
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>
                        </ProcessSection>
                        <SpbSection
                            spbList={spbList}
                            sourceOptions={wipSourceOptions}
                            customer={quotation.customer}
                            sites={sites}
                        />
                        <InvoiceSection
                            spbList={spbList}
                            defaultPayment={{
                                metode_pembayaran: quotation.sales_order.metode_pembayaran,
                                top_hari: quotation.sales_order.top_hari,
                            }}
                        />
                    </>
                )}
            </div>

            <ActionModal
                show={modal === 'reject'}
                title="Reject Quotation"
                label="Catatan rejection"
                value={rejectForm.data.catatan_rejection}
                error={rejectForm.errors.catatan_rejection}
                processing={rejectForm.processing}
                onChange={(value) => rejectForm.setData('catatan_rejection', value)}
                onClose={() => setModal(null)}
                onSubmit={submitReject}
            />
            <ActionModal
                show={modal === 'void'}
                title="Void Quotation"
                label="Alasan void"
                value={voidForm.data.alasan_void}
                error={voidForm.errors.alasan_void}
                processing={voidForm.processing}
                onChange={(value) => voidForm.setData('alasan_void', value)}
                onClose={() => setModal(null)}
                onSubmit={submitVoid}
            />
            <SalesOrderModal
                show={modal === 'sales-order'}
                form={salesOrderForm}
                onClose={() => setModal(null)}
                onSubmit={submitSalesOrder}
            />
            <WipOrderModal
                show={modal === 'wip-order'}
                form={wipOrderForm}
                onClose={() => setModal(null)}
                onSubmit={submitWipOrder}
                source_items={quotation.source_items || []}
                quotation={quotation}
            />
            <ActionModal
                show={modal === 'void-sales-order'}
                title="Void Sales Order"
                label="Alasan void"
                value={voidSalesOrderForm.data.alasan_void}
                error={voidSalesOrderForm.errors.alasan_void}
                processing={voidSalesOrderForm.processing}
                onChange={(value) => voidSalesOrderForm.setData('alasan_void', value)}
                onClose={() => setModal(null)}
                onSubmit={submitVoidSalesOrder}
            />
            <ActionModal
                show={modal === 'void-wip-order'}
                title={`Void WIP ${selectedWip?.no_wip ?? ''}`}
                label="Alasan void"
                value={voidWipOrderForm.data.alasan_void}
                error={voidWipOrderForm.errors.alasan_void}
                processing={voidWipOrderForm.processing}
                onChange={(value) => voidWipOrderForm.setData('alasan_void', value)}
                onClose={() => {
                    setSelectedWip(null);
                    setModal(null);
                }}
                onSubmit={submitVoidWipOrder}
            />
            <ConfirmDialog
                {...confirm}
                onCancel={() => setConfirm((prev) => ({ ...prev, isOpen: false }))}
            />
        </AppLayout>
    );
}
