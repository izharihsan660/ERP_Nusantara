<?php

namespace App\Http\Requests\PermintaanDana;

use Illuminate\Foundation\Http\FormRequest;

class StorePermintaanDanaRequest extends FormRequest
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
            'tujuan' => ['required', 'string', 'max:150'],
            'rekening_tujuan' => ['required', 'string'],
            'plan_pembayaran' => ['required', 'date'],
            'bank_tujuan' => ['nullable', 'string'],
            'keterangan' => ['nullable', 'string'],
            'referensi_dokumen' => ['nullable', 'string', 'max:100'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.no_part' => ['nullable', 'string'],
            'items.*.description' => ['required', 'string'],
            'items.*.qty' => ['required', 'integer', 'min:1'],
            'items.*.harga' => ['required', 'numeric', 'min:0'],
            'items.*.remarks' => ['nullable', 'string'],
            'attachments' => ['nullable', 'array', 'max:2'],
            'attachments.*' => ['nullable', 'file', 'mimes:jpg,png,pdf', 'max:5120'],
            'submit' => ['nullable', 'boolean'],
        ];
    }
}
