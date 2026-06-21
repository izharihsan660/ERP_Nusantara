<?php

namespace Database\Seeders;

use App\Models\AppSetting;
use Illuminate\Database\Seeder;

class AppSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            ['key' => 'mail_host', 'value' => 'smtp.office365.com', 'label' => 'SMTP Host', 'group' => 'email'],
            ['key' => 'mail_port', 'value' => '587', 'label' => 'SMTP Port', 'group' => 'email'],
            ['key' => 'mail_username', 'value' => '', 'label' => 'SMTP Username', 'group' => 'email'],
            ['key' => 'mail_password', 'value' => '', 'label' => 'SMTP Password', 'group' => 'email'],
            ['key' => 'mail_encryption', 'value' => 'tls', 'label' => 'SMTP Encryption (tls/ssl)', 'group' => 'email'],
            ['key' => 'mail_from_name', 'value' => 'PT. Nusantara Abadi Jaya', 'label' => 'Nama Pengirim Email', 'group' => 'email'],

            ['key' => 'approval_email_quotation', 'value' => '', 'label' => 'Email Approval Quotation', 'group' => 'approval'],
            ['key' => 'approval_email_po_naj', 'value' => '', 'label' => 'Email Approval Purchase Order NAJ', 'group' => 'approval'],
            ['key' => 'approval_email_pd', 'value' => '', 'label' => 'Email Approval Permintaan Dana', 'group' => 'approval'],

            ['key' => 'company_name', 'value' => 'PT. Nusantara Abadi Jaya', 'label' => 'Nama Perusahaan', 'group' => 'company'],
            ['key' => 'company_address', 'value' => 'Jl. Jend. Gatot Subroto...', 'label' => 'Alamat Perusahaan', 'group' => 'company'],
            ['key' => 'company_phone', 'value' => '', 'label' => 'Telepon Perusahaan', 'group' => 'company'],
        ];

        foreach ($settings as $setting) {
            AppSetting::query()->updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
