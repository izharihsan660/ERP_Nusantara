<?php

namespace App\Http\Requests\PermintaanDana;

use App\Enums\KategoriPD;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'tgl_pd' => ['required', 'date'],
            'kategori' => ['required', Rule::enum(KategoriPD::class)],
            'nominal' => ['required', 'numeric', 'min:1'],
            'keterangan' => ['required', 'string'],
            'referensi_dokumen' => ['nullable', 'string', 'max:100'],
            'submit' => ['nullable', 'boolean'],
        ];
    }
}
