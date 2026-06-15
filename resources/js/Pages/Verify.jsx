import { Head } from '@inertiajs/react';
import { CheckCircle2, XCircle } from 'lucide-react';

function Info({ label, value }) {
    return (
        <div className="rounded-md border border-slate-200 p-3">
            <div className="text-xs uppercase text-slate-500">{label}</div>
            <div className="mt-1 font-medium text-slate-950">{value ?? '-'}</div>
        </div>
    );
}

export default function Verify({ valid, document, quotation }) {
    const verifiedDocument = document ?? quotation;

    return (
        <main className="min-h-screen bg-slate-100 px-4 py-10 text-slate-950">
            <Head title="Verifikasi Dokumen" />
            <div className="mx-auto max-w-xl rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
                <div className="flex flex-col items-center text-center">
                    {valid ? (
                        <CheckCircle2 className="h-14 w-14 text-emerald-600" />
                    ) : (
                        <XCircle className="h-14 w-14 text-red-600" />
                    )}
                    <div className={`mt-4 rounded-full px-3 py-1 text-sm font-medium ${valid ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200' : 'bg-red-50 text-red-700 ring-1 ring-red-200'}`}>
                        {valid ? 'Dokumen Valid & Terverifikasi' : 'Dokumen Tidak Valid'}
                    </div>
                    <h1 className="mt-4 text-2xl font-semibold tracking-normal">Verifikasi Dokumen</h1>
                    {!valid && <p className="mt-2 text-sm text-slate-600">Dokumen tidak ditemukan atau tidak valid</p>}
                </div>

                {valid && (
                    <div className="mt-6 grid gap-3 sm:grid-cols-2">
                        <Info label="Jenis Dokumen" value={verifiedDocument.jenis_dokumen} />
                        <Info label={verifiedDocument.nomor_label ?? 'Nomor Dokumen'} value={verifiedDocument.nomor ?? verifiedDocument.no_quotation} />
                        <Info label={verifiedDocument.pihak_label ?? 'Customer'} value={verifiedDocument.pihak ?? verifiedDocument.customer} />
                        <Info label={verifiedDocument.tanggal_label ?? 'Tanggal Terbit'} value={verifiedDocument.tanggal ?? verifiedDocument.tgl_quotation} />
                        <Info label="Diapprove oleh" value={verifiedDocument.approved_by} />
                        <Info label="Tanggal Approve" value={verifiedDocument.approved_at} />
                    </div>
                )}
            </div>
        </main>
    );
}
