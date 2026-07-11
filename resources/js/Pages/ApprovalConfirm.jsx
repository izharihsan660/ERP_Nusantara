import { Button } from '@/Components/ui/button';
import { Head, Link, useForm } from '@inertiajs/react';
import { Check, X } from 'lucide-react';

export default function ApprovalConfirm({ confirmation = false, action, actionUrl, success, rejected = false, message, document }) {
    const { post, processing } = useForm({});

    const submit = () => {
        post(actionUrl);
    };

    return (
        <div className="min-h-screen flex items-center justify-center bg-slate-100 dark:bg-slate-950 p-4">
            <Head title={success ? (rejected ? "Dokumen Ditolak" : "Approval Berhasil") : "Approval Gagal"} />
            
            <div className="max-w-md w-full bg-white dark:bg-slate-900 rounded-lg shadow-lg p-8 text-center">
                {confirmation ? (
                    <>
                        <h1 className="text-2xl font-bold text-slate-900 dark:text-white mb-2">
                            Konfirmasi {action === 'reject' ? 'Penolakan' : 'Approval'}
                        </h1>

                        {document && (
                            <div className="mb-6 p-4 bg-slate-50 dark:bg-slate-800 rounded-lg">
                                <div className="text-sm text-slate-600 dark:text-slate-400 mb-1">
                                    {document.tipe}
                                </div>
                                <div className="text-lg font-semibold text-slate-900 dark:text-white mb-2">
                                    {document.nomor}
                                </div>
                                <div className="text-sm text-slate-600 dark:text-slate-400">
                                    {document.customer}
                                </div>
                            </div>
                        )}

                        <p className="text-slate-600 dark:text-slate-400 mb-6">
                            {action === 'reject'
                                ? 'Klik tombol di bawah untuk menolak dokumen ini.'
                                : 'Klik tombol di bawah untuk menyetujui dokumen ini.'}
                        </p>

                        <Button
                            variant={action === 'reject' ? 'destructive' : 'default'}
                            className="w-full"
                            disabled={processing}
                            onClick={submit}
                        >
                            {processing
                                ? 'Memproses...'
                                : action === 'reject' ? 'Tolak Dokumen' : 'Approve Dokumen'}
                        </Button>
                    </>
                ) : success ? (
                    <>
                        <div className={`mx-auto w-16 h-16 rounded-full flex items-center justify-center mb-4 ${rejected ? 'bg-red-100 dark:bg-red-900/20' : 'bg-green-100 dark:bg-green-900/20'}`}>
                            {rejected ? (
                                <X className="h-8 w-8 text-red-600 dark:text-red-400" />
                            ) : (
                                <Check className="h-8 w-8 text-green-600 dark:text-green-400" />
                            )}
                        </div>
                        
                        <h1 className="text-2xl font-bold text-slate-900 dark:text-white mb-2">
                            {rejected ? "Dokumen Ditolak" : "Approval Berhasil!"}
                        </h1>
                        
                        {document && (
                            <div className="mb-6 p-4 bg-slate-50 dark:bg-slate-800 rounded-lg">
                                <div className="text-sm text-slate-600 dark:text-slate-400 mb-1">
                                    {document.tipe}
                                </div>
                                <div className="text-lg font-semibold text-slate-900 dark:text-white mb-2">
                                    {document.nomor}
                                </div>
                                <div className="text-sm text-slate-600 dark:text-slate-400">
                                    {document.customer}
                                </div>
                            </div>
                        )}
                        
                        <p className="text-slate-600 dark:text-slate-400 mb-6">
                            {rejected 
                                ? "Dokumen telah ditolak. Notifikasi telah dikirim ke pembuat dokumen."
                                : "Dokumen telah berhasil disetujui. Anda dapat melihat detail lengkap di sistem."}
                        </p>
                        
                        {document && (
                            <Link href={document.url}>
                                <Button className="w-full">
                                    Lihat Dokumen
                                </Button>
                            </Link>
                        )}
                    </>
                ) : (
                    <>
                        <div className="mx-auto w-16 h-16 rounded-full bg-red-100 dark:bg-red-900/20 flex items-center justify-center mb-4">
                            <X className="h-8 w-8 text-red-600 dark:text-red-400" />
                        </div>
                        
                        <h1 className="text-2xl font-bold text-slate-900 dark:text-white mb-2">
                            Approval Gagal
                        </h1>
                        
                        <p className="text-slate-600 dark:text-slate-400 mb-6">
                            {message || "Terjadi kesalahan saat memproses approval. Silakan hubungi administrator."}
                        </p>
                        
                        <Button variant="outline" onClick={() => window.close()}>
                            Tutup
                        </Button>
                    </>
                )}
            </div>
        </div>
    );
}
