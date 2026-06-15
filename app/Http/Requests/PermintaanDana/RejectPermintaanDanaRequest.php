<?php

namespace App\Http\Requests\PermintaanDana;

use Illuminate\Foundation\Http\FormRequest;

class RejectPermintaanDanaRequest extends FormRequest
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
            'catatan_rejection' => ['required', 'string', 'min:10'],
        ];
    }
}
