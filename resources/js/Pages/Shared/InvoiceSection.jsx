import Modal from '@/Components/Modal';
import ConfirmDialog from '@/Components/ConfirmDialog';
import InputLabel from '@/Components/Form/InputLabel';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Select } from '@/Components/ui/select';
import { Textarea } from '@/Components/ui/textarea';
import { formatRupiah, formatRupiahInput, parseRupiah } from '@/utils/currency';
import { useForm, usePage } from '@inertiajs/react';
import { Ban, Banknote, Download, FileCheck2, Plus, Trash2, Upload } from 'lucide-react';
import { useState } from 'react';

const paymentStyles = {
    BELUM: 'bg-red-50 text-red-700 ring-red-200',
    SEBAGIAN: 'bg-amber-50 text-amber-700 ring-amber-200',
    LUNAS: 'bg-emerald-50 text-emerald-700 ring-emerald-200',
};

const paymentDocumentTypes = [
    { value: 'BUKTI_TRANSFER', label: 'Bukti Transfer' },
    { value: 'INVOICE_CUSTOMER', label: 'Invoice Customer' },
];

function today() {
    return new Date().toISOString().slice(0, 10);
}

function PaymentBadge({ status, label }) {
    return (
        <span className={`inline-flex rounded-full px-2.5 py-1 text-xs font-medium ring-1 ring-inset ${paymentStyles[status] ?? paymentStyles.BELUM}`}>
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

function setPayment(form, value) {
    form.setData({
        ...form.data,
        metode_pembayaran: value,
        top_hari: value === 'TOP' ? form.data.top_hari : '',
    });
}

export default function InvoiceSection({ spbList = [], defaultPayment = { metode_pembayaran: 'COD', top_hari: '' } }) {
    const permissions = usePage().props.auth.user.permissions ?? [];
    const [modal, setModal] = useState(null);
    const [selectedSpb, setSelectedSpb] = useState(null);
    const [selectedInvoice, setSelectedInvoice] = useState(null);
    const [confirm, setConfirm] = useState({ isOpen: false, title: '', message: '', variant: 'danger', onConfirm: () => {} });

    const canCreate = permissions.includes('buat_invoice');
    const canView = permissions.includes('lihat_invoice');
    const canUpload = permissions.includes('upload_ttd_invoice');
    const canUpdatePayment = permissions.includes('update_pembayaran_invoice');
    const canVoid = permissions.includes('void_invoice');

    const invoiceForm = useForm({
        no_faktur_pajak: '',
        metode_pembayaran: defaultPayment.metode_pembayaran ?? 'COD',
        top_hari: defaultPayment.metode_pembayaran === 'TOP' ? (defaultPayment.top_hari ?? '') : '',
        tgl_dokumen: today(),
    });
    const paymentForm = useForm({ tgl_bayar: today(), jumlah_bayar: '', keterangan: '', documents: [] });
    const uploadForm = useForm({ file_spb: null, file_invoice: null, file_tanda_terima: null });
    const voidForm = useForm({ alasan_void: '' });

    const openCreate = (spb) => {
        setSelectedSpb(spb);
        invoiceForm.setData({
            no_faktur_pajak: '',
            metode_pembayaran: defaultPayment.metode_pembayaran ?? 'COD',
            top_hari: defaultPayment.metode_pembayaran === 'TOP' ? (defaultPayment.top_hari ?? '') : '',
            tgl_dokumen: today(),
        });
        setModal('create');
    };

    const openPayment = (invoice) => {
        setSelectedInvoice(invoice);
        paymentForm.setData({
            tgl_bayar: invoice.tgl_bayar ?? today(),
            jumlah_bayar: invoice.jumlah_bayar ?? '',
            keterangan: '',
            documents: [],
        });
        setModal('payment');
    };

    const openUpload = (invoice) => {
        setSelectedInvoice(invoice);
        uploadForm.setData({ file_spb: null, file_invoice: null, file_tanda_terima: null });
        setModal('upload');
    };

    const openVoid = (invoice) => {
        setSelectedInvoice(invoice);
        voidForm.setData('alasan_void', '');
        setModal('void');
    };

    const submitCreate = (event) => {
        event.preventDefault();

        if (!selectedSpb) {
            return;
        }

        invoiceForm.post(route('spb.invoices.store', selectedSpb.id), {
            preserveScroll: true,
            onSuccess: () => {
                invoiceForm.reset();
                setSelectedSpb(null);
                setModal(null);
            },
        });
    };

    const submitPayment = (event) => {
        event.preventDefault();

        if (!selectedInvoice) {
            return;
        }

        paymentForm
            .transform((payload) => ({
                ...payload,
                jumlah_bayar: parseRupiah(payload.jumlah_bayar),
            }))
            .post(route('invoices.pembayaran', selectedInvoice.id), {
                forceFormData: true,
                preserveScroll: true,
                onSuccess: () => {
                    paymentForm.reset();
                    setSelectedInvoice(null);
                    setModal(null);
                },
            });
    };

    const updatePaymentDocument = (index, field, value) => {
        const documents = [...paymentForm.data.documents];
        documents[index] = { ...documents[index], [field]: value };
        paymentForm.setData('documents', documents);
    };

    const addPaymentDocument = () => {
        if (paymentForm.data.documents.length >= 3) {
            return;
        }

        paymentForm.setData('documents', [...paymentForm.data.documents, { tipe_dokumen: paymentDocumentTypes[0].value, file: null }]);
    };

    const removePaymentDocument = (index) => {
        paymentForm.setData('documents', paymentForm.data.documents.filter((_, documentIndex) => documentIndex !== index));
    };

    const submitUpload = (event) => {
        event.preventDefault();

        if (!selectedInvoice) {
            return;
        }

        uploadForm.post(route('invoices.upload-ttd', selectedInvoice.id), {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => {
                uploadForm.reset();
                setSelectedInvoice(null);
                setModal(null);
            },
        });
    };

    const submitVoid = (event) => {
        event.preventDefault();

        if (!selectedInvoice) {
            return;
        }

        setConfirm({
            isOpen: true,
            title: 'Void Invoice/Nota',
            message: 'Invoice/Nota akan di-void permanen. Pastikan belum ada pembayaran. Lanjutkan?',
            variant: 'danger',
            confirmLabel: 'Ya, Void',
            onConfirm: () => voidForm.post(route('invoices.void', selectedInvoice.id), {
                preserveScroll: true,
                onSuccess: () => {
                    voidForm.reset();
                    setSelectedInvoice(null);
                    setModal(null);
                },
            }),
        });
    };

    return (
        <section id="tagihan" className="rounded-lg border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-950">
            <div className="border-b border-slate-200 p-4 dark:border-slate-800">
                <h2 className="font-semibold text-slate-950 dark:text-white">Tagihan</h2>
            </div>
            <div className="space-y-3 p-4">
                {spbList.length === 0 && (
                    <div className="rounded-md bg-slate-50 p-4 text-sm text-slate-600 dark:bg-slate-900 dark:text-slate-300">Belum ada SPB untuk dibuatkan tagihan.</div>
                )}
                {spbList.map((spb) => (
                    <div key={spb.id} className="rounded-md border border-slate-200 p-4 dark:border-slate-800">
                        <div className="flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <div className="text-sm text-slate-500">SPB</div>
                                <div className="font-semibold text-slate-950 dark:text-white">{spb.no_spb}</div>
                                <div className="mt-1 text-sm text-slate-500">{spb.items_count} baris / {spb.items_qty} qty</div>
                            </div>
                            {!spb.invoice && canCreate && spb.status !== 'VOID' && (
                                <Button type="button" variant="secondary" onClick={() => openCreate(spb)}>
                                    <Plus className="h-4 w-4" />Buat Invoice/Nota
                                </Button>
                            )}
                        </div>

                        {spb.invoice && (
                            <div className="mt-4 grid gap-4 lg:grid-cols-[1fr_auto]">
                                <div className="grid gap-3 md:grid-cols-2 lg:grid-cols-4">
                                    <div>
                                        <div className="text-xs uppercase text-slate-500">No. Dokumen</div>
                                        <div className="mt-1 font-medium">{spb.invoice.no_dokumen}</div>
                                    </div>
                                    <div>
                                        <div className="text-xs uppercase text-slate-500">Tipe</div>
                                        <div className="mt-1 font-medium">{spb.invoice.tipe_dokumen_label}</div>
                                    </div>
                                    <div>
                                        <div className="text-xs uppercase text-slate-500">Total</div>
                                        <div className="mt-1 font-medium">{formatRupiah(spb.invoice.total_nilai)}</div>
                                    </div>
                                    <div>
                                        <div className="text-xs uppercase text-slate-500">Status Bayar</div>
                                        <div className="mt-1"><PaymentBadge status={spb.invoice.status_pembayaran} label={spb.invoice.status_pembayaran_label} /></div>
                                    </div>
                                    {spb.invoice.metode_pembayaran === 'TOP' && (
                                        <div>
                                            <div className="text-xs uppercase text-slate-500">Jatuh Tempo</div>
                                            <div className={`mt-1 font-medium ${spb.invoice.is_jatuh_tempo_h7 ? 'text-red-600' : ''}`}>{spb.invoice.tgl_jatuh_tempo}</div>
                                        </div>
                                    )}
                                    <div>
                                        <div className="text-xs uppercase text-slate-500">Sudah Dibayar</div>
                                        <div className="mt-1 font-medium">{formatRupiah(spb.invoice.jumlah_bayar)}</div>
                                    </div>
                                    <div>
                                        <div className="text-xs uppercase text-slate-500">Sisa</div>
                                        <div className="mt-1 font-medium">{formatRupiah(Number(spb.invoice.total_nilai ?? 0) - Number(spb.invoice.jumlah_bayar ?? 0))}</div>
                                    </div>
                                </div>
                                <div className="flex flex-wrap justify-start gap-2 lg:justify-end">
                                    {canView && (
                                        <>
                                            <Button asChild size="sm" variant="secondary"><a href={route('invoices.download', [spb.invoice.id, 'invoice'])}><Download className="h-4 w-4" />Dokumen</a></Button>
                                            <Button asChild size="sm" variant="secondary"><a href={route('invoices.download', [spb.invoice.id, 'faktur'])}><Download className="h-4 w-4" />Faktur</a></Button>
                                            <Button asChild size="sm" variant="secondary"><a href={route('invoices.download', [spb.invoice.id, 'tanda-terima'])}><Download className="h-4 w-4" />Tanda Terima</a></Button>
                                            {spb.invoice.file_ttd_gabungan && (
                                                <Button asChild size="sm" variant="secondary"><a href={route('invoices.download', [spb.invoice.id, 'gabungan'])}><FileCheck2 className="h-4 w-4" />Gabungan</a></Button>
                                            )}
                                        </>
                                    )}
                                    {canUpdatePayment && spb.invoice.status !== 'VOID' && (
                                        <Button type="button" size="sm" variant="outline" onClick={() => openPayment(spb.invoice)}><Banknote className="h-4 w-4" />Pembayaran</Button>
                                    )}
                                    {canUpload && spb.invoice.status !== 'VOID' && (
                                        <Button type="button" size="sm" variant="outline" onClick={() => openUpload(spb.invoice)}><Upload className="h-4 w-4" />Upload TTD</Button>
                                    )}
                                    {canVoid && spb.invoice.is_voidable && (
                                        <Button type="button" size="sm" variant="destructive" onClick={() => openVoid(spb.invoice)}><Ban className="h-4 w-4" />Void</Button>
                                    )}
                                </div>
                                {spb.invoice.payment_documents?.length > 0 && (
                                    <div className="lg:col-span-2">
                                        <div className="mt-2 overflow-x-auto rounded-md border border-slate-200 dark:border-slate-800">
                                            <table className="min-w-full table-fixed divide-y divide-slate-200 text-sm dark:divide-slate-800">
                                                <thead className="bg-slate-50 dark:bg-slate-900">
                                                    <tr>
                                                        <th className="px-3 py-2 text-left">Tipe</th>
                                                        <th className="px-3 py-2 text-left">Nama File</th>
                                                        <th className="px-3 py-2 text-left">Tanggal</th>
                                                        <th className="px-3 py-2 text-right">Download</th>
                                                    </tr>
                                                </thead>
                                                <tbody className="divide-y divide-slate-100 dark:divide-slate-900">
                                                    {spb.invoice.payment_documents.map((document) => (
                                                        <tr key={document.id}>
                                                            <td className="px-3 py-2">{document.tipe_dokumen_label}</td>
                                                            <td className="px-3 py-2">{document.nama_file}</td>
                                                            <td className="px-3 py-2">{document.created_at}</td>
                                                            <td className="px-3 py-2 text-right">
                                                                <Button asChild size="sm" variant="secondary">
                                                                    <a href={route('invoices.payment-documents.download', document.id)}><Download className="h-4 w-4" />Download</a>
                                                                </Button>
                                                            </td>
                                                        </tr>
                                                    ))}
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                )}
                            </div>
                        )}
                    </div>
                ))}
            </div>

            <Modal show={modal === 'create'} onClose={() => setModal(null)} maxWidth="lg">
                <form onSubmit={submitCreate} className="p-6">
                    <h2 className="text-lg font-semibold text-slate-950 dark:text-white">Buat Invoice/Nota</h2>
                    <div className="mt-5 grid gap-4 md:grid-cols-2">
                        <FormRow label="No. Faktur Pajak" optional error={invoiceForm.errors.no_faktur_pajak}>
                            <Input value={invoiceForm.data.no_faktur_pajak} onChange={(e) => invoiceForm.setData('no_faktur_pajak', e.target.value)} />
                        </FormRow>
                        <FormRow label="Tanggal Dokumen" required error={invoiceForm.errors.tgl_dokumen}>
                            <Input type="date" value={invoiceForm.data.tgl_dokumen} onChange={(e) => invoiceForm.setData('tgl_dokumen', e.target.value)} />
                        </FormRow>
                        <FormRow label="Metode Pembayaran" required error={invoiceForm.errors.metode_pembayaran}>
                            <Select value={invoiceForm.data.metode_pembayaran} onChange={(e) => setPayment(invoiceForm, e.target.value)}>
                                <option value="COD">COD</option>
                                <option value="CBD">CBD</option>
                                <option value="TOP">TOP</option>
                            </Select>
                        </FormRow>
                        {invoiceForm.data.metode_pembayaran === 'TOP' && (
                            <FormRow label="Jangka TOP" conditionalNote="wajib jika metode TOP" error={invoiceForm.errors.top_hari}>
                                <Input type="number" min="1" max="365" value={invoiceForm.data.top_hari} onChange={(e) => invoiceForm.setData('top_hari', e.target.value)} />
                            </FormRow>
                        )}
                    </div>
                    <div className="mt-6 flex flex-col justify-end gap-2 sm:flex-row">
                        <Button type="button" variant="outline" onClick={() => setModal(null)} disabled={invoiceForm.processing}>Batal</Button>
                        <Button type="submit" disabled={invoiceForm.processing}>Buat Invoice/Nota</Button>
                    </div>
                </form>
            </Modal>

            <Modal show={modal === 'payment'} onClose={() => setModal(null)} maxWidth="lg">
                <form onSubmit={submitPayment} className="p-6">
                    <h2 className="text-lg font-semibold text-slate-950 dark:text-white">Update Pembayaran</h2>
                    <div className="mt-5 grid gap-4 md:grid-cols-2">
                        <FormRow label="Tanggal Bayar" required error={paymentForm.errors.tgl_bayar}>
                            <Input type="date" value={paymentForm.data.tgl_bayar} onChange={(e) => paymentForm.setData('tgl_bayar', e.target.value)} />
                        </FormRow>
                        <FormRow label="Jumlah Bayar" required error={paymentForm.errors.jumlah_bayar}>
                            <Input inputMode="numeric" value={formatRupiahInput(paymentForm.data.jumlah_bayar)} onChange={(e) => paymentForm.setData('jumlah_bayar', parseRupiah(e.target.value))} />
                        </FormRow>
                        <div className="md:col-span-2">
                            <FormRow label="Keterangan" optional error={paymentForm.errors.keterangan}>
                                <Textarea value={paymentForm.data.keterangan} onChange={(e) => paymentForm.setData('keterangan', e.target.value)} />
                            </FormRow>
                        </div>
                        <div className="space-y-3 md:col-span-2">
                            <div className="flex items-center justify-between gap-3">
                                <InputLabel label="Dokumen Pembayaran" optional />
                                <Button type="button" size="sm" variant="secondary" onClick={addPaymentDocument} disabled={paymentForm.data.documents.length >= 3}>
                                    <Plus className="h-4 w-4" />Tambah Dokumen
                                </Button>
                            </div>
                            <FieldError message={paymentForm.errors.documents} />
                            {paymentForm.data.documents.map((document, index) => (
                                <div key={index} className="grid gap-3 rounded-md border border-slate-200 p-3 dark:border-slate-800 md:grid-cols-[180px_1fr_auto]">
                                    <div>
                                        <InputLabel label="Tipe" required />
                                        <Select className="mt-1" value={document.tipe_dokumen} onChange={(e) => updatePaymentDocument(index, 'tipe_dokumen', e.target.value)}>
                                            {paymentDocumentTypes.map((option) => <option key={option.value} value={option.value}>{option.label}</option>)}
                                        </Select>
                                        <FieldError message={paymentForm.errors[`documents.${index}.tipe_dokumen`]} />
                                    </div>
                                    <div>
                                        <InputLabel label="File" required />
                                        <Input className="mt-1" type="file" accept=".pdf,.jpg,.png" onChange={(e) => updatePaymentDocument(index, 'file', e.target.files[0])} />
                                        <FieldError message={paymentForm.errors[`documents.${index}.file`]} />
                                    </div>
                                    <div className="flex items-end">
                                        <Button type="button" size="icon" variant="ghost" onClick={() => removePaymentDocument(index)}>
                                            <Trash2 className="h-4 w-4" />
                                        </Button>
                                    </div>
                                </div>
                            ))}
                        </div>
                    </div>
                    <div className="mt-6 flex flex-col justify-end gap-2 sm:flex-row">
                        <Button type="button" variant="outline" onClick={() => setModal(null)} disabled={paymentForm.processing}>Batal</Button>
                        <Button type="submit" disabled={paymentForm.processing}>Simpan</Button>
                    </div>
                </form>
            </Modal>

            <Modal show={modal === 'upload'} onClose={() => setModal(null)} maxWidth="lg">
                <form onSubmit={submitUpload} className="p-6">
                    <h2 className="text-lg font-semibold text-slate-950 dark:text-white">Upload TTD Customer</h2>
                    <div className="mt-5 grid gap-4">
                        <FormRow label="File SPB TTD" required error={uploadForm.errors.file_spb}>
                            <Input type="file" accept=".pdf,.jpg,.png" onChange={(e) => uploadForm.setData('file_spb', e.target.files[0])} />
                        </FormRow>
                        <FormRow label="File Invoice/Nota TTD" required error={uploadForm.errors.file_invoice}>
                            <Input type="file" accept=".pdf,.jpg,.png" onChange={(e) => uploadForm.setData('file_invoice', e.target.files[0])} />
                        </FormRow>
                        <FormRow label="File Tanda Terima TTD" required error={uploadForm.errors.file_tanda_terima}>
                            <Input type="file" accept=".pdf,.jpg,.png" onChange={(e) => uploadForm.setData('file_tanda_terima', e.target.files[0])} />
                        </FormRow>
                        <div className="rounded-md bg-slate-50 p-3 text-sm text-slate-600 dark:bg-slate-900 dark:text-slate-300">
                            Sistem akan menggabungkan ketiga file menjadi 1 PDF otomatis.
                        </div>
                    </div>
                    <div className="mt-6 flex flex-col justify-end gap-2 sm:flex-row">
                        <Button type="button" variant="outline" onClick={() => setModal(null)} disabled={uploadForm.processing}>Batal</Button>
                        <Button type="submit" disabled={uploadForm.processing}>Upload & Gabung</Button>
                    </div>
                </form>
            </Modal>

            <Modal show={modal === 'void'} onClose={() => setModal(null)} maxWidth="md">
                <form onSubmit={submitVoid} className="p-6">
                    <h2 className="text-lg font-semibold text-slate-950 dark:text-white">Void Invoice/Nota</h2>
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
