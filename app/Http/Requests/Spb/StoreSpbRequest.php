<?php

namespace App\Http\Requests\Spb;

use Illuminate\Contracts\Validation\Validator;
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
            'items.*.katalog_id' => ['nullable', Rule::exists('katalog', 'id')],
            'items.*.part_no' => ['nullable', 'string', 'max:50'],
            'items.*.deskripsi' => ['nullable', 'string', 'max:200'],
            'items.*.qty_kirim' => ['nullable', 'integer', 'min:0'],
            'items.*.qty' => ['nullable', 'integer', 'min:0'],
            'items.*.qty_sisa' => ['nullable', 'integer', 'min:0'],
            'items.*.berat' => ['nullable', 'numeric', 'min:0'],
            'items.*.volume' => ['nullable', 'numeric', 'min:0'],
            'items.*.dimensi' => ['nullable', 'string', 'max:100'],
            'items.*.sku' => ['nullable', 'string', 'max:50'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $items = $this->input('items', []);
            $hasQtyKirim = false;

            foreach ($items as $index => $item) {
                $qtyKirim = (int) ($item['qty_kirim'] ?? $item['qty'] ?? 0);
                $qtySisa = (int) ($item['qty_sisa'] ?? PHP_INT_MAX);

                if ($qtyKirim > $qtySisa) {
                    $validator->errors()->add(
                        "items.{$index}.qty_kirim",
                        "Qty kirim tidak boleh melebihi qty sisa ({$qtySisa})"
                    );
                }

                if ($qtyKirim > 0) {
                    $hasQtyKirim = true;
                }
            }

            if (! $hasQtyKirim) {
                $validator->errors()->add('items', 'Minimal 1 item harus memiliki qty kirim > 0');
            }
        });
    }
}
