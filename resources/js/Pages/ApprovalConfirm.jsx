import { Link, Head } from '@inertiajs/react';
import { CheckCircle2 } from 'lucide-react';
import { Button } from '@/Components/ui/button';

export default function ApprovalConfirm({ document }) {
    return (
        <div className="flex min-h-screen items-center justify-center bg-slate-50 px-4">
            <Head title="Dokumen Approved" />
            <div className="w-full max-w-md rounded-lg border bg-white p-8 shadow-lg">
                <div className="flex flex-col items-center text-center">
                    <CheckCircle2 className="h-16 w-16 text-emerald-600" />
                    <h1 className="mt-4 text-2xl font-semibold">Dokumen Berhasil Diapprove</h1>
                    <p className="mt-2 text-sm text-slate-600">
                        {document.tipe} <strong>{document.nomor}</strong> untuk <strong>{document.customer}</strong> telah diapprove.
                    </p>
                    <Link href={document.url}>
                        <Button className="mt-6">Lihat di Sistem</Button>
                    </Link>
                </div>
            </div>
        </div>
    );
}
