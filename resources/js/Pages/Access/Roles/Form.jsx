import FormField from '@/Components/FormField';
import PageHeader from '@/Components/PageHeader';
import { Button } from '@/Components/ui/button';
import { Checkbox } from '@/Components/ui/checkbox';
import AppLayout from '@/Layouts/AppLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import { Save } from 'lucide-react';

export default function Form({ role, permissionGroups }) {
    const isEdit = Boolean(role);
    const { data, setData, post, put, processing, errors } = useForm({
        name: role?.name ?? '',
        permissions: role?.permissions ?? [],
    });
    const togglePermission = (permission) => {
        setData('permissions', data.permissions.includes(permission)
            ? data.permissions.filter((item) => item !== permission)
            : [...data.permissions, permission]);
    };
    const toggleModule = (permissions) => {
        const allSelected = permissions.every((permission) => data.permissions.includes(permission));
        setData('permissions', allSelected
            ? data.permissions.filter((permission) => !permissions.includes(permission))
            : [...new Set([...data.permissions, ...permissions])]);
    };
    const submit = (event) => {
        event.preventDefault();
        isEdit ? put(route('roles.update', role.id)) : post(route('roles.store'));
    };
    return (
        <AppLayout title={isEdit ? 'Edit Jabatan' : 'Tambah Jabatan'}>
            <Head title={isEdit ? 'Edit Jabatan' : 'Tambah Jabatan'} />
            <PageHeader title={isEdit ? 'Edit Jabatan' : 'Tambah Jabatan'} description="Centang permission yang boleh diakses jabatan ini." actions={<Button asChild variant="outline"><Link href={route('roles.index')}>Kembali</Link></Button>} />
            <form onSubmit={submit} className="space-y-6">
                <div className="rounded-lg border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                    <FormField label="Nama Jabatan" name="name" value={data.name} onChange={(e) => setData('name', e.target.value)} error={errors.name} />
                </div>
                <div className="grid gap-4 lg:grid-cols-2">
                    {permissionGroups.map((group) => {
                        const permissions = group.permissions;
                        const allSelected = permissions.every((permission) => data.permissions.includes(permission));
                        return (
                            <section key={group.module} className="rounded-lg border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                                <div className="mb-4 flex items-center justify-between gap-3">
                                    <h2 className="font-semibold text-slate-950 dark:text-white">{group.module}</h2>
                                    <label className="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-300">
                                        <Checkbox checked={allSelected} onChange={() => toggleModule(permissions)} />
                                        Semua Akses
                                    </label>
                                </div>
                                <div className="grid gap-3 sm:grid-cols-2">
                                    {permissions.map((permission) => (
                                        <label key={permission} className="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-300">
                                            <Checkbox checked={data.permissions.includes(permission)} onChange={() => togglePermission(permission)} />
                                            {permission}
                                        </label>
                                    ))}
                                </div>
                            </section>
                        );
                    })}
                </div>
                <div className="flex justify-end">
                    <Button type="submit" disabled={processing}><Save className="h-4 w-4" />Simpan</Button>
                </div>
            </form>
        </AppLayout>
    );
}
