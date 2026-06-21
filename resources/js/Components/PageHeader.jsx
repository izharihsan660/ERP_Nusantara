import { cn } from '@/lib/utils';

export default function PageHeader({ title, description, actions, className }) {
    return (
        <div className={cn('mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between', className)}>
            <div>
                <h1 className="text-2xl font-semibold tracking-normal text-slate-950 dark:text-white">
                    {title}
                </h1>
                {description && (
                    <p className="mt-1 text-sm text-slate-500 dark:text-slate-400">{description}</p>
                )}
            </div>
            {actions && <div className="flex w-full flex-col gap-2 [&>*]:w-full sm:w-auto sm:flex-row sm:flex-wrap sm:items-center sm:[&>*]:w-auto">{actions}</div>}
        </div>
    );
}
