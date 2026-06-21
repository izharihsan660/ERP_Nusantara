import { cn } from '@/lib/utils';

export function Input({ className, ...props }) {
    return (
        <input
            className={cn(
                'h-10 w-full rounded-md border border-[hsl(var(--input))] bg-[hsl(var(--background))] px-3 text-sm text-[hsl(var(--foreground))] shadow-sm outline-none transition placeholder:text-[hsl(var(--muted-foreground))] focus:border-[hsl(var(--ring))] focus:ring-2 focus:ring-[hsl(var(--ring))]/20',
                className,
            )}
            {...props}
        />
    );
}
