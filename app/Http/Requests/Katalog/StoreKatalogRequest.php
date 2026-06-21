<?php

namespace App\Http\Requests\Katalog;

use App\Http\Requests\Concerns\NormalizesMoneyInput;
use App\Http\Requests\Concerns\SanitizesRequestInput;
use Illuminate\Foundation\Http\FormRequest;

class StoreKatalogRequest extends FormRequest
{
    use NormalizesMoneyInput;
    use SanitizesRequestInput;

    public function authorize(): bool
    {
        return $this->user()?->can('tambah_katalog') ?? false;
    }

    public function rules(): array
    {
        return [
            'part_no' => ['required', 'string', 'max:100', 'unique:katalog,part_no'],
            'nama_barang' => ['required', 'string', 'max:255'],
            'spesifikasi' => ['nullable', 'string'],
            'satuan' => ['nullable', 'string', 'max:50'],
            'hpp' => ['required', 'numeric', 'min:0', 'max:999999999999.99'],
            'harga_jual_default' => ['required', 'numeric', 'min:0', 'max:999999999999.99'],
            'kategori' => ['nullable', 'string', 'max:100'],
            'is_active' => ['boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge($this->sanitizedStrings([
            'part_no',
            'nama_barang',
            'spesifikasi',
            'satuan',
            'kategori',
        ]));

        $this->normalizeMoneyInput(['hpp', 'harga_jual_default']);
    }
}
