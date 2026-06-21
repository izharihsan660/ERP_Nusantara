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
        <div className="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-950">
            <div className="flex flex-col gap-3 border-b border-slate-200 p-4 dark:border-slate-800 lg:flex-row lg:items-center lg:justify-between">
                <form onSubmit={submit} className="flex w-full flex-col gap-2 sm:flex-row lg:max-w-md">
                    <div className="relative flex-1">
                        <Search className="pointer-events-none absolute left-3 top-2.5 h-4 w-4 text-slate-400" />
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
                <table className="min-w-full table-fixed divide-y divide-slate-200 text-sm dark:divide-slate-800">
                    <thead className="bg-slate-50 dark:bg-slate-900">
                        <tr>
                            {columns.map((column) => (
                                <th key={column.key} className={`${column.width ?? 'w-48'} px-4 py-3 text-left font-medium text-slate-600 dark:text-slate-300`}>
                                    {column.sortable ? (
                                        <button type="button" onClick={() => sortBy(column.key)} className="inline-flex items-center gap-1">
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
                    <tbody className="divide-y divide-slate-100 dark:divide-slate-900">
                        {rows.length === 0 && (
                            <tr>
                                <td colSpan={columns.length} className="px-4 py-10 text-center text-slate-500">
                                    {emptyText}
                                </td>
                            </tr>
                        )}
                        {rows.map((row) => (
                            <tr key={row.id} className="hover:bg-slate-50 dark:hover:bg-slate-900/60">
                                {columns.map((column) => (
                                    <td key={column.key} className="px-4 py-3 text-slate-700 dark:text-slate-200">
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

            <div className="flex flex-col gap-3 border-t border-slate-200 p-4 text-sm text-slate-500 dark:border-slate-800 sm:flex-row sm:items-center sm:justify-between">
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
                            className={`rounded-md px-3 py-1.5 ${link.active ? 'bg-slate-950 text-white dark:bg-white dark:text-slate-950' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800'} ${!link.url ? 'pointer-events-none opacity-40' : ''}`}
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
