<?php

namespace App\Http\Requests\Vendor;

use App\Enums\VendorType;
use App\Http\Requests\Concerns\SanitizesRequestInput;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateVendorRequest extends FormRequest
{
    use SanitizesRequestInput;

    public function authorize(): bool
    {
        return $this->user()?->can('ubah_vendor') ?? false;
    }

    public function rules(): array
    {
        return [
            'tipe_vendor' => ['required', Rule::enum(VendorType::class)],
            'nama_vendor' => ['required', 'string', 'max:255'],
            'alamat' => ['nullable', 'string'],
            'pic_name' => ['nullable', 'string', 'max:255'],
            'pic_email' => ['nullable', 'email', 'max:255'],
            'rekening' => ['nullable', 'string', 'max:255'],
            'keterangan' => ['nullable', 'string'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge($this->sanitizedStrings([
            'nama_vendor',
            'alamat',
            'pic_name',
            'pic_email',
            'rekening',
            'keterangan',
        ]));
    }
}
