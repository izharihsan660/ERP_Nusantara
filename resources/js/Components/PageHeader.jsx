import { cn } from '@/lib/utils';

export default function PageHeader({ title, description, actions, className }) {
    return (
        <div className={cn('mb-6 border-b border-[hsl(var(--border))] pb-6', className)}>
            <div className="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div className="min-w-0">
                    <h1 className="text-2xl font-bold tracking-tight text-[hsl(var(--foreground))]">
                        {title}
                    </h1>
                    {description && (
                        <p className="mt-1 text-sm text-[hsl(var(--muted-foreground))]">{description}</p>
                    )}
                </div>
                {actions && <div className="flex w-full flex-col gap-2 sm:ml-auto sm:w-auto sm:flex-row sm:flex-wrap sm:items-center sm:justify-end [&>*]:w-full sm:[&>*]:w-auto">{actions}</div>}
            </div>
        </div>
    );
}
