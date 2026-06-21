<?php

namespace App\Http\Requests\PurchaseOrder;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateReferensiPurchaseOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('buat_purchase_order') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'no_pr_customer' => ['nullable', 'string', 'max:50'],
            'no_po_customer' => ['nullable', 'string', 'max:50'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if (blank($this->input('no_pr_customer')) && blank($this->input('no_po_customer'))) {
                    $validator->errors()->add('no_pr_customer', 'Minimal No. PR Customer atau No. PO Customer harus diisi.');
                }
            },
        ];
    }
}
