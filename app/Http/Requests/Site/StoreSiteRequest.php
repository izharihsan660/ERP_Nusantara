<?php

namespace App\Http\Requests\Site;

use App\Http\Requests\Concerns\SanitizesRequestInput;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSiteRequest extends FormRequest
{
    use SanitizesRequestInput;

    public function authorize(): bool
    {
        return $this->user()?->can('tambah_site') ?? false;
    }

    public function rules(): array
    {
        return [
            'nama_site' => ['required', 'string', 'max:255'],
            'alamat' => ['nullable', 'string'],
            'customer_id' => ['required', Rule::exists('customers', 'id')],
            'keterangan' => ['nullable', 'string'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge($this->sanitizedStrings([
            'nama_site',
            'alamat',
            'keterangan',
        ]));
    }
}
