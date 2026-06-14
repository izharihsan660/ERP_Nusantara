import Modal from '@/Components/Modal';
import { Button } from '@/Components/ui/button';

export default function ConfirmDialog({
    show,
    title = 'Konfirmasi aksi',
    description = 'Aksi ini akan diproses.',
    confirmText = 'Lanjutkan',
    onCancel,
    onConfirm,
    processing = false,
}) {
    return (
        <Modal show={show} onClose={onCancel} maxWidth="md">
            <div className="p-6">
                <h2 className="text-lg font-semibold text-slate-950 dark:text-white">{title}</h2>
                <p className="mt-2 text-sm text-slate-500 dark:text-slate-400">{description}</p>
                <div className="mt-6 flex justify-end gap-2">
                    <Button type="button" variant="outline" onClick={onCancel} disabled={processing}>
                        Batal
                    </Button>
                    <Button type="button" variant="destructive" onClick={onConfirm} disabled={processing}>
                        {confirmText}
                    </Button>
                </div>
            </div>
        </Modal>
    );
}
