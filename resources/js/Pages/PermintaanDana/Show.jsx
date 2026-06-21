import InputError from '@/Components/InputError';
import InputLabel from '@/Components/Form/InputLabel';
import Modal from '@/Components/Modal';
import ConfirmDialog from '@/Components/ConfirmDialog';
import PageHeader from '@/Components/PageHeader';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Select } from '@/Components/ui/select';
import { Textarea } from '@/Components/ui/textarea';
import AppLayout from '@/Layouts/AppLayout';
import { formatRupiah, formatRupiahInput, parseRupiah } from '@/utils/currency';
import { Head, Link, router, useForm, usePage } from '@inertiajs/react';
import { Ban, Check, Download, Plus, Send, Trash2, Upload, X } from 'lucide-react';
import { useState } from 'react';

const statusStyles = {
    DRAFT: 'bg-slate-100 text-slate-700 ring-slate-200',
    PENDING_APPROVAL: 'bg-amber-50 text-amber-700 ring-amber-200',
    APPROVED: 'bg-sky-50 text-sky-700 ring-sky-200',
    REJECTED: 'bg-red-50 text-red-700 ring-red-200',
    PAID: 'bg-emerald-50 text-emerald-700 ring-emerald-200',
    VOID: 'bg-zinc-800 text-white ring-zinc-800',
};

function StatusBadge({ status, label }) {
    return (
        <span className={`inline-flex rounded-full px-2.5 py-1 text-xs font-medium ring-1 ring-inset ${statusStyles[status] ?? statusStyles.DRAFT}`}>
            {label ?? status}
        </span>
    );
}

function today() {
    return new Date().toISOString().slice(0, 10);
}

function Info({ label, value }) {
    return (
        <div>
            <div className="text-xs uppercase text-slate-500">{label}</div>
            <div className="mt-1 font-medium text-slate-900 dark:text-slate-100">{value ?? '-'}</div>
        </div>
    );
}

function TextActionModal({ show, title, label, value, error, processing, submitLabel, variant = 'destructive', onChange, onClose, onSubmit }) {
    return (
        <Modal show={show} onClose={onClose} maxWidth="md">
            <form onSubmit={onSubmit} className="p-6">
                <h2 className="text-lg font-semibold text-slate-950 dark:text-white">{title}</h2>
                <InputLabel label={label} required className="mt-4" />
                <Textarea className="mt-2" value={value} onChange={(e) => onChange(e.target.value)} />
                <InputError message={error} className="mt-2" />
                <div className="mt-6 flex flex-col justify-end gap-2 sm:flex-row">
                    <Button type="button" variant="outline" onClick={onClose} disabled={processing}>Batal</Button>
                    <Button type="submit" variant={variant} disabled={processing}>{submitLabel}</Button>
                </div>
            </form>
        </Modal>
    );
}

