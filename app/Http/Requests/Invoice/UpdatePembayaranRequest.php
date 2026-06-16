<?php

namespace App\Http\Requests\Invoice;

use App\Enums\InvoicePaymentDocumentType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePembayaranRequest extends FormRequest
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
            'tgl_bayar' => ['required', 'date'],
            'jumlah_bayar' => ['required', 'numeric', 'min:0'],
            'keterangan' => ['nullable', 'string', 'max:1000'],
            'documents' => ['nullable', 'array', 'max:3'],
            'documents.*.tipe_dokumen' => ['required_with:documents.*.file', Rule::enum(InvoicePaymentDocumentType::class)],
            'documents.*.file' => ['required_with:documents.*.tipe_dokumen', 'file', 'mimes:pdf,jpg,png', 'max:10240'],
        ];
    }
}
