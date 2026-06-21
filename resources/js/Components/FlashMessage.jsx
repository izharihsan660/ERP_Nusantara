import { useEffect, useState } from 'react';
import { usePage } from '@inertiajs/react';
import { CheckCircle2, XCircle, AlertTriangle, X } from 'lucide-react';

export default function FlashMessage() {
    const { flash } = usePage().props;
    const [visible, setVisible] = useState(false);
    const [message, setMessage] = useState('');
    const [type, setType] = useState('success');

    useEffect(() => {
        if (flash?.success) {
            setMessage(flash.success);
            setType('success');
            setVisible(true);
            const timer = setTimeout(() => setVisible(false), 4000);
            return () => clearTimeout(timer);
        }
        
        if (flash?.error) {
            setMessage(flash.error);
            setType('error');
            setVisible(true);
            const timer = setTimeout(() => setVisible(false), 6000);
            return () => clearTimeout(timer);
        }
        
        if (flash?.warning) {
            setMessage(flash.warning);
            setType('warning');
            setVisible(true);
            const timer = setTimeout(() => setVisible(false), 5000);
            return () => clearTimeout(timer);
        }
    }, [flash]);

    if (!visible) return null;

    const config = {
        success: {
            icon: CheckCircle2,
            bgClass: 'bg-green-50 border-green-200',
            iconClass: 'text-green-600',
            textClass: 'text-green-800',
        },
        error: {
            icon: XCircle,
            bgClass: 'bg-red-50 border-red-200',
            iconClass: 'text-red-600',
            textClass: 'text-red-800',
        },
        warning: {
            icon: AlertTriangle,
            bgClass: 'bg-yellow-50 border-yellow-200',
            iconClass: 'text-yellow-600',
            textClass: 'text-yellow-800',
        },
    };

    const { icon: Icon, bgClass, iconClass, textClass } = config[type];

    return (
        <div
            className="fixed top-4 right-4 z-50 animate-in slide-in-from-right duration-300"
            role="alert"
            aria-live="polite"
        >
            <div className={`flex items-start gap-3 p-4 rounded-lg border shadow-lg max-w-md ${bgClass}`}>
                <Icon className={`w-5 h-5 flex-shrink-0 mt-0.5 ${iconClass}`} />
                <p className={`flex-1 text-sm font-medium ${textClass}`}>
                    {message}
                </p>
                <button
                    onClick={() => setVisible(false)}
                    className={`flex-shrink-0 hover:opacity-70 transition-opacity ${iconClass}`}
                    aria-label="Tutup notifikasi"
                >
                    <X className="w-5 h-5" />
                </button>
            </div>
        </div>
    );
}
