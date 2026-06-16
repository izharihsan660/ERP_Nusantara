<?php

namespace App\Http\Requests\Spb;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSpbRequest extends FormRequest
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
        $customerRule = $this->routeIs('purchase-orders.spb.store') || $this->routeIs('wip-orders.spb.store')
            ? 'nullable'
            : 'required';

        return [
            'tgl_spb' => ['required', 'date'],
            'customer_id' => [$customerRule, Rule::exists('customers', 'id')],
            'site_id' => ['nullable', Rule::exists('sites', 'id')],
            'nama_ekspedisi' => ['required', 'string', 'max:100'],
            'etd' => ['nullable', 'date'],
            'eta' => ['nullable', 'date', 'after_or_equal:etd'],
            'catatan' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.part_no' => ['required', 'string', 'max:50'],
            'items.*.deskripsi' => ['required', 'string', 'max:200'],
            'items.*.qty' => ['required', 'integer', 'min:1'],
            'items.*.satuan' => ['required', 'string', 'max:20'],
            'items.*.berat' => ['nullable', 'numeric', 'min:0'],
            'items.*.volume' => ['nullable', 'numeric', 'min:0'],
            'items.*.dimensi' => ['nullable', 'string', 'max:100'],
            'items.*.sku' => ['nullable', 'string', 'max:50'],
        ];
    }
}
