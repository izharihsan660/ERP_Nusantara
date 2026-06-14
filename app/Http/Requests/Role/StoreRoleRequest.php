<?php

namespace App\Http\Requests\Role;

use App\Http\Requests\Concerns\SanitizesRequestInput;
use Illuminate\Foundation\Http\FormRequest;

class StoreRoleRequest extends FormRequest
{
    use SanitizesRequestInput;

    public function authorize(): bool
    {
        return $this->user()?->can('Jabatan tambah') ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:roles,name'],
            'permissions' => ['array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge($this->sanitizedStrings(['name']));
    }
}
