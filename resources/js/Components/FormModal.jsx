import Modal from '@/Components/Modal';
import { Button } from '@/Components/ui/button';

export default function FormModal({ show, onClose, title, children, footer }) {
    return (
        <Modal show={show} onClose={onClose} maxWidth="2xl">
            <div className="p-4 sm:p-6">
                <div className="mb-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <h2 className="text-lg font-semibold text-slate-950 dark:text-white">{title}</h2>
                    <Button type="button" variant="ghost" onClick={onClose}>Tutup</Button>
                </div>
                {children}
                {footer && <div className="mt-6 flex flex-col justify-end gap-2 sm:flex-row">{footer}</div>}
            </div>
        </Modal>
    );
}
