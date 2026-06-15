import InputError from '@/Components/InputError';
import Modal from '@/Components/Modal';
import PageHeader from '@/Components/PageHeader';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Textarea } from '@/Components/ui/textarea';
import AppLayout from '@/Layouts/AppLayout';
import { Head, Link, router, useForm, usePage } from '@inertiajs/react';
import { Ban, Check, Download, Send, Upload, X } from 'lucide-react';
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

function rupiah(value) {
    return `Rp ${Number(value ?? 0).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
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
                <Label className="mt-4 block">{label}</Label>
                <Textarea className="mt-2" value={value} onChange={(e) => onChange(e.target.value)} />
                <InputError message={error} className="mt-2" />
                <div className="mt-6 flex justify-end gap-2">
                    <Button type="button" variant="outline" onClick={onClose} disabled={processing}>Batal</Button>
                    <Button type="submit" variant={variant} disabled={processing}>{submitLabel}</Button>
                </div>
            </form>
        </Modal>
    );
}

function UploadBuktiModal({ show, form, onClose, onSubmit }) {
    return (
        <Modal show={show} onClose={onClose} maxWidth="md">
            <form onSubmit={onSubmit} className="p-6">
                <h2 className="text-lg font-semibold text-slate-950 dark:text-white">Upload Bukti</h2>
                <div className="mt-4 space-y-4">
                    <div>
                        <Label>Tanggal Realisasi</Label>
                        <Input className="mt-1" type="date" value={form.data.tgl_realisasi} onChange={(e) => form.setData('tgl_realisasi', e.target.value)} />
                        <InputError message={form.errors.tgl_realisasi} className="mt-2" />
                    </div>
                    <div>
                        <Label>Jumlah aktual yang dicairkan</Label>
                        <Input className="mt-1" type="number" min="0" step="0.01" value={form.data.jumlah_realisasi} onChange={(e) => form.setData('jumlah_realisasi', e.target.value)} />
                        <div className="mt-1 text-xs text-slate-500">{rupiah(form.data.jumlah_realisasi)}</div>
                        <InputError message={form.errors.jumlah_realisasi} className="mt-2" />
                    </div>
                    <div>
                        <Label>Bukti transfer / kwitansi</Label>
                        <Input className="mt-1" type="file" accept=".pdf,.jpg,.png" onChange={(e) => form.setData('file_bukti', e.target.files[0])} />
                        <InputError message={form.errors.file_bukti} className="mt-2" />
                    </div>
                </div>
                <div className="mt-6 flex justify-end gap-2">
                    <Button type="button" variant="outline" onClick={onClose} disabled={form.processing}>Batal</Button>
                    <Button type="submit" disabled={form.processing}>Upload & Selesai</Button>
                </div>
            </form>
        </Modal>
    );
}

export default function Show({ permintaanDana }) {
    const permissions = usePage().props.auth.user.permissions ?? [];
    const [modal, setModal] = useState(null);
    const rejectForm = useForm({ catatan_rejection: '' });
    const voidForm = useForm({ alasan_void: '' });
    const uploadForm = useForm({
        tgl_realisasi: today(),
        jumlah_realisasi: permintaanDana.nominal ?? '',
        file_bukti: null,
    });

    const canCreate = permissions.includes('buat_pd');
    const canApprove = permissions.includes('approve_pd');
    const canUploadBukti = permissions.includes('upload_bukti_pd');
    const canVoid = permissions.includes('void_pd') && permintaanDana.is_voidable;

    const submitReject = (event) => {
        event.preventDefault();
        rejectForm.post(route('permintaan-dana.reject', permintaanDana.id), { onSuccess: () => setModal(null) });
    };

    const submitVoid = (event) => {
        event.preventDefault();
        voidForm.post(route('permintaan-dana.void', permintaanDana.id), { onSuccess: () => setModal(null) });
    };

    const submitUploadBukti = (event) => {
        event.preventDefault();
        uploadForm.post(route('permintaan-dana.upload-bukti', permintaanDana.id), {
            forceFormData: true,
            onSuccess: () => setModal(null),
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
                            <Button type="button" onClick={() => router.post(route('permintaan-dana.submit', permintaanDana.id))}><Send className="h-4 w-4" />Submit ke Manager</Button>
                        )}
                        {permintaanDana.status === 'PENDING_APPROVAL' && canApprove && (
                            <>
                                <Button type="button" onClick={() => router.post(route('permintaan-dana.approve', permintaanDana.id))}><Check className="h-4 w-4" />Approve</Button>
                                <Button type="button" variant="destructive" onClick={() => setModal('reject')}><X className="h-4 w-4" />Reject</Button>
                            </>
                        )}
                        {['APPROVED', 'PAID'].includes(permintaanDana.status) && (
                            <Button asChild variant="secondary"><a href={route('permintaan-dana.download', permintaanDana.id)}><Download className="h-4 w-4" />Download PDF</a></Button>
                        )}
                        {permintaanDana.status === 'APPROVED' && canUploadBukti && (
                            <Button type="button" onClick={() => setModal('upload-bukti')}><Upload className="h-4 w-4" />Upload Bukti</Button>
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
                    <Info label="Tanggal PD" value={permintaanDana.tgl_pd} />
                    <Info label="Kategori" value={permintaanDana.kategori_label} />
                    <Info label="Nominal" value={rupiah(permintaanDana.nominal)} />
                    <Info label="Referensi" value={permintaanDana.referensi_dokumen} />
                    <Info label="Dibuat oleh" value={permintaanDana.created_by?.name} />
                    <Info label="Tanggal submit" value={permintaanDana.submitted_at} />
                    <Info label="Diapprove oleh" value={permintaanDana.approved_by?.name} />
                    <Info label="Tanggal approve" value={permintaanDana.approved_at} />
                    {permintaanDana.status === 'PAID' && (
                        <>
                            <Info label="Tanggal realisasi" value={permintaanDana.tgl_realisasi} />
                            <Info label="Jumlah realisasi" value={rupiah(permintaanDana.jumlah_realisasi)} />
                            <Info
                                label="File bukti"
                                value={(
                                    <a className="text-sky-700 underline underline-offset-2" href={`${route('permintaan-dana.download', permintaanDana.id)}?type=bukti`}>
                                        {permintaanDana.file_bukti_name ?? 'Download bukti'}
                                    </a>
                                )}
                            />
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
        </AppLayout>
    );
}
