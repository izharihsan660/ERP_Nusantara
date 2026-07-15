import Modal from '@/Components/Modal';
import ConfirmDialog from '@/Components/ConfirmDialog';
import FormErrorSummary from '@/Components/FormErrorSummary';
import InputLabel from '@/Components/Form/InputLabel';
import PageHeader from '@/Components/PageHeader';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Textarea } from '@/Components/ui/textarea';
import AppLayout from '@/Layouts/AppLayout';
import InvoiceSection from '@/Pages/Shared/InvoiceSection';
import SpbSection from '@/Pages/Shared/SpbSection';
import { formatRupiah } from '@/utils/currency';
import { Head, Link, router, useForm, usePage } from '@inertiajs/react';
import { Ban, Check, Download, Pencil, Send, X } from 'lucide-react';
import { useState } from 'react';

const statusStyles = {
    DRAFT: 'bg-slate-100 text-slate-700 ring-slate-200',
    PENDING_APPROVAL: 'bg-amber-50 text-amber-700 ring-amber-200',
    APPROVED: 'bg-emerald-50 text-emerald-700 ring-emerald-200',
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

function ActionModal({ show, title, label, value, error, errors, errorKey, processing, variant = 'destructive', onChange, onClose, onSubmit }) {
    return (
        <Modal show={show} onClose={onClose} maxWidth="md">
            <form onSubmit={onSubmit} className="p-6">
                <h2 className="text-lg font-semibold text-[hsl(var(--foreground))]">{title}</h2>
                <FormErrorSummary errors={errors} renderedKeys={[errorKey]} />
                <InputLabel label={label} required className="mt-4" />
                <Textarea className="mt-2" value={value} onChange={(e) => onChange(e.target.value)} />
                {error && <div className="mt-2 text-sm text-red-600">{error}</div>}
                <div className="mt-6 flex flex-col justify-end gap-2 sm:flex-row">
                    <Button type="button" variant="outline" onClick={onClose} disabled={processing}>Batal</Button>
                    <Button type="submit" variant={variant} disabled={processing}>Proses</Button>
                </div>
            </form>
        </Modal>
    );
}

function ReferensiModal({ show, form, onClose, onSubmit }) {
    return (
        <Modal show={show} onClose={onClose} maxWidth="md">
            <form onSubmit={onSubmit} className="p-6">
                <h2 className="text-lg font-semibold text-[hsl(var(--foreground))]">Edit Referensi</h2>
                <FormErrorSummary errors={form.errors} renderedKeys={['no_pr_customer', 'no_po_customer']} />
                <div className="mt-4 space-y-4">
                    <div>
                        <InputLabel label="No. PR Customer" />
                        <Input className="mt-2" value={form.data.no_pr_customer} onChange={(e) => form.setData('no_pr_customer', e.target.value)} />
                        {form.errors.no_pr_customer && <div className="mt-2 text-sm text-red-600">{form.errors.no_pr_customer}</div>}
                    </div>
                    <div>
                        <InputLabel label="No. PO Customer" />
                        <Input className="mt-2" value={form.data.no_po_customer} onChange={(e) => form.setData('no_po_customer', e.target.value)} />
                        {form.errors.no_po_customer && <div className="mt-2 text-sm text-red-600">{form.errors.no_po_customer}</div>}
                    </div>
                    <div className="rounded-md bg-sky-50 p-3 text-sm text-sky-700 dark:bg-sky-950 dark:text-sky-300">
                        Setelah No. PO Customer diisi, referensi di SPB terkait akan otomatis diperbarui dari PR ke PO
                    </div>
                </div>
                <div className="mt-6 flex flex-col justify-end gap-2 sm:flex-row">
                    <Button type="button" variant="outline" onClick={onClose} disabled={form.processing}>Batal</Button>
                    <Button type="submit" disabled={form.processing}>Simpan</Button>
                </div>
            </form>
        </Modal>
    );
}

export default function Show({ purchaseOrder, sites }) {
    const permissions = usePage().props.auth.user.permissions ?? [];
    const [modal, setModal] = useState(null);
    const [confirm, setConfirm] = useState({ isOpen: false, title: '', message: '', variant: 'danger', onConfirm: () => {} });
    const submitForm = useForm({});
    const rejectForm = useForm({ catatan: '' });
    const voidForm = useForm({ alasan_void: '' });
    const referensiForm = useForm({
        no_pr_customer: purchaseOrder.no_pr_customer ?? '',
        no_po_customer: purchaseOrder.no_po_customer ?? '',
    });

    const canCreate = permissions.includes('buat_purchase_order');
    const canApprove = permissions.includes('approve_purchase_order');
    const canDownload = permissions.includes('download_pdf_purchase_order');
    const canVoid = permissions.includes('void_purchase_order') && purchaseOrder.status !== 'VOID';
    const spbSourceOptions = purchaseOrder.status === 'APPROVED'
        ? [{
            id: purchaseOrder.id,
            label: purchaseOrder.no_purchase_order,
            route: 'purchase-orders.spb.store',
            source_items: purchaseOrder.source_items ?? [],
        }]
        : [];

    const submitReject = (event) => {
        event.preventDefault();
        setConfirm({
            isOpen: true,
            title: 'Tolak Purchase Order',
            message: 'Purchase Order akan dikembalikan ke status Draft. Lanjutkan?',
            variant: 'warning',
            confirmLabel: 'Ya, Tolak',
            onConfirm: () => rejectForm.post(route('purchase-orders.reject', purchaseOrder.id), { onSuccess: () => setModal(null) }),
        });
    };

    const submitVoid = (event) => {
        event.preventDefault();
        setConfirm({
            isOpen: true,
            title: 'Void Purchase Order',
            message: 'Purchase Order akan di-void permanen. Lanjutkan?',
            variant: 'danger',
            confirmLabel: 'Ya, Void',
            onConfirm: () => voidForm.post(route('purchase-orders.void', purchaseOrder.id), { onSuccess: () => setModal(null) }),
        });
    };

    const submitToManager = () => {
        submitForm.post(route('purchase-orders.submit', purchaseOrder.id), {
            preserveScroll: true,
        });
    };

    const openReferensiModal = () => {
        referensiForm.setData({
            no_pr_customer: purchaseOrder.no_pr_customer ?? '',
            no_po_customer: purchaseOrder.no_po_customer ?? '',
        });
        referensiForm.clearErrors();
        setModal('referensi');
    };

    const submitReferensi = (event) => {
        event.preventDefault();
        referensiForm.patch(route('purchase-orders.referensi.update', purchaseOrder.id), {
            preserveScroll: true,
            onSuccess: () => setModal(null),
        });
    };

    return (
        <AppLayout title="Detail Purchase Order">
            <Head title={purchaseOrder.no_purchase_order} />
            <PageHeader
                title={purchaseOrder.no_purchase_order}
                description="Detail purchase order NAJ ke vendor dan proses turunannya."
                actions={(
                    <>
                        <Button asChild variant="outline"><Link href={route('purchase-orders.index')}>Kembali</Link></Button>
                        {purchaseOrder.status === 'DRAFT' && canCreate && (
                            <Button type="button" disabled={submitForm.processing} onClick={() => setConfirm({ isOpen: true, title: 'Submit Purchase Order', message: 'Purchase Order akan dikirim ke Manager untuk disetujui. Lanjutkan?', variant: 'warning', confirmLabel: 'Ya, Submit', onConfirm: submitToManager })}><Send className="h-4 w-4" />Submit ke Manager</Button>
                        )}
                        {purchaseOrder.status === 'PENDING_APPROVAL' && canApprove && (
                            <>
                                <Button type="button" onClick={() => setConfirm({ isOpen: true, title: 'Approve Purchase Order', message: 'Kamu akan menyetujui Purchase Order ini. PDF dan QR Code akan di-generate. Lanjutkan?', variant: 'success', confirmLabel: 'Ya, Approve', onConfirm: () => router.post(route('purchase-orders.approve', purchaseOrder.id)) })}><Check className="h-4 w-4" />Approve</Button>
                                <Button type="button" variant="destructive" onClick={() => setModal('reject')}><X className="h-4 w-4" />Reject</Button>
                            </>
                        )}
                        {purchaseOrder.status === 'APPROVED' && canDownload && (
                            <Button asChild variant="secondary"><a href={route('purchase-orders.download', purchaseOrder.id)}><Download className="h-4 w-4" />Download PDF</a></Button>
                        )}
                        {canVoid && (
                            <Button type="button" variant="destructive" onClick={() => setModal('void')}><Ban className="h-4 w-4" />Void</Button>
                        )}
                    </>
                )}
            />
            <FormErrorSummary errors={submitForm.errors} className="mb-4 mt-0" />

            <div className="space-y-6">
                <section className="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] p-6 shadow-sm">
                    <div className="mb-4 flex items-center justify-between gap-3">
                        <h2 className="font-semibold text-[hsl(var(--foreground))]">Informasi Purchase Order</h2>
                        <StatusBadge status={purchaseOrder.status} label={purchaseOrder.status_label} />
                    </div>
                    <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                        <Info label="Customer" value={purchaseOrder.customer?.nama_customer} />
                        <Info label="Vendor" value={purchaseOrder.vendor?.nama_vendor} />
                        <Info label="Tanggal" value={purchaseOrder.tgl_po} />
                        <Info label="Dibuat oleh" value={purchaseOrder.created_by?.name} />
                        <Info label="No. PR Customer" value={purchaseOrder.no_pr_customer} />
                        <div>
                            <div className="flex items-center gap-2">
                                <Info label="No. PO Customer" value={purchaseOrder.no_po_customer} />
                                {purchaseOrder.status === 'APPROVED' && (
                                    <Button type="button" size="sm" variant="outline" onClick={openReferensiModal}>
                                        <Pencil className="h-4 w-4" />Edit Referensi
                                    </Button>
                                )}
                            </div>
                        </div>
                        <Info label="Diapprove oleh" value={purchaseOrder.approved_by?.name} />
                        <Info label="Tanggal approve" value={purchaseOrder.approved_at} />
                        <Info label="Voided oleh" value={purchaseOrder.voided_by?.name} />
                    </div>
                    {purchaseOrder.catatan && <div className="mt-4 rounded-md bg-slate-50 p-3 text-sm text-slate-700 dark:bg-slate-900 dark:text-slate-300">Catatan: {purchaseOrder.catatan}</div>}
                    {purchaseOrder.catatan_rejection && <div className="mt-4 rounded-md bg-red-50 p-3 text-sm text-red-700 dark:bg-red-950/30 dark:text-red-300">Catatan rejection: {purchaseOrder.catatan_rejection}</div>}
                    {purchaseOrder.alasan_void && <div className="mt-4 rounded-md bg-zinc-100 p-3 text-sm text-zinc-700">Alasan void: {purchaseOrder.alasan_void}</div>}
                </section>

                <section className="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] shadow-sm">
                    <div className="border-b border-[hsl(var(--border))] p-6">
                        <h2 className="font-semibold text-[hsl(var(--foreground))]">Item</h2>
                    </div>
                    <div className="overflow-x-auto">
                        <table className="min-w-full table-fixed divide-y divide-[hsl(var(--border))] text-sm">
                            <thead className="bg-[hsl(var(--muted))]/60">
                                <tr>
                                    <th className="px-4 py-3 text-left">No</th>
                                    <th className="px-4 py-3 text-left">Deskripsi</th>
                                    <th className="px-4 py-3 text-right">Qty</th>
                                    <th className="px-4 py-3 text-left">Satuan</th>
                                    <th className="px-4 py-3 text-right">Harga</th>
                                    <th className="px-4 py-3 text-right">Jumlah</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-[hsl(var(--border))]">
                                {purchaseOrder.items.map((item, index) => (
                                    <tr key={item.id}>
                                        <td className="whitespace-nowrap px-4 py-3">{index + 1}</td>
                                        <td className="px-4 py-3">{item.deskripsi}</td>
                                        <td className="px-4 py-3 text-right">{item.qty}</td>
                                        <td className="px-4 py-3">{item.satuan}</td>
                                        <td className="px-4 py-3 text-right">{formatRupiah(item.harga_satuan)}</td>
                                        <td className="px-4 py-3 text-right">{formatRupiah(item.jumlah)}</td>
                                    </tr>
                                ))}
                            </tbody>
                            <tfoot className="bg-slate-50 font-semibold dark:bg-slate-900">
                                <tr>
                                    <td className="px-4 py-3" colSpan="5">Total</td>
                                    <td className="px-4 py-3 text-right">{formatRupiah(purchaseOrder.total)}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </section>

                <SpbSection
                    spbList={purchaseOrder.spb}
                    sourceOptions={spbSourceOptions}
                    customer={purchaseOrder.customer}
                    sites={sites}
                />
                <InvoiceSection spbList={purchaseOrder.spb} />
            </div>

            <ActionModal
                show={modal === 'reject'}
                title="Reject Purchase Order"
                label="Catatan reject"
                value={rejectForm.data.catatan}
                error={rejectForm.errors.catatan}
                errors={rejectForm.errors}
                errorKey="catatan"
                processing={rejectForm.processing}
                variant="destructive"
                onChange={(value) => rejectForm.setData('catatan', value)}
                onClose={() => setModal(null)}
                onSubmit={submitReject}
            />
            <ActionModal
                show={modal === 'void'}
                title="Void Purchase Order"
                label="Alasan void"
                value={voidForm.data.alasan_void}
                error={voidForm.errors.alasan_void}
                errors={voidForm.errors}
                errorKey="alasan_void"
                processing={voidForm.processing}
                onChange={(value) => voidForm.setData('alasan_void', value)}
                onClose={() => setModal(null)}
                onSubmit={submitVoid}
            />
            <ReferensiModal
                show={modal === 'referensi'}
                form={referensiForm}
                onClose={() => setModal(null)}
                onSubmit={submitReferensi}
            />
            <ConfirmDialog
                {...confirm}
                onCancel={() => setConfirm((prev) => ({ ...prev, isOpen: false }))}
            />
        </AppLayout>
    );
}