function UploadBuktiModal({ show, form, documentCategories, onClose, onSubmit }) {
    const defaultCategory = documentCategories[0]?.value ?? 'BUKTI_PEMBELIAN';

    const updateDocument = (index, field, value) => {
        const documents = [...form.data.documents];
        documents[index] = { ...documents[index], [field]: value };
        form.setData('documents', documents);
    };

    const addDocument = () => {
        if (form.data.documents.length >= 3) {
            return;
        }

        form.setData('documents', [...form.data.documents, { kategori: defaultCategory, file: null }]);
    };

    const removeDocument = (index) => {
        if (form.data.documents.length === 1) {
            return;
        }

        form.setData('documents', form.data.documents.filter((_, documentIndex) => documentIndex !== index));
    };

    return (
        <Modal show={show} onClose={onClose} maxWidth="lg">
            <form onSubmit={onSubmit} className="p-6">
                <h2 className="text-lg font-semibold text-slate-950 dark:text-white">Upload Bukti</h2>
                <div className="mt-4 space-y-4">
                    <div>
                        <InputLabel label="Tanggal Realisasi" required />
                        <Input className="mt-1" type="date" value={form.data.tgl_realisasi} onChange={(e) => form.setData('tgl_realisasi', e.target.value)} />
                        <InputError message={form.errors.tgl_realisasi} className="mt-2" />
                    </div>
                    <div>
                        <InputLabel label="Jumlah Realisasi" required />
                        <Input className="mt-1" inputMode="numeric" value={formatRupiahInput(form.data.jumlah_realisasi)} onChange={(e) => form.setData('jumlah_realisasi', parseRupiah(e.target.value))} />
                        <div className="mt-1 text-xs text-slate-500">{formatRupiah(form.data.jumlah_realisasi)}</div>
                        <InputError message={form.errors.jumlah_realisasi} className="mt-2" />
                    </div>
                    <div className="space-y-3">
                        <div className="flex items-center justify-between gap-3">
                            <InputLabel label="Dokumen Bukti" required />
                            <Button type="button" size="sm" variant="secondary" onClick={addDocument} disabled={form.data.documents.length >= 3}>
                                <Plus className="h-4 w-4" />Tambah Dokumen
                            </Button>
                        </div>
                        <InputError message={form.errors.documents} className="mt-2" />
                        {form.data.documents.map((document, index) => (
                            <div key={index} className="grid gap-3 rounded-md border border-slate-200 p-3 dark:border-slate-800 md:grid-cols-[180px_1fr_auto]">
                                <div>
                                    <InputLabel label="Kategori" required />
                                    <Select className="mt-1" value={document.kategori} onChange={(e) => updateDocument(index, 'kategori', e.target.value)}>
                                        {documentCategories.map((option) => <option key={option.value} value={option.value}>{option.label}</option>)}
                                    </Select>
                                    <InputError message={form.errors[`documents.${index}.kategori`]} className="mt-2" />
                                </div>
                                <div>
                                    <InputLabel label="File" required />
                                    <Input className="mt-1" type="file" accept=".pdf,.jpg,.png" onChange={(e) => updateDocument(index, 'file', e.target.files[0])} />
                                    <InputError message={form.errors[`documents.${index}.file`]} className="mt-2" />
                                </div>
                                <div className="flex items-end">
                                    <Button type="button" size="icon" variant="ghost" onClick={() => removeDocument(index)} disabled={form.data.documents.length === 1}>
                                        <Trash2 className="h-4 w-4" />
                                    </Button>
                                </div>
                            </div>
                        ))}
                    </div>
                </div>
                <div className="mt-6 flex flex-col justify-end gap-2 sm:flex-row">
                    <Button type="button" variant="outline" onClick={onClose} disabled={form.processing}>Batal</Button>
                    <Button type="submit" disabled={form.processing}>Upload & Selesai</Button>
                </div>
            </form>
        </Modal>
    );
}

