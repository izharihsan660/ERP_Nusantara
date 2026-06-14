<?php

namespace App\Http\Requests\User;

use App\Http\Requests\Concerns\SanitizesRequestInput;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class StoreUserRequest extends FormRequest
{
    use SanitizesRequestInput;

    public function authorize(): bool
    {
        return $this->user()?->can('User tambah') ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'is_active' => ['boolean'],
            'roles' => ['array'],
            'roles.*' => ['string', 'exists:roles,name'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge($this->sanitizedStrings(['name', 'email']));
    }
}
