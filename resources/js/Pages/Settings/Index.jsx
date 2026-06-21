import PageHeader from '@/Components/PageHeader';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import AppLayout from '@/Layouts/AppLayout';
import { Head, router, useForm } from '@inertiajs/react';
import { MailCheck, Save, Send, Settings as SettingsIcon } from 'lucide-react';
import { useMemo, useState } from 'react';

const sections = [
    {
        title: 'Konfigurasi Email SMTP',
        description: 'Pengaturan server email untuk notifikasi sistem.',
        keys: ['mail_host', 'mail_port', 'mail_username', 'mail_password', 'mail_encryption', 'mail_from_name'],
    },
    {
        title: 'Email Approval',
        description: 'Email yang akan menerima notifikasi approval untuk masing-masing dokumen.',
        keys: ['approval_email_quotation', 'approval_email_po_naj', 'approval_email_pd'],
    },
    {
        title: 'Informasi Perusahaan',
        description: 'Identitas perusahaan yang dipakai di dokumen dan email.',
        keys: ['company_name', 'company_address', 'company_phone'],
    },
];

function flattenSettings(settings) {
    return Object.values(settings ?? {}).flat().reduce((carry, setting) => ({ ...carry, [setting.key]: setting }), {});
}

export default function Index({ settings }) {
    const settingMap = useMemo(() => flattenSettings(settings), [settings]);
    const initialSettings = useMemo(() => Object.values(settingMap).map((setting) => ({ key: setting.key, value: setting.value ?? '' })), [settingMap]);
    const { data, setData, post, processing } = useForm({ settings: initialSettings });
    const [testEmail, setTestEmail] = useState('');

    const valueFor = (key) => data.settings.find((setting) => setting.key === key)?.value ?? '';
    const setValue = (key, value) => {
        setData('settings', data.settings.map((setting) => (setting.key === key ? { ...setting, value } : setting)));
    };

    return (
        <AppLayout title="Pengaturan Sistem">
            <Head title="Pengaturan Sistem" />
            <PageHeader
                title="Pengaturan Sistem"
                description="Konfigurasi SMTP, email approval, dan informasi perusahaan."
            />

            <form onSubmit={(event) => { event.preventDefault(); post(route('settings.update')); }} className="space-y-6">
                {sections.map((section) => (
                    <section key={section.title} className="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] p-6 shadow-sm">
                        <div className="flex items-start gap-3">
                            <div className="rounded-lg bg-[hsl(var(--muted))] p-2 text-[hsl(var(--muted-foreground))]">
                                {section.title === 'Email Approval' ? <MailCheck className="h-5 w-5" /> : <SettingsIcon className="h-5 w-5" />}
                            </div>
                            <div>
                                <h2 className="text-base font-semibold text-[hsl(var(--foreground))]">{section.title}</h2>
                                <p className="mt-1 text-sm text-[hsl(var(--muted-foreground))]">{section.description}</p>
                            </div>
                        </div>

                        <div className="my-4 border-t border-[hsl(var(--border))]" />

                        <div className="grid gap-4 md:grid-cols-2">
                            {section.keys.map((key) => {
                                const setting = settingMap[key];
                                if (!setting) return null;

                                return (
                                    <label key={key} className={key.includes('address') ? 'space-y-1 md:col-span-2' : 'space-y-1'}>
                                        <Label>{setting.label}{key.startsWith('approval_email') ? <span className="text-red-600"> *</span> : null}</Label>
                                        <Input
                                            type={key.includes('password') ? 'password' : key.includes('email') || key === 'mail_username' ? 'email' : 'text'}
                                            value={valueFor(key)}
                                            onChange={(event) => setValue(key, event.target.value)}
                                            placeholder={setting.label}
                                        />
                                    </label>
                                );
                            })}
                        </div>

                        {section.title === 'Konfigurasi Email SMTP' && (
                            <div className="mt-4 flex flex-col gap-2 border-t border-[hsl(var(--border))] pt-4 sm:flex-row">
                                <Input type="email" placeholder="test@example.com" value={testEmail} onChange={(event) => setTestEmail(event.target.value)} className="sm:max-w-xs" />
                                <Button type="button" variant="outline" onClick={() => router.post(route('settings.test-email'), { email: testEmail })}>
                                    <Send className="h-4 w-4" />Test Kirim Email
                                </Button>
                            </div>
                        )}
                    </section>
                ))}

                <div className="flex justify-end">
                    <Button type="submit" disabled={processing}><Save className="h-4 w-4" />Simpan Semua</Button>
                </div>
            </form>
        </AppLayout>
    );
}
