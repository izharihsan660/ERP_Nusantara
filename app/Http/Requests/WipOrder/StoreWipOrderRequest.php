<?php

namespace App\Http\Requests\WipOrder;

use App\Enums\TipeOrder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreWipOrderRequest extends FormRequest
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
            'no_wip' => ['required', 'string', 'max:30'],
            'tipe_order' => ['required', Rule::enum(TipeOrder::class)],
            'nama_ekspedisi' => ['nullable', 'required_if:tipe_order,VOR', 'string', 'max:100'],
        ];
    }
}
