<?php

namespace App\Http\Requests\Invoice;

use Illuminate\Foundation\Http\FormRequest;

class UploadTtdRequest extends FormRequest
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
            'file_spb' => ['required', 'file', 'mimes:pdf,jpg,png', 'max:10240'],
            'file_invoice' => ['required', 'file', 'mimes:pdf,jpg,png', 'max:10240'],
            'file_tanda_terima' => ['required', 'file', 'mimes:pdf,jpg,png', 'max:10240'],
        ];
    }
}
