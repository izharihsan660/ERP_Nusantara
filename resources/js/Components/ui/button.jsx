import { Slot } from '@radix-ui/react-slot';
import { cn } from '@/lib/utils';

const variants = {
    default: 'bg-slate-950 text-white hover:bg-slate-800 dark:bg-white dark:text-slate-950 dark:hover:bg-slate-200',
    secondary: 'bg-slate-100 text-slate-900 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-100 dark:hover:bg-slate-700',
    destructive: 'bg-red-600 text-white hover:bg-red-700',
    outline: 'border border-slate-200 bg-white text-slate-900 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:hover:bg-slate-900',
    ghost: 'text-slate-700 hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-slate-800',
};

export function Button({ className, variant = 'default', size = 'md', asChild = false, ...props }) {
    const Comp = asChild ? Slot : 'button';
    const sizes = {
        sm: 'h-8 px-3 text-xs',
        md: 'h-10 px-4 text-sm',
        icon: 'h-9 w-9 p-0',
    };

    return (
        <Comp
            className={cn(
                'inline-flex items-center justify-center gap-2 rounded-md font-medium transition focus:outline-none focus:ring-2 focus:ring-slate-400 disabled:pointer-events-none disabled:opacity-50',
                variants[variant],
                sizes[size],
                className,
            )}
            {...props}
        />
    );
}
