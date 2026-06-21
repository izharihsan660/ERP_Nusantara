import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Select } from '@/Components/ui/select';
import { router } from '@inertiajs/react';
import { Filter, RotateCcw, Search } from 'lucide-react';
import { useState } from 'react';

function clean(values) {
    return Object.fromEntries(Object.entries(values).filter(([, value]) => value !== null && value !== undefined && value !== ''));
}

export default function FilterSection({ routeName, filters = {}, fields = [] }) {
    const [open, setOpen] = useState(true);
    const [values, setValues] = useState(filters);

    const setValue = (key, value) => setValues((current) => ({ ...current, [key]: value }));

    const submit = (event) => {
        event.preventDefault();
        router.get(route(routeName), { ...clean(values), page: 1 }, { preserveState: true, replace: true });
    };

    const reset = () => {
        router.get(route(routeName), values.tab ? { tab: values.tab } : {}, { preserveState: true, replace: true });
    };

    return (
        <div className="mb-6 rounded-lg border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-950">
            <button
                type="button"
                onClick={() => setOpen((current) => !current)}
                className="flex w-full items-center justify-between px-4 py-3 text-left text-sm font-semibold text-slate-800 dark:text-slate-100"
            >
                <span className="inline-flex items-center gap-2"><Filter className="h-4 w-4" />Filter</span>
                <span className="text-xs text-slate-500">{open ? 'Tutup' : 'Buka'}</span>
            </button>
            {open && (
                <form onSubmit={submit} className="grid gap-3 border-t border-slate-200 p-4 dark:border-slate-800 md:grid-cols-2 xl:grid-cols-4">
                    <label className="space-y-1">
                        <span className="text-xs font-medium text-slate-500">Cari</span>
                        <div className="relative">
                            <Search className="pointer-events-none absolute left-3 top-2.5 h-4 w-4 text-slate-400" />
                            <Input value={values.search ?? ''} onChange={(event) => setValue('search', event.target.value)} className="pl-9" placeholder="Cari dokumen..." />
                        </div>
                    </label>

                    {fields.map((field) => (
                        <label key={field.key} className="space-y-1">
                            <span className="text-xs font-medium text-slate-500">{field.label}</span>
                            {field.type === 'select' ? (
                                <Select value={values[field.key] ?? ''} onChange={(event) => setValue(field.key, event.target.value)}>
                                    <option value="">{field.placeholder ?? 'Semua'}</option>
                                    {field.options?.map((option) => (
                                        <option key={option.value ?? option.id} value={option.value ?? option.id}>{option.label}</option>
                                    ))}
                                </Select>
                            ) : (
                                <Input type={field.type ?? 'text'} value={values[field.key] ?? ''} onChange={(event) => setValue(field.key, event.target.value)} />
                            )}
                        </label>
                    ))}

                    <div className="flex items-end gap-2 md:col-span-2 xl:col-span-4">
                        <Button type="submit"><Filter className="h-4 w-4" />Terapkan Filter</Button>
                        <Button type="button" variant="outline" onClick={reset}><RotateCcw className="h-4 w-4" />Reset</Button>
                    </div>
                </form>
            )}
        </div>
    );
}
