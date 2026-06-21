import { Button } from '@/Components/ui/button';
import { Link } from '@inertiajs/react';
import { FileText, Loader2 } from 'lucide-react';

export function Card({ children, className = '' }) {
    return (
        <section className={`rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] p-6 text-[hsl(var(--card-foreground))] shadow-sm ${className}`}>
            {children}
        </section>
    );
}

export function SectionTitle({ children, action }) {
    return (
        <div className="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h2 className="text-base font-semibold text-[hsl(var(--foreground))]">{children}</h2>
            {action}
        </div>
    );
}

export function InfoGrid({ children, columns = 'lg:grid-cols-4' }) {
    return <div className={`grid gap-4 sm:grid-cols-2 ${columns}`}>{children}</div>;
}

export function InfoItem({ label, value }) {
    return (
        <div className="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--muted))]/35 p-4">
            <div className="text-xs font-medium uppercase tracking-wide text-[hsl(var(--muted-foreground))]">{label}</div>
            <div className="mt-1 break-words text-sm font-semibold text-[hsl(var(--foreground))]">{value ?? '-'}</div>
        </div>
    );
}

export function EmptyState({ title, description, action, icon: Icon = FileText }) {
    return (
        <div className="flex flex-col items-center justify-center rounded-lg border border-dashed border-[hsl(var(--border))] bg-[hsl(var(--muted))]/25 px-6 py-12 text-center">
            <div className="flex h-12 w-12 items-center justify-center rounded-full bg-[hsl(var(--background))] text-[hsl(var(--muted-foreground))] shadow-sm">
                <Icon className="h-6 w-6" />
            </div>
            <h3 className="mt-4 text-sm font-semibold text-[hsl(var(--foreground))]">{title}</h3>
            {description && <p className="mt-1 max-w-sm text-sm text-[hsl(var(--muted-foreground))]">{description}</p>}
            {action && <div className="mt-4">{action}</div>}
        </div>
    );
}

export function IconButton({ href, title, icon: Icon, variant = 'outline', method }) {
    const content = <><Icon className="h-4 w-4" /><span className="sr-only">{title}</span></>;

    if (href) {
        return (
            <Button asChild size="icon" variant={variant} title={title} aria-label={title}>
                <Link href={href} method={method}>{content}</Link>
            </Button>
        );
    }

    return <Button size="icon" variant={variant} title={title} aria-label={title}>{content}</Button>;
}

export function LoadingButtonContent({ loading, children }) {
    return <>{loading && <Loader2 className="h-4 w-4 animate-spin" />}{children}</>;
}
