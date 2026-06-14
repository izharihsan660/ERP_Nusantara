import { cn } from '@/lib/utils';

const styles = {
    active: 'bg-emerald-50 text-emerald-700 ring-emerald-200 dark:bg-emerald-950 dark:text-emerald-300 dark:ring-emerald-900',
    inactive: 'bg-slate-100 text-slate-600 ring-slate-200 dark:bg-slate-900 dark:text-slate-300 dark:ring-slate-800',
    default: 'bg-blue-50 text-blue-700 ring-blue-200 dark:bg-blue-950 dark:text-blue-300 dark:ring-blue-900',
};

export default function StatusBadge({ value, children, className }) {
    const key = value === true || value === 'active' ? 'active' : value === false || value === 'inactive' ? 'inactive' : 'default';

    return (
        <span className={cn('inline-flex items-center rounded-full px-2 py-1 text-xs font-medium ring-1 ring-inset', styles[key], className)}>
            {children ?? (key === 'active' ? 'Aktif' : key === 'inactive' ? 'Nonaktif' : value)}
        </span>
    );
}
