<?php

namespace App\Http\Requests\WipOrder;

use Illuminate\Foundation\Http\FormRequest;

class VoidWipOrderRequest extends FormRequest
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
            'alasan_void' => ['required', 'string', 'max:1000'],
        ];
    }
}
