import { useEffect } from 'react';
import { AlertTriangle, CheckCircle, XCircle, X } from 'lucide-react';
import { Button } from '@/Components/ui/button';

export default function ConfirmDialog({
    isOpen = false,
    show, // Backward compatibility
    onConfirm,
    onCancel,
    title = 'Konfirmasi',
    message = 'Apakah Anda yakin?',
    description, // Backward compatibility
    confirmLabel = 'Ya, Lanjutkan',
    confirmText, // Backward compatibility
    cancelLabel = 'Batal',
    variant = 'danger', // 'danger' | 'warning' | 'success'
}) {
    // Support both prop styles for backward compatibility
    const dialogIsOpen = isOpen || show || false;
    const dialogMessage = message || description || 'Apakah Anda yakin?';
    const dialogConfirmLabel = confirmLabel || confirmText || 'Ya, Lanjutkan';

    // Handle Escape key press
    useEffect(() => {
        const handleEscape = (e) => {
            if (e.key === 'Escape' && dialogIsOpen) {
                onCancel?.();
            }
        };

        if (dialogIsOpen) {
            document.addEventListener('keydown', handleEscape);
            // Prevent background scroll
            document.body.style.overflow = 'hidden';
        }

        return () => {
            document.removeEventListener('keydown', handleEscape);
            document.body.style.overflow = 'unset';
        };
    }, [dialogIsOpen, onCancel]);

    if (!dialogIsOpen) return null;

    const variantConfig = {
        danger: {
            icon: XCircle,
            iconClass: 'text-red-600',
            bgClass: 'bg-red-50',
            buttonClass: 'bg-red-600 hover:bg-red-700 text-white',
        },
        warning: {
            icon: AlertTriangle,
            iconClass: 'text-yellow-600',
            bgClass: 'bg-yellow-50',
            buttonClass: 'bg-yellow-600 hover:bg-yellow-700 text-white',
        },
        success: {
            icon: CheckCircle,
            iconClass: 'text-green-600',
            bgClass: 'bg-green-50',
            buttonClass: 'bg-green-600 hover:bg-green-700 text-white',
        },
    };

    const config = variantConfig[variant] || variantConfig.danger;
    const Icon = config.icon;

    return (
        <div
            className="fixed inset-0 z-50 flex items-center justify-center p-4 animate-in fade-in duration-200"
            role="dialog"
            aria-modal="true"
            aria-labelledby="dialog-title"
        >
            {/* Overlay */}
            <div
                className="absolute inset-0 bg-black/50"
                onClick={onCancel}
                aria-label="Tutup dialog"
            />

            {/* Dialog */}
            <div className="relative w-full max-w-md rounded-lg bg-white p-6 shadow-xl dark:bg-slate-900 animate-in zoom-in-95 duration-200">
                {/* Close button */}
                <button
                    type="button"
                    onClick={onCancel}
                    className="absolute right-4 top-4 rounded-md p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-500 dark:hover:bg-slate-800"
                    aria-label="Tutup"
                >
                    <X className="h-4 w-4" />
                </button>

                {/* Icon */}
                <div className={`mx-auto flex h-12 w-12 items-center justify-center rounded-full ${config.bgClass}`}>
                    <Icon className={`h-6 w-6 ${config.iconClass}`} />
                </div>

                {/* Content */}
                <div className="mt-4 text-center">
                    <h3
                        id="dialog-title"
                        className="text-lg font-semibold text-slate-900 dark:text-white"
                    >
                        {title}
                    </h3>
                    <p className="mt-2 text-sm text-slate-600 dark:text-slate-400">
                        {dialogMessage}
                    </p>
                </div>

                {/* Actions */}
                <div className="mt-6 flex gap-3">
                    <Button
                        type="button"
                        variant="outline"
                        className="flex-1"
                        onClick={onCancel}
                    >
                        {cancelLabel}
                    </Button>
                    <button
                        type="button"
                        className={`flex-1 rounded-md px-4 py-2 text-sm font-semibold transition-colors ${config.buttonClass}`}
                        onClick={() => {
                            onConfirm?.();
                            onCancel?.();
                        }}
                    >
                        {dialogConfirmLabel}
                    </button>
                </div>
            </div>
        </div>
    );
}