export default function Show({ permintaanDana, documentCategories = [] }) {
    const permissions = usePage().props.auth.user.permissions ?? [];
    const [modal, setModal] = useState(null);
    const [confirm, setConfirm] = useState({ isOpen: false, title: '', message: '', variant: 'danger', onConfirm: () => {} });
    const defaultDocumentCategory = documentCategories[0]?.value ?? 'BUKTI_PEMBELIAN';
    const rejectForm = useForm({ catatan_rejection: '' });
    const voidForm = useForm({ alasan_void: '' });
    const uploadForm = useForm({
        tgl_realisasi: today(),
        jumlah_realisasi: permintaanDana.nominal ?? '',
        documents: [{ kategori: defaultDocumentCategory, file: null }],
    });

    const canCreate = permissions.includes('buat_pd');
    const canApprove = permissions.includes('approve_pd');
    const canUploadBukti = permissions.includes('upload_bukti_pd');
    const canVoid = permissions.includes('void_pd') && permintaanDana.is_voidable;

    const submitReject = (event) => {
        event.preventDefault();
        setConfirm({
            isOpen: true,
            title: 'Tolak Permintaan Dana',
            message: 'Permintaan Dana akan ditolak dan dikembalikan ke pembuat. Lanjutkan?',
            variant: 'warning',
            confirmLabel: 'Ya, Tolak',
            onConfirm: () => rejectForm.post(route('permintaan-dana.reject', permintaanDana.id), { onSuccess: () => setModal(null) }),
        });
    };

    const submitVoid = (event) => {
        event.preventDefault();
        setConfirm({
            isOpen: true,
            title: 'Void Permintaan Dana',
            message: 'Permintaan Dana akan di-void permanen. Lanjutkan?',
            variant: 'danger',
            confirmLabel: 'Ya, Void',
            onConfirm: () => voidForm.post(route('permintaan-dana.void', permintaanDana.id), { onSuccess: () => setModal(null) }),
        });
    };

    const openUploadBukti = () => {
        uploadForm.setData({
            tgl_realisasi: today(),
            jumlah_realisasi: permintaanDana.nominal ?? '',
            documents: [{ kategori: defaultDocumentCategory, file: null }],
        });
        setModal('upload-bukti');
    };

    const submitUploadBukti = (event) => {
        event.preventDefault();
        uploadForm
            .transform((payload) => ({
                ...payload,
                jumlah_realisasi: parseRupiah(payload.jumlah_realisasi),
            }))
            .post(route('permintaan-dana.upload-bukti', permintaanDana.id), {
                forceFormData: true,
                onSuccess: () => {
                    uploadForm.reset();
                    setModal(null);
                },
            });
    };

    return (
        <AppLayout title="Detail Permintaan Dana">
            <Head title={permintaanDana.no_pd} />
            <PageHeader
                title={permintaanDana.no_pd}
                description="Detail permintaan dana, approval Manager, dan bukti realisasi."
                actions={(
                    <>
                        <Button asChild variant="outline"><Link href={route('permintaan-dana.index')}>Kembali</Link></Button>
                        {permintaanDana.status === 'DRAFT' && canCreate && (
                            <Button type="button" onClick={() => setConfirm({ isOpen: true, title: 'Submit Permintaan Dana', message: 'Permintaan Dana akan dikirim ke Manager untuk disetujui. Lanjutkan?', variant: 'warning', confirmLabel: 'Ya, Submit', onConfirm: () => router.post(route('permintaan-dana.submit', permintaanDana.id)) })}><Send className="h-4 w-4" />Submit ke Manager</Button>
                        )}
                        {permintaanDana.status === 'PENDING_APPROVAL' && canApprove && (
                            <>
                                <Button type="button" onClick={() => setConfirm({ isOpen: true, title: 'Approve Permintaan Dana', message: 'Kamu akan menyetujui Permintaan Dana ini. Dana bisa dicairkan setelah ini. Lanjutkan?', variant: 'success', confirmLabel: 'Ya, Approve', onConfirm: () => router.post(route('permintaan-dana.approve', permintaanDana.id)) })}><Check className="h-4 w-4" />Approve</Button>
                                <Button type="button" variant="destructive" onClick={() => setModal('reject')}><X className="h-4 w-4" />Reject</Button>
                            </>
                        )}
                        {['APPROVED', 'PAID'].includes(permintaanDana.status) && (
                            <Button asChild variant="secondary"><a href={route('permintaan-dana.download', permintaanDana.id)}><Download className="h-4 w-4" />Download PDF</a></Button>
                        )}
                        {permintaanDana.status === 'APPROVED' && canUploadBukti && (
                            <Button type="button" onClick={openUploadBukti}><Upload className="h-4 w-4" />Upload Bukti</Button>
                        )}
                        {canVoid && (
                            <Button type="button" variant="destructive" onClick={() => setModal('void')}><Ban className="h-4 w-4" />Void</Button>
                        )}
                    </>
                )}
            />

            <section className="rounded-lg border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                <div className="mb-4 flex items-center justify-between gap-3">
                    <h2 className="font-semibold text-slate-950 dark:text-white">Informasi Permintaan Dana</h2>
                    <StatusBadge status={permintaanDana.status} label={permintaanDana.status_label} />
                </div>
                <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                    <Info label="Tujuan" value={permintaanDana.tujuan} />
                    <Info label="Rekening Tujuan" value={permintaanDana.rekening_tujuan} />
                    <Info label="Bank Tujuan" value={permintaanDana.bank_tujuan} />
                    <Info label="Plan Pembayaran" value={permintaanDana.plan_pembayaran} />
                    <Info label="Nominal" value={formatRupiah(permintaanDana.nominal)} />
                    <Info label="Referensi" value={permintaanDana.referensi_dokumen} />
                    <Info label="Dibuat oleh" value={permintaanDana.created_by?.name} />
                    <Info label="Tanggal submit" value={permintaanDana.submitted_at} />
                    <Info label="Diapprove oleh" value={permintaanDana.approved_by?.name} />
                    <Info label="Tanggal approve" value={permintaanDana.approved_at} />
                    {permintaanDana.status === 'PAID' && (
                        <>
                            <Info label="Tanggal realisasi" value={permintaanDana.tgl_realisasi} />
                            <Info label="Jumlah realisasi" value={formatRupiah(permintaanDana.jumlah_realisasi)} />
                        </>
                    )}
                    {permintaanDana.voided_by && <Info label="Voided oleh" value={permintaanDana.voided_by?.name} />}
                </div>
                <div className="mt-4 rounded-md bg-slate-50 p-3 text-sm text-slate-700 dark:bg-slate-900 dark:text-slate-300">
                    <div className="font-medium text-slate-950 dark:text-white">Keterangan</div>
                    <div className="mt-1 whitespace-pre-line">{permintaanDana.keterangan}</div>
                </div>
                {permintaanDana.catatan_rejection && <div className="mt-4 rounded-md bg-red-50 p-3 text-sm text-red-700">Catatan rejection: {permintaanDana.catatan_rejection}</div>}
                {permintaanDana.alasan_void && <div className="mt-4 rounded-md bg-zinc-100 p-3 text-sm text-zinc-700">Alasan void: {permintaanDana.alasan_void}</div>}
            </section>

            <section className="mt-6 rounded-lg border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-950">
                <div className="border-b border-slate-200 p-4 dark:border-slate-800">
                    <h2 className="font-semibold text-slate-950 dark:text-white">Dokumen Bukti</h2>
                </div>
                <div className="overflow-x-auto">
                    <table className="min-w-full table-fixed divide-y divide-slate-200 text-sm dark:divide-slate-800">
                        <thead className="bg-slate-50 dark:bg-slate-900">
                            <tr>
                                <th className="px-4 py-3 text-left">Kategori</th>
                                <th className="px-4 py-3 text-left">Nama File</th>
                                <th className="px-4 py-3 text-left">Tanggal</th>
                                <th className="px-4 py-3 text-right">Download</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-slate-100 dark:divide-slate-900">
                            {permintaanDana.documents.length === 0 && (
                                <tr>
                                    <td className="px-4 py-6 text-center text-slate-500" colSpan="4">Belum ada dokumen bukti.</td>
                                </tr>
                            )}
                            {permintaanDana.documents.map((document) => (
                                <tr key={document.id}>
                                    <td className="px-4 py-3">{document.kategori_label}</td>
                                    <td className="px-4 py-3">{document.nama_file}</td>
                                    <td className="px-4 py-3">{document.created_at}</td>
                                    <td className="px-4 py-3 text-right">
                                        <Button asChild size="sm" variant="secondary">
                                            <a href={route('permintaan-dana.documents.download', document.id)}><Download className="h-4 w-4" />Download</a>
                                        </Button>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            </section>

            <TextActionModal
                show={modal === 'reject'}
                title="Reject Permintaan Dana"
                label="Catatan rejection"
                value={rejectForm.data.catatan_rejection}
                error={rejectForm.errors.catatan_rejection}
                processing={rejectForm.processing}
                submitLabel="Reject"
                onChange={(value) => rejectForm.setData('catatan_rejection', value)}
                onClose={() => setModal(null)}
                onSubmit={submitReject}
            />
            <UploadBuktiModal
                show={modal === 'upload-bukti'}
                form={uploadForm}
                documentCategories={documentCategories}
                onClose={() => setModal(null)}
                onSubmit={submitUploadBukti}
            />
            <TextActionModal
                show={modal === 'void'}
                title="Void Permintaan Dana"
                label="Alasan void"
                value={voidForm.data.alasan_void}
                error={voidForm.errors.alasan_void}
                processing={voidForm.processing}
                submitLabel="Void"
                onChange={(value) => voidForm.setData('alasan_void', value)}
                onClose={() => setModal(null)}
                onSubmit={submitVoid}
            />
            <ConfirmDialog
                {...confirm}
                onCancel={() => setConfirm((prev) => ({ ...prev, isOpen: false }))}
            />
        </AppLayout>
    );
}
