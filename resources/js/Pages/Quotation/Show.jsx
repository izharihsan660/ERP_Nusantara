import Modal from '@/Components/Modal';
import PageHeader from '@/Components/PageHeader';
import { Button } from '@/Components/ui/button';
import { Textarea } from '@/Components/ui/textarea';
import AppLayout from '@/Layouts/AppLayout';
import { Head, Link, router, useForm, usePage } from '@inertiajs/react';
import { Ban, Check, Copy, Download, Send, X } from 'lucide-react';
import { useState } from 'react';

const statusStyles = {
    DRAFT: 'bg-slate-100 text-slate-700 ring-slate-200',
    PENDING_APPROVAL: 'bg-amber-50 text-amber-700 ring-amber-200',
    APPROVED: 'bg-emerald-50 text-emerald-700 ring-emerald-200',
    REJECTED: 'bg-red-50 text-red-700 ring-red-200',
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
    const rejectForm = useForm({ catatan_rejection: '' });
    const voidForm = useForm({ alasan_void: '' });

    const submitReject = (event) => {
        event.preventDefault();
        rejectForm.post(route('quotations.reject', quotation.id), { onSuccess: () => setModal(null) });
    };

    const submitVoid = (event) => {
        event.preventDefault();
        voidForm.post(route('quotations.void', quotation.id), { onSuccess: () => setModal(null) });
    };

    const canApprove = permissions.includes('Quotation approve');
    const canVoid = permissions.includes('Quotation void') && quotation.status !== 'VOID';
    const canCreate = permissions.includes('Quotation buat');
    const canDownload = permissions.includes('Quotation download_pdf');

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
                        <div className="flex flex-wrap items-center justify-between gap-3 rounded-md bg-slate-50 p-4 dark:bg-slate-900">
                            <div className="text-sm text-slate-600 dark:text-slate-300">Belum ada PO Customer untuk quotation ini.</div>
                            <Button type="button" variant="secondary">Input PO Customer</Button>
                        </div>
                    </ProcessSection>
                )}

                {quotation.status === 'APPROVED' && (
                    <>
                        <ProcessSection title="WIP">
                            <div className="flex flex-wrap items-center justify-between gap-3 rounded-md bg-slate-50 p-4 dark:bg-slate-900">
                                <div className="text-sm text-slate-600 dark:text-slate-300">WIP akan tersedia setelah PO Customer diinput.</div>
                                <Button type="button" variant="secondary">Input WIP</Button>
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
        </AppLayout>
    );
}
