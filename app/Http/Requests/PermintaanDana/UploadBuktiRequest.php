<?php

namespace App\Http\Requests\PermintaanDana;

use Illuminate\Foundation\Http\FormRequest;

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
            'file_bukti' => ['required', 'file', 'mimes:pdf,jpg,png', 'max:10240'],
        ];
    }
}
