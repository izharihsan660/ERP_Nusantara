<?php

namespace App\Http\Requests\Katalog;

use Illuminate\Foundation\Http\FormRequest;

class ImportKatalogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('Katalog import') ?? false;
    }

    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:5120'],
        ];
    }
}
