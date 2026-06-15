<?php

namespace App\Http\Requests\Spb;

use Illuminate\Foundation\Http\FormRequest;

class VoidSpbRequest extends FormRequest
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
            'alasan_void' => ['required', 'string', 'min:10', 'max:1000'],
        ];
    }
}
