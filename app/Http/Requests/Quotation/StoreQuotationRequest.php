<?php

namespace App\Http\Requests\Quotation;

use App\Http\Requests\Concerns\NormalizesMoneyInput;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreQuotationRequest extends FormRequest
{
    use NormalizesMoneyInput;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->normalizeMoneyInput(['items.*.harga_satuan']);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'tgl_quotation' => ['required', 'date'],
            'customer_id' => ['required', Rule::exists('customers', 'id')],
            'template_id' => ['required', Rule::exists('document_templates', 'id')],
            'catatan' => ['nullable', 'string'],
            'submit' => ['nullable', 'boolean'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.katalog_id' => ['required', Rule::exists('katalog', 'id')],
            'items.*.qty' => ['required', 'integer', 'min:1'],
            'items.*.harga_satuan' => ['required', 'numeric', 'min:0'],
        ];
    }
}
