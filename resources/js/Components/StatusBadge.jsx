import { cn } from '@/lib/utils';

const styles = {
    DRAFT: 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-200',
    PENDING_APPROVAL: 'bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-300',
    APPROVED: 'bg-green-100 text-green-700 dark:bg-green-950 dark:text-green-300',
    REJECTED: 'bg-red-100 text-red-700 dark:bg-red-950 dark:text-red-300',
    VOID: 'bg-slate-100 text-slate-500 line-through dark:bg-slate-800 dark:text-slate-400',
    PAID: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300',
    SHIPPED: 'bg-blue-100 text-blue-700 dark:bg-blue-950 dark:text-blue-300',
    OPEN: 'bg-sky-100 text-sky-700 dark:bg-sky-950 dark:text-sky-300',
    COMPLETED: 'bg-green-100 text-green-700 dark:bg-green-950 dark:text-green-300',
    BELUM_TERSUPPLY: 'bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-300',
    TERSUPPLY: 'bg-green-100 text-green-700 dark:bg-green-950 dark:text-green-300',
    BELUM: 'bg-red-100 text-red-700 dark:bg-red-950 dark:text-red-300',
    SEBAGIAN: 'bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-300',
    LUNAS: 'bg-green-100 text-green-700 dark:bg-green-950 dark:text-green-300',
    active: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300',
    inactive: 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400',
    default: 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-200',
};

export default function StatusBadge({ value, children, className }) {
    const key = value === true || value === 'active' ? 'active' : value === false || value === 'inactive' ? 'inactive' : value;

    return (
        <span className={cn('inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium', styles[key] ?? styles.default, className)}>
            {children ?? (key === 'active' ? 'Aktif' : key === 'inactive' ? 'Nonaktif' : value)}
        </span>
    );
}
