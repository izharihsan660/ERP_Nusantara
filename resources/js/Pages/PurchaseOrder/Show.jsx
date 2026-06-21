import Modal from '@/Components/Modal';
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
            <div className="text-xs uppercase text-slate-500">{label}</div>
            <div className="mt-1 font-medium text-slate-900 dark:text-slate-100">{value ?? '-'}</div>
        </div>
    );
}

function ActionModal({ show, title, label, value, error, processing, variant = 'destructive', onChange, onClose, onSubmit }) {
    return (
        <Modal show={show} onClose={onClose} maxWidth="md">
            <form onSubmit={onSubmit} className="p-6">
                <h2 className="text-lg font-semibold text-slate-950 dark:text-white">{title}</h2>
                <InputLabel label={label} required className="mt-4" />
                <Textarea className="mt-2" value={value} onChange={(e) => onChange(e.target.value)} />
                {error && <div className="mt-2 text-sm text-red-600">{error}</div>}
                <div className="mt-6 flex justify-end gap-2">
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
                <h2 className="text-lg font-semibold text-slate-950 dark:text-white">Edit Referensi</h2>
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
                <div className="mt-6 flex justify-end gap-2">
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
        rejectForm.post(route('purchase-orders.reject', purchaseOrder.id), { onSuccess: () => setModal(null) });
    };

    const submitVoid = (event) => {
        event.preventDefault();
        voidForm.post(route('purchase-orders.void', purchaseOrder.id), { onSuccess: () => setModal(null) });
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
                            <Button type="button" disabled={submitForm.processing} onClick={submitToManager}><Send className="h-4 w-4" />Submit ke Manager</Button>
                        )}
                        {purchaseOrder.status === 'PENDING_APPROVAL' && canApprove && (
                            <>
                                <Button type="button" onClick={() => router.post(route('purchase-orders.approve', purchaseOrder.id))}><Check className="h-4 w-4" />Approve</Button>
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
            {(submitForm.errors.status || submitForm.errors.items) && (
                <div className="mb-4 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    {submitForm.errors.status ?? submitForm.errors.items}
                </div>
            )}

            <div className="space-y-6">
                <section className="rounded-lg border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                    <div className="mb-4 flex items-center justify-between gap-3">
                        <h2 className="font-semibold text-slate-950 dark:text-white">Informasi Purchase Order</h2>
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
                    {purchaseOrder.alasan_void && <div className="mt-4 rounded-md bg-zinc-100 p-3 text-sm text-zinc-700">Alasan void: {purchaseOrder.alasan_void}</div>}
                </section>

                <section className="rounded-lg border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-950">
                    <div className="border-b border-slate-200 p-4 dark:border-slate-800">
                        <h2 className="font-semibold text-slate-950 dark:text-white">Item</h2>
                    </div>
                    <div className="overflow-x-auto">
                        <table className="min-w-full table-fixed divide-y divide-slate-200 text-sm dark:divide-slate-800">
                            <thead className="bg-slate-50 dark:bg-slate-900">
                                <tr>
                                    <th className="px-4 py-3 text-left">No</th>
                                    <th className="px-4 py-3 text-left">Deskripsi</th>
                                    <th className="px-4 py-3 text-right">Qty</th>
                                    <th className="px-4 py-3 text-left">Satuan</th>
                                    <th className="px-4 py-3 text-right">Harga</th>
                                    <th className="px-4 py-3 text-right">Jumlah</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-100 dark:divide-slate-900">
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
        </AppLayout>
    );
}
