import { useEffect } from 'react';
import { AlertTriangle, CheckCircle, XCircle, X } from 'lucide-react';
import { Button } from '@/Components/ui/button';

export default function ConfirmDialog({
    isOpen = false,
    show, // Backward compatibility
    onConfirm,
    onCancel,
    title = 'Konfirmasi',
    message,
    description, // Backward compatibility
    confirmLabel,
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
            iconClass: 'text-red-600 dark:text-red-400',
            bgClass: 'bg-red-50 dark:bg-red-950',
            buttonClass: 'bg-[hsl(var(--destructive))] text-[hsl(var(--destructive-foreground))] hover:opacity-90',
        },
        warning: {
            icon: AlertTriangle,
            iconClass: 'text-amber-600 dark:text-amber-400',
            bgClass: 'bg-amber-50 dark:bg-amber-950',
            buttonClass: 'bg-amber-500 text-white hover:bg-amber-600',
        },
        success: {
            icon: CheckCircle,
            iconClass: 'text-green-600 dark:text-green-400',
            bgClass: 'bg-green-50 dark:bg-green-950',
            buttonClass: 'bg-green-600 text-white hover:bg-green-700',
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
                className="absolute inset-0 bg-slate-950/50"
                onClick={onCancel}
                aria-label="Tutup dialog"
            />

            {/* Dialog */}
            <div className="relative w-full max-w-md rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] p-6 text-[hsl(var(--card-foreground))] shadow-xl animate-in zoom-in-95 duration-200">
                {/* Close button */}
                <button
                    type="button"
                    onClick={onCancel}
                    className="absolute right-4 top-4 rounded-md p-1 text-[hsl(var(--muted-foreground))] hover:bg-[hsl(var(--accent))]"
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
                        className="text-lg font-semibold text-[hsl(var(--foreground))]"
                    >
                        {title}
                    </h3>
                    <p className="mt-2 text-sm text-[hsl(var(--muted-foreground))]">
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
