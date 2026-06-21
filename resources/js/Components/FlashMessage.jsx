import { useEffect, useState } from 'react';
import { usePage } from '@inertiajs/react';
import { AlertTriangle, CheckCircle2, X, XCircle } from 'lucide-react';

export default function FlashMessage() {
    const { flash } = usePage().props;
    const [visible, setVisible] = useState(false);
    const [message, setMessage] = useState('');
    const [type, setType] = useState('success');

    useEffect(() => {
        const next = flash?.success ? ['success', flash.success, 4000]
            : flash?.error ? ['error', flash.error, 6000]
                : flash?.warning ? ['warning', flash.warning, 5000]
                    : null;

        if (!next) return undefined;

        const [nextType, nextMessage, duration] = next;
        setType(nextType);
        setMessage(nextMessage);
        setVisible(true);

        const timer = setTimeout(() => setVisible(false), duration);
        return () => clearTimeout(timer);
    }, [flash]);

    if (!visible) return null;

    const config = {
        success: { icon: CheckCircle2, className: 'border-green-200 bg-green-50 text-green-800 dark:border-green-900 dark:bg-green-950 dark:text-green-200' },
        error: { icon: XCircle, className: 'border-red-200 bg-red-50 text-red-800 dark:border-red-900 dark:bg-red-950 dark:text-red-200' },
        warning: { icon: AlertTriangle, className: 'border-amber-200 bg-amber-50 text-amber-800 dark:border-amber-900 dark:bg-amber-950 dark:text-amber-200' },
    };
    const { icon: Icon, className } = config[type];

    return (
        <div className="fixed right-4 top-4 z-[60] animate-in slide-in-from-right-4 fade-in duration-200" role="alert" aria-live="polite">
            <div className={`flex max-w-md items-start gap-3 rounded-lg border p-4 shadow-lg ${className}`}>
                <Icon className="mt-0.5 h-5 w-5 shrink-0" />
                <p className="flex-1 text-sm font-medium">{message}</p>
                <button type="button" onClick={() => setVisible(false)} className="rounded-md p-0.5 opacity-70 hover:opacity-100" aria-label="Tutup notifikasi">
                    <X className="h-4 w-4" />
                </button>
            </div>
        </div>
    );
}
