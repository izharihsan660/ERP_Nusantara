import AppLayout from '@/Layouts/AppLayout';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Head, router, useForm } from '@inertiajs/react';
import { useState } from 'react';

export default function Index({ settings }) {
    const { data, setData, post, processing } = useForm({ settings: [] });
    const [testEmail, setTestEmail] = useState('');

    return (
        <AppLayout>
            <Head title="Pengaturan Sistem" />
            <div className="space-y-6">
                <div>
                    <h2 className="text-2xl font-semibold">Pengaturan Sistem</h2>
                    <p className="mt-1 text-sm text-slate-600">Konfigurasi email SMTP, approval, dan informasi perusahaan</p>
                </div>
                <form onSubmit={(e) => { e.preventDefault(); post(route('settings.update')); }} className="space-y-6">
                    {Object.keys(settings).map((group) => (
                        <div key={group} className="rounded-md border border-slate-200 p-4 dark:border-slate-800">
                            <h3 className="mb-4 text-lg font-semibold capitalize">{group}</h3>
                            <div className="space-y-4">
                                {settings[group].map((setting) => (
                                    <div key={setting.key}>
                                        <Label>{setting.label}</Label>
                                        <Input
                                            type={setting.key.includes('password') ? 'password' : 'text'}
                                            defaultValue={setting.value}
                                            onChange={(e) => {
                                                const updated = [...(data.settings || [])];
                                                const index = updated.findIndex((s) => s.key === setting.key);
                                                if (index >= 0) updated[index].value = e.target.value;
                                                else updated.push({ key: setting.key, value: e.target.value });
                                                setData('settings', updated);
                                            }}
                                        />
                                    </div>
                                ))}
                            </div>
                        </div>
                    ))}
                    <div className="flex items-center gap-3">
                        <Button type="submit" disabled={processing}>Simpan Konfigurasi</Button>
                        <Input type="email" placeholder="test@example.com" value={testEmail} onChange={(e) => setTestEmail(e.target.value)} className="w-64" />
                        <Button type="button" variant="outline" onClick={() => router.post(route('settings.test-email'), { email: testEmail })}>Test Kirim Email</Button>
                    </div>
                </form>
            </div>
        </AppLayout>
    );
}
