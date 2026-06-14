<?php

namespace App\Http\Requests\User;

use App\Http\Requests\Concerns\SanitizesRequestInput;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateUserRequest extends FormRequest
{
    use SanitizesRequestInput;

    public function authorize(): bool
    {
        return $this->user()?->can('User ubah') ?? false;
    }

    public function rules(): array
    {
        $userId = $this->route('user')?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'password' => ['nullable', 'confirmed', Password::defaults()],
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
