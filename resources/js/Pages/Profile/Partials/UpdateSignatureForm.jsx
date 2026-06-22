import ConfirmDialog from '@/Components/ConfirmDialog';
import DangerButton from '@/Components/DangerButton';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import SecondaryButton from '@/Components/SecondaryButton';
import { Transition } from '@headlessui/react';
import { router, useForm } from '@inertiajs/react';
import { Upload, Trash2 } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';

export default function UpdateSignatureForm({ signatureUrl, status, className = '' }) {
    const inputRef = useRef(null);
    const [previewUrl, setPreviewUrl] = useState(signatureUrl);
    const [localPreviewUrl, setLocalPreviewUrl] = useState(null);
    const [showDeleteConfirm, setShowDeleteConfirm] = useState(false);

    const { data, setData, errors, processing, patch, reset, recentlySuccessful } =
        useForm({
            signature: null,
        });

    useEffect(() => {
        setPreviewUrl(signatureUrl);
    }, [signatureUrl]);

    useEffect(() => {
        return () => {
            if (localPreviewUrl) {
                URL.revokeObjectURL(localPreviewUrl);
            }
        };
    }, [localPreviewUrl]);

    const selectFile = (e) => {
        const file = e.target.files?.[0] ?? null;

        setData('signature', file);

        if (localPreviewUrl) {
            URL.revokeObjectURL(localPreviewUrl);
        }

        if (file) {
            const nextPreviewUrl = URL.createObjectURL(file);
            setLocalPreviewUrl(nextPreviewUrl);
            setPreviewUrl(nextPreviewUrl);
        } else {
            setLocalPreviewUrl(null);
            setPreviewUrl(signatureUrl);
        }
    };

    const submit = (e) => {
        e.preventDefault();

        patch(route('profile.signature.update'), {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => {
                reset('signature');
                if (inputRef.current) {
                    inputRef.current.value = '';
                }
            },
        });
    };

    const deleteSignature = () => {
        router.delete(route('profile.signature.delete'), {
            preserveScroll: true,
            onSuccess: () => {
                reset('signature');
                setPreviewUrl(null);
                setLocalPreviewUrl(null);
                if (inputRef.current) {
                    inputRef.current.value = '';
                }
            },
        });
    };

    return (
        <section className={className}>
            <header>
                <h2 className="text-lg font-medium text-gray-900 dark:text-gray-100">
                    Tanda Tangan Digital
                </h2>

                <p className="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Tanda tangan akan digunakan pada dokumen yang Anda setujui
                    (Quotation, Purchase Order, Permintaan Dana)
                </p>
            </header>

            <form onSubmit={submit} className="mt-6 space-y-6">
                <div>
                    <InputLabel htmlFor="signature" value="File Tanda Tangan" />

                    <input
                        ref={inputRef}
                        id="signature"
                        type="file"
                        accept="image/png,image/jpeg"
                        className="sr-only"
                        onChange={selectFile}
                    />

                    <div className="mt-2 flex flex-wrap items-center gap-3">
                        <SecondaryButton type="button" onClick={() => inputRef.current?.click()}>
                            <Upload className="mr-2 h-4 w-4" />
                            Upload Tanda Tangan
                        </SecondaryButton>

                        {(signatureUrl || previewUrl) && (
                            <DangerButton type="button" onClick={() => setShowDeleteConfirm(true)}>
                                <Trash2 className="mr-2 h-4 w-4" />
                                Hapus TTD
                            </DangerButton>
                        )}
                    </div>

                    <InputError className="mt-2" message={errors.signature} />
                </div>

                <div className="rounded-lg border border-dashed border-gray-300 bg-gray-50 p-4 dark:border-gray-600 dark:bg-gray-900/40">
                    {previewUrl ? (
                        <img
                            src={previewUrl}
                            alt="Preview tanda tangan digital"
                            className="h-auto max-h-48 max-w-full rounded bg-white object-contain p-3 shadow-sm dark:bg-gray-100"
                        />
                    ) : (
                        <p className="text-sm text-gray-500 dark:text-gray-400">
                            Belum ada tanda tangan digital. Upload file PNG/JPG maksimal 2MB.
                        </p>
                    )}
                </div>

                <div className="flex items-center gap-4">
                    <PrimaryButton disabled={processing || !data.signature}>
                        Simpan TTD
                    </PrimaryButton>

                    <Transition
                        show={recentlySuccessful || status === 'signature-updated'}
                        enter="transition ease-in-out"
                        enterFrom="opacity-0"
                        leave="transition ease-in-out"
                        leaveTo="opacity-0"
                    >
                        <p className="text-sm text-gray-600 dark:text-gray-400">
                            Tanda tangan berhasil disimpan.
                        </p>
                    </Transition>

                    {status === 'signature-deleted' && (
                        <p className="text-sm text-gray-600 dark:text-gray-400">
                            Tanda tangan berhasil dihapus.
                        </p>
                    )}
                </div>
            </form>

            <ConfirmDialog
                show={showDeleteConfirm}
                title="Hapus tanda tangan"
                description="Tanda tangan digital Anda akan dihapus dari profil."
                confirmText="Hapus TTD"
                onCancel={() => setShowDeleteConfirm(false)}
                onConfirm={deleteSignature}
            />
        </section>
    );
}
