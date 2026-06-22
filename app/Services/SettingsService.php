<?php

namespace App\Services;

use App\Models\AppSetting;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;

class SettingsService
{
    private const PASSWORD_PLACEHOLDER = '********';

    private const DEFINITIONS = [
        ['key' => 'mail_host', 'label' => 'Host SMTP', 'group' => 'smtp', 'default' => ''],
        ['key' => 'mail_port', 'label' => 'Port SMTP', 'group' => 'smtp', 'default' => '587'],
        ['key' => 'mail_username', 'label' => 'Username SMTP', 'group' => 'smtp', 'default' => ''],
        ['key' => 'mail_password', 'label' => 'Password SMTP', 'group' => 'smtp', 'default' => ''],
        ['key' => 'mail_encryption', 'label' => 'Encryption', 'group' => 'smtp', 'default' => 'tls'],
        ['key' => 'mail_from_address', 'label' => 'From Address', 'group' => 'smtp', 'default' => ''],
        ['key' => 'mail_from_name', 'label' => 'From Name', 'group' => 'smtp', 'default' => 'PT. Nusantara Abadi Jaya'],
        ['key' => 'approval_email_quotation', 'label' => 'Email Approval Quotation', 'group' => 'approval', 'default' => ''],
        ['key' => 'approval_email_po_naj', 'label' => 'Email Approval Purchase Order', 'group' => 'approval', 'default' => ''],
        ['key' => 'approval_email_pd', 'label' => 'Email Approval Permintaan Dana', 'group' => 'approval', 'default' => ''],
        ['key' => 'company_name', 'label' => 'Nama Perusahaan', 'group' => 'company', 'default' => 'PT. Nusantara Abadi Jaya'],
        ['key' => 'company_address', 'label' => 'Alamat', 'group' => 'company', 'default' => ''],
        ['key' => 'company_phone', 'label' => 'Telepon', 'group' => 'company', 'default' => ''],
        ['key' => 'company_email', 'label' => 'Email', 'group' => 'company', 'default' => ''],
        ['key' => 'company_website', 'label' => 'Website', 'group' => 'company', 'default' => ''],
    ];

    public function groupedSettings(): array
    {
        $this->ensureDefinitionsExist();

        $keys = Arr::pluck(self::DEFINITIONS, 'key');

        return AppSetting::query()
            ->whereIn('key', Arr::pluck(self::DEFINITIONS, 'key'))
            ->get()
            ->sortBy(fn (AppSetting $setting): int => array_search($setting->key, $keys, true))
            ->map(fn (AppSetting $setting): array => [
                'key' => $setting->key,
                'value' => $this->displayValue($setting),
                'label' => $this->labelFor($setting),
                'group' => $this->groupFor($setting),
            ])
            ->groupBy('group')
            ->map(fn ($settings) => $settings->values()->all())
            ->all();
    }

    public function update(array $settings): void
    {
        $this->ensureDefinitionsExist();

        foreach ($settings as $item) {
            $value = $item['value'] ?? null;

            if ($item['key'] === 'mail_password') {
                if ($value === self::PASSWORD_PLACEHOLDER) {
                    continue;
                }

                $value = filled($value) ? Crypt::encryptString($value) : null;
            }

            AppSetting::query()->where('key', $item['key'])->update(['value' => $value]);
        }

        Cache::forget('app_settings_mail');
        Artisan::call('config:clear');
    }

    public function sendTestEmail(string $email): void
    {
        $this->applyMailConfig();

        Mail::raw('Ini adalah email test dari ERP PT. Nusantara Abadi Jaya.', function ($message) use ($email): void {
            $message->to($email)->subject('Test Email - ERP NAJ');
        });
    }

    private function ensureDefinitionsExist(): void
    {
        foreach (self::DEFINITIONS as $definition) {
            AppSetting::query()->firstOrCreate(
                ['key' => $definition['key']],
                [
                    'value' => $definition['default'],
                    'label' => $definition['label'],
                    'group' => $definition['group'],
                ]
            );
        }
    }

    private function applyMailConfig(): void
    {
        $password = AppSetting::value('mail_password', '');
        $fromAddress = AppSetting::value('mail_from_address') ?: AppSetting::value('mail_username', config('mail.from.address'));

        config([
            'mail.mailers.smtp.host' => AppSetting::value('mail_host', config('mail.mailers.smtp.host')),
            'mail.mailers.smtp.port' => AppSetting::value('mail_port', config('mail.mailers.smtp.port')),
            'mail.mailers.smtp.username' => AppSetting::value('mail_username', ''),
            'mail.mailers.smtp.password' => $password,
            'mail.mailers.smtp.encryption' => AppSetting::value('mail_encryption', 'tls'),
            'mail.from.address' => $fromAddress,
            'mail.from.name' => AppSetting::value('mail_from_name', 'PT. Nusantara Abadi Jaya'),
        ]);
    }

    private function displayValue(AppSetting $setting): ?string
    {
        if ($setting->key === 'mail_password' && filled($setting->value)) {
            return self::PASSWORD_PLACEHOLDER;
        }

        return $setting->value;
    }

    private function labelFor(AppSetting $setting): string
    {
        return $this->definitionFor($setting->key)['label'] ?? $setting->label;
    }

    private function groupFor(AppSetting $setting): string
    {
        return $this->definitionFor($setting->key)['group'] ?? $setting->group;
    }

    private function definitionFor(string $key): ?array
    {
        return collect(self::DEFINITIONS)->firstWhere('key', $key);
    }
}
