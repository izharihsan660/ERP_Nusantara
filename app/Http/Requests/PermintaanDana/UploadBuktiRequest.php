<?php

namespace App\Http\Requests\PermintaanDana;

use App\Enums\PdDocumentKategori;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UploadBuktiRequest extends FormRequest
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
            'tgl_realisasi' => ['required', 'date'],
            'jumlah_realisasi' => ['required', 'numeric', 'min:0'],
            'documents' => ['required', 'array', 'min:1', 'max:3'],
            'documents.*.kategori' => ['required', Rule::enum(PdDocumentKategori::class)],
            'documents.*.file' => ['required', 'file', 'mimes:pdf,jpg,png', 'max:10240'],
        ];
    }
}
