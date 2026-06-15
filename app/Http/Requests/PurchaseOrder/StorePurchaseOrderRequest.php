<?php

namespace App\Http\Requests\PurchaseOrder;

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
            'vendor_id' => ['required', Rule::exists('vendors', 'id')],
            'tgl_po' => ['required', 'date'],
            'no_pr_customer' => ['nullable', 'string', 'max:50'],
            'no_po_customer' => ['nullable', 'string', 'max:50'],
            'catatan' => ['nullable', 'string'],
            'submit' => ['nullable', 'boolean'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.katalog_id' => ['nullable', Rule::exists('katalog', 'id')],
            'items.*.deskripsi' => ['required', 'string', 'max:200'],
            'items.*.qty' => ['required', 'integer', 'min:1'],
            'items.*.satuan' => ['required', 'string', 'max:20'],
            'items.*.harga_satuan' => ['required', 'numeric', 'min:0'],
        ];
    }
}
