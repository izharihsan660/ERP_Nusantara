<?php

namespace App\Http\Requests\Customer;

use App\Http\Requests\Concerns\SanitizesRequestInput;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCustomerRequest extends FormRequest
{
    use SanitizesRequestInput;

    public function authorize(): bool
    {
        return $this->user()?->can('Customer tambah') ?? false;
    }

    public function rules(): array
    {
        return [
            'kode_customer' => ['required', 'string', 'max:50', 'unique:customers,kode_customer'],
            'nama_customer' => ['required', 'string', 'max:255'],
            'alamat' => ['nullable', 'string'],
            'kota' => ['nullable', 'string', 'max:100'],
            'npwp' => ['nullable', 'string', 'max:50'],
            'pic_name' => ['nullable', 'string', 'max:255'],
            'pic_email' => ['nullable', 'email', 'max:255'],
            'pic_phone' => ['nullable', 'string', 'max:50'],
            'template_quotation_id' => ['nullable', Rule::exists('document_templates', 'id')],
            'template_spb_id' => ['nullable', Rule::exists('document_templates', 'id')],
            'is_active' => ['boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge($this->sanitizedStrings([
            'kode_customer',
            'nama_customer',
            'alamat',
            'kota',
            'npwp',
            'pic_name',
            'pic_email',
            'pic_phone',
        ]));
    }
}
