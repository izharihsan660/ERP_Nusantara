import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Select } from '@/Components/ui/select';
import { Link, router, usePage } from '@inertiajs/react';
import { ArrowDown, ArrowUp, Search } from 'lucide-react';
import { useState } from 'react';

export default function DataTable({
    data,
    columns,
    filters = {},
    routeName,
    filterSlot,
    emptyText = 'Data belum tersedia.',
}) {
    const [search, setSearch] = useState(filters.search ?? '');
    const rows = data?.data ?? [];
    const links = data?.links ?? [];
    const currentUrl = usePage().url;

    const submit = (event) => {
        event.preventDefault();
        router.get(route(routeName), { ...filters, search, page: 1 }, { preserveState: true, replace: true });
    };

    const sortBy = (key) => {
        const direction = filters.sort === key && filters.direction === 'asc' ? 'desc' : 'asc';
        router.get(route(routeName), { ...filters, sort: key, direction, page: 1 }, { preserveState: true, replace: true });
    };

    return (
        <div className="overflow-hidden rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] text-[hsl(var(--card-foreground))]">
            <div className="flex flex-col gap-3 border-b border-[hsl(var(--border))] p-4 lg:flex-row lg:items-center lg:justify-between">
                <form onSubmit={submit} className="flex w-full flex-col gap-2 sm:flex-row lg:max-w-md">
                    <div className="relative flex-1">
                        <Search className="pointer-events-none absolute left-3 top-2.5 h-4 w-4 text-[hsl(var(--muted-foreground))]" />
                        <Input
                            value={search}
                            onChange={(event) => setSearch(event.target.value)}
                            className="pl-9"
                            placeholder="Cari data..."
                        />
                    </div>
                    <Button type="submit" variant="secondary">Cari</Button>
                </form>
                {filterSlot && <div className="flex w-full flex-col gap-2 [&>*]:w-full sm:w-auto sm:flex-row sm:flex-wrap sm:[&>*]:w-auto">{filterSlot}</div>}
            </div>

            <div className="overflow-x-auto">
                <table className="min-w-full table-fixed text-sm">
                    <thead className="bg-[hsl(var(--muted))]">
                        <tr className="border-b border-[hsl(var(--border))]">
                            {columns.map((column) => (
                                <th key={column.key} className={`${column.width ?? 'w-48'} px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-[hsl(var(--muted-foreground))]`}>
                                    {column.sortable ? (
                                        <button type="button" onClick={() => sortBy(column.key)} className="inline-flex items-center gap-1 hover:text-[hsl(var(--foreground))]">
                                            {column.label}
                                            {filters.sort === column.key && (
                                                filters.direction === 'asc' ? <ArrowUp className="h-3.5 w-3.5" /> : <ArrowDown className="h-3.5 w-3.5" />
                                            )}
                                        </button>
                                    ) : column.label}
                                </th>
                            ))}
                        </tr>
                    </thead>
                    <tbody>
                        {rows.length === 0 && (
                            <tr>
                                <td colSpan={columns.length} className="px-4 py-14 text-center">
                                    <div className="mx-auto flex max-w-sm flex-col items-center gap-3 text-[hsl(var(--muted-foreground))]">
                                        <div className="flex h-10 w-10 items-center justify-center rounded-full bg-[hsl(var(--muted))]">
                                            <Search className="h-4 w-4" />
                                        </div>
                                        <div className="text-sm font-medium">{emptyText}</div>
                                    </div>
                                </td>
                            </tr>
                        )}
                        {rows.map((row) => (
                            <tr key={row.id} className="border-b border-[hsl(var(--border))] transition-colors last:border-0 hover:bg-[hsl(var(--accent))]/50">
                                {columns.map((column) => (
                                    <td key={column.key} className="px-4 py-3 text-sm text-[hsl(var(--foreground))]">
                                        <div className={column.truncate === false ? '' : 'truncate'} title={column.title ? column.title(row) : (typeof row[column.key] === 'string' ? row[column.key] : undefined)}>
                                            {column.render ? column.render(row) : row[column.key]}
                                        </div>
                                    </td>
                                ))}
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>

            <div className="flex flex-col gap-3 border-t border-[hsl(var(--border))] p-4 text-sm text-[hsl(var(--muted-foreground))] sm:flex-row sm:items-center sm:justify-between">
                <div>
                    Menampilkan {data?.from ?? 0}-{data?.to ?? 0} dari {data?.total ?? 0}
                </div>
                <div className="flex flex-wrap gap-1">
                    {links.map((link, index) => (
                        <Link
                            key={`${link.label}-${index}`}
                            href={link.url ?? currentUrl}
                            preserveScroll
                            preserveState
                            className={`rounded-md px-3 py-1.5 transition-colors ${link.active ? 'bg-[hsl(var(--primary))] text-[hsl(var(--primary-foreground))]' : 'hover:bg-[hsl(var(--accent))]'} ${!link.url ? 'pointer-events-none opacity-40' : ''}`}
                            dangerouslySetInnerHTML={{ __html: link.label }}
                        />
                    ))}
                </div>
                <Select
                    value={filters.per_page ?? 10}
                    onChange={(event) => router.get(route(routeName), { ...filters, per_page: event.target.value, page: 1 }, { preserveState: true, replace: true })}
                    className="w-24"
                >
                    {[10, 25, 50].map((value) => <option key={value} value={value}>{value}</option>)}
                </Select>
            </div>
        </div>
    );
}
