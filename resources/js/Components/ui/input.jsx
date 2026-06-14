import { cn } from '@/lib/utils';

export function Input({ className, ...props }) {
    return (
        <input
            className={cn(
                'h-10 w-full rounded-md border border-slate-200 bg-white px-3 text-sm text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-slate-400 focus:ring-2 focus:ring-slate-200 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:ring-slate-800',
                className,
            )}
            {...props}
        />
    );
}
