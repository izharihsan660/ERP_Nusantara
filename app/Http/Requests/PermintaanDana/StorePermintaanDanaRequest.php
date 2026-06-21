<?php

namespace App\Http\Requests\PermintaanDana;

use App\Enums\KategoriPD;
use App\Http\Requests\Concerns\NormalizesMoneyInput;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'kategori' => ['required', Rule::enum(KategoriPD::class)],
            'nominal' => ['required', 'numeric', 'min:1'],
            'keterangan' => ['required', 'string'],
            'referensi_dokumen' => ['nullable', 'string', 'max:100'],
            'submit' => ['nullable', 'boolean'],
        ];
    }
}
