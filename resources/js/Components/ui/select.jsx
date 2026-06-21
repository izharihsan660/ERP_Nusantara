import { cn } from '@/lib/utils';

export function Select({ className, children, ...props }) {
    return (
        <select
            className={cn(
                'h-10 w-full rounded-md border border-[hsl(var(--input))] bg-[hsl(var(--background))] px-3 text-sm text-[hsl(var(--foreground))] shadow-sm outline-none transition focus:border-[hsl(var(--ring))] focus:ring-2 focus:ring-[hsl(var(--ring))]/20',
                className,
            )}
            {...props}
        >
            {children}
        </select>
    );
}
