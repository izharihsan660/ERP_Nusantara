<?php

namespace App\Http\Requests\PermintaanDana;

use App\Http\Requests\Concerns\NormalizesMoneyInput;
use Illuminate\Foundation\Http\FormRequest;

class StorePermintaanDanaRequest extends FormRequest
{
    use NormalizesMoneyInput;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->normalizeMoneyInput(['nominal']);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'tgl_pd' => ['required', 'date'],
            'tujuan' => ['required', 'string', 'max:150'],
            'rekening_tujuan' => ['required', 'string', 'max:200'],
            'bank_tujuan' => ['nullable', 'string', 'max:100'],
            'plan_pembayaran' => ['required', 'date'],
            'nominal' => ['required', 'numeric', 'min:1'],
            'keterangan' => ['required', 'string'],
            'referensi_dokumen' => ['nullable', 'string', 'max:100'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.no_po' => ['nullable', 'string', 'max:50'],
            'items.*.no_part' => ['nullable', 'string', 'max:50'],
            'items.*.description' => ['required', 'string', 'max:200'],
            'items.*.qty' => ['required', 'integer', 'min:1'],
            'items.*.harga' => ['required', 'numeric', 'min:0'],
            'attachments' => ['nullable', 'array', 'max:2'],
            'attachments.*' => ['file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'submit' => ['nullable', 'boolean'],
        ];
    }
}
