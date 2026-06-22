<?php

namespace App\Http\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('ubah_jabatan') ?? false;
    }

    public function rules(): array
    {
        return [
            'settings' => ['required', 'array'],
            'settings.*.key' => ['required', 'string', Rule::in([
                'mail_host',
                'mail_port',
                'mail_username',
                'mail_password',
                'mail_encryption',
                'mail_from_address',
                'mail_from_name',
                'approval_email_quotation',
                'approval_email_po_naj',
                'approval_email_pd',
                'company_name',
                'company_address',
                'company_phone',
                'company_email',
                'company_website',
            ])],
            'settings.*.value' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
