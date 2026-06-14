<?php

namespace App\Http\Requests\PurchaseOrder;

use App\Enums\MetodePembayaran;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePurchaseOrderRequest extends FormRequest
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
            'no_po_customer' => ['required', 'string', 'max:50'],
            'no_pr_customer' => ['nullable', 'string', 'max:50'],
            'tgl_po' => ['required', 'date'],
            'metode_pembayaran' => ['required', Rule::enum(MetodePembayaran::class)],
            'top_hari' => ['nullable', 'required_if:metode_pembayaran,TOP', 'integer', 'min:1'],
        ];
    }
}
