<?php

namespace App\Http\Requests\Invoice;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInvoiceRequest extends FormRequest
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
            'no_faktur_pajak' => ['nullable', 'string', 'max:50'],
            'metode_pembayaran' => ['required', Rule::in(['COD', 'CBD', 'TOP'])],
            'top_hari' => ['required_if:metode_pembayaran,TOP', 'nullable', 'integer', 'min:1', 'max:365'],
            'tgl_dokumen' => ['required', 'date'],
        ];
    }
}
