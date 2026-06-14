import FormField from '@/Components/FormField';
import { Checkbox } from '@/Components/ui/checkbox';
import { Label } from '@/Components/ui/label';
import FormShell from '@/Pages/MasterData/Shared/FormShell';
import { useForm } from '@inertiajs/react';

export default function Form({ managedUser, roles }) {
    const isEdit = Boolean(managedUser);
    const { data, setData, post, put, processing, errors } = useForm({
        name: managedUser?.name ?? '',
        email: managedUser?.email ?? '',
        password: '',
        password_confirmation: '',
        is_active: managedUser?.is_active ?? true,
        roles: managedUser?.roles ?? [],
    });
    const toggleRole = (roleName) => {
        setData('roles', data.roles.includes(roleName)
            ? data.roles.filter((role) => role !== roleName)
            : [...data.roles, roleName]);
    };
    const submit = (event) => {
        event.preventDefault();
        isEdit ? put(route('users.update', managedUser.id)) : post(route('users.store'));
    };
    return (
        <FormShell title={isEdit ? 'Edit User' : 'Tambah User'} description="Atur akun dan jabatan user." backRoute="users.index" processing={processing} onSubmit={submit}>
            <FormField label="Nama" name="name" value={data.name} onChange={(e) => setData('name', e.target.value)} error={errors.name} />
            <FormField label="Email" name="email" type="email" value={data.email} onChange={(e) => setData('email', e.target.value)} error={errors.email} />
            <FormField label={isEdit ? 'Password Baru' : 'Password'} name="password" type="password" value={data.password} onChange={(e) => setData('password', e.target.value)} error={errors.password} />
            <FormField label="Konfirmasi Password" name="password_confirmation" type="password" value={data.password_confirmation} onChange={(e) => setData('password_confirmation', e.target.value)} error={errors.password_confirmation} />
            <div className="md:col-span-2">
                <Label>Jabatan</Label>
                <div className="mt-2 grid gap-3 rounded-md border border-slate-200 p-4 dark:border-slate-800 sm:grid-cols-2 md:grid-cols-3">
                    {roles.map((role) => (
                        <label key={role.id} className="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-300">
                            <Checkbox checked={data.roles.includes(role.name)} onChange={() => toggleRole(role.name)} />
                            {role.name}
                        </label>
                    ))}
                </div>
            </div>
            <div className="flex items-center gap-2">
                <Checkbox checked={data.is_active} onChange={(e) => setData('is_active', e.target.checked)} />
                <Label>Aktif</Label>
            </div>
        </FormShell>
    );
}
