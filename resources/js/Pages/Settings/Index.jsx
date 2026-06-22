import PageHeader from '@/Components/PageHeader';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Select } from '@/Components/ui/select';
import { Textarea } from '@/Components/ui/textarea';
import AppLayout from '@/Layouts/AppLayout';
import { Head, router, useForm } from '@inertiajs/react';
import { Building2, MailCheck, Save, Send, Settings as SettingsIcon } from 'lucide-react';
import { useMemo, useState } from 'react';

const sections = [
    {
        title: 'Konfigurasi Email SMTP',
        description: 'Pengaturan server email untuk notifikasi sistem.',
        icon: SettingsIcon,
        fields: [
            { key: 'mail_host', label: 'Host', type: 'text', placeholder: 'smtp.example.com' },
            { key: 'mail_port', label: 'Port', type: 'number', placeholder: '587' },
            { key: 'mail_username', label: 'Username', type: 'text', placeholder: 'username SMTP' },
            { key: 'mail_password', label: 'Password', type: 'password', placeholder: 'Password SMTP' },
            { key: 'mail_encryption', label: 'Encryption', type: 'select', options: ['tls', 'ssl'] },
            { key: 'mail_from_address', label: 'From Address', type: 'email', placeholder: 'noreply@example.com' },
            { key: 'mail_from_name', label: 'From Name', type: 'text', placeholder: 'PT. Nusantara Abadi Jaya' },
        ],
    },
    {
        title: 'Email Approval',
        description: 'Email tujuan notifikasi approval untuk masing-masing dokumen.',
        icon: MailCheck,
        fields: [
            { key: 'approval_email_quotation', label: 'email_approval_quotation', type: 'email', placeholder: 'manager@example.com' },
            { key: 'approval_email_po_naj', label: 'email_approval_purchase_order', type: 'email', placeholder: 'manager@example.com' },
            { key: 'approval_email_pd', label: 'email_approval_permintaan_dana', type: 'email', placeholder: 'manager@example.com' },
        ],
    },
    {
        title: 'Informasi Perusahaan',
        description: 'Identitas perusahaan yang dipakai di dokumen dan email.',
        icon: Building2,
        fields: [
            { key: 'company_name', label: 'nama_perusahaan', type: 'text', placeholder: 'PT. Nusantara Abadi Jaya' },
            { key: 'company_address', label: 'alamat', type: 'textarea', placeholder: 'Alamat perusahaan', span: true },
            { key: 'company_phone', label: 'telepon', type: 'text', placeholder: 'Nomor telepon' },
            { key: 'company_email', label: 'email', type: 'email', placeholder: 'info@example.com' },
            { key: 'company_website', label: 'website', type: 'text', placeholder: 'https://example.com' },
        ],
    },
];

const allFields = sections.flatMap((section) => section.fields);

function flattenSettings(settings) {
    const groups = Object.values(settings ?? {});
    const flattened = groups.flatMap((group) => (Array.isArray(group) ? group : Object.values(group ?? {})));

    return flattened.reduce((carry, setting) => {
        if (!setting?.key) {
            return carry;
        }

        return { ...carry, [setting.key]: setting };
    }, {});
}

function initialFormSettings(settingMap) {
    return allFields.map((field) => ({
        key: field.key,
        value: settingMap[field.key]?.value ?? '',
    }));
}

export default function Index({ settings }) {
    const settingMap = useMemo(() => flattenSettings(settings), [settings]);
    const { data, setData, post, processing, errors } = useForm({ settings: initialFormSettings(settingMap) });
    const [testEmail, setTestEmail] = useState('');

    const valueFor = (key) => data.settings.find((setting) => setting.key === key)?.value ?? '';
    const setValue = (key, value) => {
        const nextSettings = data.settings.some((setting) => setting.key === key)
            ? data.settings.map((setting) => (setting.key === key ? { ...setting, value } : setting))
            : [...data.settings, { key, value }];

        setData('settings', nextSettings);
    };

    const errorFor = (key) => {
        const index = data.settings.findIndex((setting) => setting.key === key);

        return index >= 0 ? errors[`settings.${index}.value`] : null;
    };

    const submit = (event) => {
        event.preventDefault();
        post(route('settings.update'), { preserveScroll: true });
    };

    const sendTestEmail = () => {
        router.post(route('settings.test-email'), { email: testEmail }, { preserveScroll: true });
    };

    const renderField = (field) => {
        const fieldError = errorFor(field.key);
        const inputClassName = fieldError ? 'border-red-500 focus:border-red-500 focus:ring-red-500/20' : '';

        if (field.type === 'select') {
            return (
                <Select
                    value={valueFor(field.key)}
                    onChange={(event) => setValue(field.key, event.target.value)}
                    className={inputClassName}
                >
                    {field.options.map((option) => (
                        <option key={option} value={option}>{option}</option>
                    ))}
                </Select>
            );
        }

        if (field.type === 'textarea') {
            return (
                <Textarea
                    value={valueFor(field.key)}
                    onChange={(event) => setValue(field.key, event.target.value)}
                    placeholder={field.placeholder}
                    className={inputClassName}
                />
            );
        }

        return (
            <Input
                type={field.type}
                value={valueFor(field.key)}
                onChange={(event) => setValue(field.key, event.target.value)}
                placeholder={field.placeholder}
                className={inputClassName}
            />
        );
    };

    return (
        <AppLayout title="Pengaturan Sistem">
            <Head title="Pengaturan Sistem" />
            <PageHeader
                title="Pengaturan Sistem"
                description="Konfigurasi SMTP, email approval, dan informasi perusahaan."
            />

            <form onSubmit={submit} className="space-y-6">
                {sections.map((section) => {
                    const Icon = section.icon;

                    return (
                        <section key={section.title} className="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] p-6 shadow-sm">
                            <div className="flex items-start gap-3">
                                <div className="rounded-lg bg-[hsl(var(--muted))] p-2 text-[hsl(var(--muted-foreground))]">
                                    <Icon className="h-5 w-5" />
                                </div>
                                <div>
                                    <h2 className="text-base font-semibold text-[hsl(var(--foreground))]">{section.title}</h2>
                                    <p className="mt-1 text-sm text-[hsl(var(--muted-foreground))]">{section.description}</p>
                                </div>
                            </div>

                            <div className="my-4 border-t border-[hsl(var(--border))]" />

                            <div className="grid gap-4 md:grid-cols-2">
                                {section.fields.map((field) => (
                                    <label key={field.key} className={field.span ? 'space-y-1 md:col-span-2' : 'space-y-1'}>
                                        <Label>{field.label}</Label>
                                        {renderField(field)}
                                        {errorFor(field.key) && <p className="text-sm text-red-600">{errorFor(field.key)}</p>}
                                    </label>
                                ))}
                            </div>

                            {section.title === 'Konfigurasi Email SMTP' && (
                                <div className="mt-4 flex flex-col gap-2 border-t border-[hsl(var(--border))] pt-4 sm:flex-row">
                                    <Input
                                        type="email"
                                        placeholder="Email tujuan test"
                                        value={testEmail}
                                        onChange={(event) => setTestEmail(event.target.value)}
                                        className="sm:max-w-xs"
                                    />
                                    <Button type="button" variant="outline" onClick={sendTestEmail}>
                                        <Send className="h-4 w-4" />Test Kirim Email
                                    </Button>
                                </div>
                            )}
                        </section>
                    );
                })}

                <div className="flex justify-end">
                    <Button type="submit" disabled={processing}><Save className="h-4 w-4" />Simpan Semua</Button>
                </div>
            </form>
        </AppLayout>
    );
}
