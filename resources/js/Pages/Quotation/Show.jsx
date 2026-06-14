import Modal from '@/Components/Modal';
import PageHeader from '@/Components/PageHeader';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Select } from '@/Components/ui/select';
import { Textarea } from '@/Components/ui/textarea';
import AppLayout from '@/Layouts/AppLayout';
import { Head, Link, router, useForm, usePage } from '@inertiajs/react';
import { Ban, Check, Copy, Download, Plus, Send, X } from 'lucide-react';
import { useState } from 'react';

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

function money(value) {
    return Number(value ?? 0).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function Info({ label, value }) {
    return (
        <div>
            <div className="text-xs uppercase text-slate-500">{label}</div>
            <div className="mt-1 font-medium text-slate-900 dark:text-slate-100">{value ?? '-'}</div>
        </div>
    );
}

function ActionModal({ show, title, label, value, error, processing, onChange, onClose, onSubmit }) {
    return (
        <Modal show={show} onClose={onClose} maxWidth="md">
            <form onSubmit={onSubmit} className="p-6">
                <h2 className="text-lg font-semibold text-slate-950 dark:text-white">{title}</h2>
                <label className="mt-4 block text-sm font-medium text-slate-700 dark:text-slate-200">{label}</label>
                <Textarea className="mt-2" value={value} onChange={(e) => onChange(e.target.value)} />
                {error && <div className="mt-2 text-sm text-red-600">{error}</div>}
                <div className="mt-6 flex justify-end gap-2">
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

function FormRow({ label, error, children }) {
    return (
        <div>
            <Label>{label}</Label>
            <div className="mt-1">{children}</div>
            <FieldError message={error} />
        </div>
    );
}

function PurchaseOrderModal({ show, form, onClose, onSubmit }) {
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
                <h2 className="text-lg font-semibold text-slate-950 dark:text-white">Input PO Customer</h2>
                <div className="mt-5 grid gap-4 md:grid-cols-2">
                    <FormRow label="No. PO Customer" error={form.errors.no_po_customer}>
                        <Input value={form.data.no_po_customer} onChange={(e) => form.setData('no_po_customer', e.target.value)} />
                    </FormRow>
                    <FormRow label="No. PR Customer" error={form.errors.no_pr_customer}>
                        <Input
                            value={form.data.no_pr_customer}
                            onChange={(e) => form.setData('no_pr_customer', e.target.value)}
                            placeholder="Opsional - khusus jika customer belum keluarkan PO resmi"
                        />
                    </FormRow>
                    <FormRow label="Tanggal PO" error={form.errors.tgl_po}>
                        <Input type="date" value={form.data.tgl_po} onChange={(e) => form.setData('tgl_po', e.target.value)} />
                    </FormRow>
                    <FormRow label="Metode Pembayaran" error={form.errors.metode_pembayaran}>
                        <Select value={form.data.metode_pembayaran} onChange={(e) => setMetode(e.target.value)}>
                            <option value="COD">COD</option>
                            <option value="CBD">CBD</option>
                            <option value="TOP">TOP</option>
                        </Select>
                    </FormRow>
                    {form.data.metode_pembayaran === 'TOP' && (
                        <FormRow label="Jangka Waktu (hari)" error={form.errors.top_hari}>
                            <Input type="number" min="1" value={form.data.top_hari} onChange={(e) => form.setData('top_hari', e.target.value)} />
                        </FormRow>
                    )}
                </div>
                <div className="mt-6 flex justify-end gap-2">
                    <Button type="button" variant="outline" onClick={onClose} disabled={form.processing}>Batal</Button>
                    <Button type="submit" disabled={form.processing}>Simpan</Button>
                </div>
            </form>
        </Modal>
    );
}

function WipOrderModal({ show, form, onClose, onSubmit }) {
    const setTipeOrder = (value) => {
        form.setData({
            ...form.data,
            tipe_order: value,
            nama_ekspedisi: value === 'VOR' ? form.data.nama_ekspedisi : '',
        });
    };

    return (
        <Modal show={show} onClose={onClose} maxWidth="lg">
            <form onSubmit={onSubmit} className="p-6">
                <h2 className="text-lg font-semibold text-slate-950 dark:text-white">Input WIP</h2>
                <div className="mt-5 grid gap-4">
                    <FormRow label="No. WIP" error={form.errors.no_wip}>
                        <Input
                            value={form.data.no_wip}
                            onChange={(e) => form.setData('no_wip', e.target.value)}
                            placeholder="Nomor dari portal RMA"
                        />
                    </FormRow>
                    <FormRow label="Tipe Order" error={form.errors.tipe_order}>
                        <Select value={form.data.tipe_order} onChange={(e) => setTipeOrder(e.target.value)}>
                            <option value="VOR">VOR</option>
                            <option value="STK">STK</option>
                        </Select>
                    </FormRow>
                    {form.data.tipe_order === 'VOR' && (
                        <FormRow label="Nama Ekspedisi" error={form.errors.nama_ekspedisi}>
                            <Input value={form.data.nama_ekspedisi} onChange={(e) => form.setData('nama_ekspedisi', e.target.value)} />
                        </FormRow>
                    )}
                </div>
                <div className="mt-6 flex justify-end gap-2">
                    <Button type="button" variant="outline" onClick={onClose} disabled={form.processing}>Batal</Button>
                    <Button type="submit" disabled={form.processing}>Simpan</Button>
                </div>
            </form>
        </Modal>
    );
}

function ProcessSection({ title, children }) {
    return (
        <section className="rounded-lg border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-950">
            <h2 className="font-semibold text-slate-950 dark:text-white">{title}</h2>
            <div className="mt-4">{children}</div>
        </section>
    );
}

export default function Show({ quotation }) {
    const permissions = usePage().props.auth.user.permissions ?? [];
    const [modal, setModal] = useState(null);
    const [selectedWip, setSelectedWip] = useState(null);
    const rejectForm = useForm({ catatan_rejection: '' });
    const voidForm = useForm({ alasan_void: '' });
    const purchaseOrderForm = useForm({
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
    const voidPurchaseOrderForm = useForm({ alasan_void: '' });
    const voidWipOrderForm = useForm({ alasan_void: '' });

    const submitReject = (event) => {
        event.preventDefault();
        rejectForm.post(route('quotations.reject', quotation.id), { onSuccess: () => setModal(null) });
    };

    const submitVoid = (event) => {
        event.preventDefault();
        voidForm.post(route('quotations.void', quotation.id), { onSuccess: () => setModal(null) });
    };

    const submitPurchaseOrder = (event) => {
        event.preventDefault();
        purchaseOrderForm.post(route('quotations.purchase-orders.store', quotation.id), {
            onSuccess: () => {
                purchaseOrderForm.reset();
                setModal(null);
            },
        });
    };

    const submitWipOrder = (event) => {
        event.preventDefault();
        wipOrderForm.post(route('purchase-orders.wip-orders.store', quotation.purchase_order.id), {
            onSuccess: () => {
                wipOrderForm.reset();
                setModal(null);
            },
        });
    };

    const submitVoidPurchaseOrder = (event) => {
        event.preventDefault();
        voidPurchaseOrderForm.post(route('purchase-orders.void', quotation.purchase_order.id), {
            onSuccess: () => {
                voidPurchaseOrderForm.reset();
                setModal(null);
            },
        });
    };

    const submitVoidWipOrder = (event) => {
        event.preventDefault();
        if (!selectedWip) {
            return;
        }

        voidWipOrderForm.post(route('wip-orders.void', selectedWip.id), {
            onSuccess: () => {
                voidWipOrderForm.reset();
                setSelectedWip(null);
                setModal(null);
            },
        });
    };

    const canApprove = permissions.includes('Quotation approve');
    const canVoid = permissions.includes('Quotation void') && quotation.status !== 'VOID';
    const canCreate = permissions.includes('Quotation buat');
    const canDownload = permissions.includes('Quotation download_pdf');
    const canInputPO = permissions.includes('PO Customer input');
    const canVoidPO = permissions.includes('PO Customer void');
    const canInputWIP = permissions.includes('WIP buat');
    const canVoidWIP = permissions.includes('WIP void');

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
                            <Button type="button" onClick={() => router.post(route('quotations.submit', quotation.id))}><Send className="h-4 w-4" />Submit ke Manager</Button>
                        )}
                        {quotation.status === 'PENDING_APPROVAL' && canApprove && (
                            <>
                                <Button type="button" onClick={() => router.post(route('quotations.approve', quotation.id))}><Check className="h-4 w-4" />Approve</Button>
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
                <section className="rounded-lg border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                    <div className="mb-4 flex items-center justify-between gap-3">
                        <h2 className="font-semibold text-slate-950 dark:text-white">Informasi Quotation</h2>
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
                    {quotation.catatan_rejection && <div className="mt-4 rounded-md bg-red-50 p-3 text-sm text-red-700">Catatan rejection: {quotation.catatan_rejection}</div>}
                    {quotation.alasan_void && <div className="mt-4 rounded-md bg-zinc-100 p-3 text-sm text-zinc-700">Alasan void: {quotation.alasan_void}</div>}
                </section>

                <section className="rounded-lg border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-950">
                    <div className="border-b border-slate-200 p-4 dark:border-slate-800">
                        <h2 className="font-semibold text-slate-950 dark:text-white">Barang</h2>
                    </div>
                    <div className="overflow-x-auto">
                        <table className="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                            <thead className="bg-slate-50 dark:bg-slate-900">
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
                            <tbody className="divide-y divide-slate-100 dark:divide-slate-900">
                                {quotation.items.map((item) => (
                                    <tr key={item.id}>
                                        <td className="whitespace-nowrap px-4 py-3">{item.part_no}</td>
                                        <td className="px-4 py-3">{item.deskripsi}</td>
                                        <td className="px-4 py-3 text-right">{item.qty}</td>
                                        <td className="px-4 py-3">{item.satuan}</td>
                                        <td className="px-4 py-3 text-right">{money(item.harga_satuan)}</td>
                                        <td className="px-4 py-3 text-right">{money(item.jumlah)}</td>
                                        <td className="px-4 py-3 text-right">{money(Number(item.qty) * Number(item.hpp_satuan))}</td>
                                        <td className="px-4 py-3 text-right">{money(item.profit)}</td>
                                    </tr>
                                ))}
                            </tbody>
                            <tfoot className="bg-slate-50 font-semibold dark:bg-slate-900">
                                <tr>
                                    <td className="px-4 py-3" colSpan="5">Total</td>
                                    <td className="px-4 py-3 text-right">{money(quotation.total)}</td>
                                    <td className="px-4 py-3 text-right">{money(quotation.total_hpp)}</td>
                                    <td className="px-4 py-3 text-right">{money(quotation.total_profit)}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </section>

                {quotation.status === 'APPROVED' && (
                    <ProcessSection title="PO Customer">
                        {!quotation.purchase_order ? (
                            <div className="flex flex-wrap items-center justify-between gap-3 rounded-md bg-slate-50 p-4 dark:bg-slate-900">
                                <div className="text-sm text-slate-600 dark:text-slate-300">Belum ada PO Customer untuk quotation ini.</div>
                                {canInputPO && (
                                    <Button type="button" variant="secondary" onClick={() => setModal('purchase-order')}>
                                        <Plus className="h-4 w-4" />Input PO Customer
                                    </Button>
                                )}
                            </div>
                        ) : (
                            <div className="space-y-4">
                                <div className="flex flex-wrap items-start justify-between gap-3">
                                    <div className="grid flex-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
                                        <Info label="No. PO Customer" value={quotation.purchase_order.no_po_customer} />
                                        {quotation.purchase_order.no_pr_customer && (
                                            <Info label="No. PR" value={quotation.purchase_order.no_pr_customer} />
                                        )}
                                        <Info label="Tanggal PO" value={quotation.purchase_order.tgl_po} />
                                        <Info label="Metode Bayar" value={quotation.purchase_order.metode_pembayaran_label} />
                                        {quotation.purchase_order.metode_pembayaran === 'TOP' && (
                                            <>
                                                <Info label="TOP Hari" value={quotation.purchase_order.top_hari} />
                                                <Info label="Jatuh Tempo" value={quotation.purchase_order.tgl_jatuh_tempo} />
                                            </>
                                        )}
                                        <div>
                                            <div className="text-xs uppercase text-slate-500">Status</div>
                                            <div className="mt-1"><StatusBadge status={quotation.purchase_order.status} label={quotation.purchase_order.status_label} /></div>
                                        </div>
                                    </div>
                                    {canVoidPO && quotation.purchase_order.is_voidable && (
                                        <Button type="button" variant="destructive" onClick={() => setModal('void-purchase-order')}>
                                            <Ban className="h-4 w-4" />Void PO
                                        </Button>
                                    )}
                                </div>
                                {quotation.purchase_order.alasan_void && (
                                    <div className="rounded-md bg-zinc-100 p-3 text-sm text-zinc-700">Alasan void: {quotation.purchase_order.alasan_void}</div>
                                )}
                            </div>
                        )}
                    </ProcessSection>
                )}

                {quotation.status === 'APPROVED' && quotation.purchase_order && (
                    <>
                        <ProcessSection title="WIP">
                            <div className="mb-4 flex flex-wrap items-center justify-between gap-3">
                                <div className="text-sm font-medium text-slate-700 dark:text-slate-200">WIP - Order ke RMA</div>
                                {canInputWIP && quotation.purchase_order.status === 'OPEN' && (
                                    <Button type="button" variant="secondary" onClick={() => setModal('wip-order')}>
                                        <Plus className="h-4 w-4" />Input WIP
                                    </Button>
                                )}
                            </div>
                            <div className="overflow-x-auto">
                                <table className="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                                    <thead className="bg-slate-50 dark:bg-slate-900">
                                        <tr>
                                            <th className="px-4 py-3 text-left">No. WIP</th>
                                            <th className="px-4 py-3 text-left">Tipe</th>
                                            <th className="px-4 py-3 text-left">Ekspedisi</th>
                                            <th className="px-4 py-3 text-left">Status Supply</th>
                                            <th className="px-4 py-3 text-right">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y divide-slate-100 dark:divide-slate-900">
                                        {quotation.purchase_order.wip_orders.length === 0 && (
                                            <tr>
                                                <td className="px-4 py-6 text-center text-slate-500" colSpan="5">Belum ada WIP.</td>
                                            </tr>
                                        )}
                                        {quotation.purchase_order.wip_orders.map((wip) => (
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
                        <ProcessSection title="SPB">
                            <div className="text-sm text-slate-600 dark:text-slate-300">SPB akan muncul setelah WIP dibuat.</div>
                        </ProcessSection>
                        <ProcessSection title="Tagihan">
                            <div className="text-sm text-slate-600 dark:text-slate-300">Invoice/Nota akan dibuat setelah SPB tersedia.</div>
                        </ProcessSection>
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
            <PurchaseOrderModal
                show={modal === 'purchase-order'}
                form={purchaseOrderForm}
                onClose={() => setModal(null)}
                onSubmit={submitPurchaseOrder}
            />
            <WipOrderModal
                show={modal === 'wip-order'}
                form={wipOrderForm}
                onClose={() => setModal(null)}
                onSubmit={submitWipOrder}
            />
            <ActionModal
                show={modal === 'void-purchase-order'}
                title="Void PO Customer"
                label="Alasan void"
                value={voidPurchaseOrderForm.data.alasan_void}
                error={voidPurchaseOrderForm.errors.alasan_void}
                processing={voidPurchaseOrderForm.processing}
                onChange={(value) => voidPurchaseOrderForm.setData('alasan_void', value)}
                onClose={() => setModal(null)}
                onSubmit={submitVoidPurchaseOrder}
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
        </AppLayout>
    );
}
