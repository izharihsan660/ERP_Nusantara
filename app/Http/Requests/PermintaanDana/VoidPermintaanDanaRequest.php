<?php

namespace App\Http\Requests\PermintaanDana;

use Illuminate\Foundation\Http\FormRequest;

class VoidPermintaanDanaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'alasan_void' => ['required', 'string', 'min:10'],
        ];
    }
}
