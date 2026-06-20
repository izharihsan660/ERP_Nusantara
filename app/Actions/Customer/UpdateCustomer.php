<?php

namespace App\Actions\Customer;

use App\Actions\ActivityLog\RecordActivity;
use App\Models\Customer;
use Illuminate\Http\Request;

class UpdateCustomer
{
    public function __construct(private readonly RecordActivity $recordActivity) {}

    public function handle(Customer $customer, array $data, Request $request): Customer
    {
        $customer->update($data);

        $this->recordActivity->handle('updated_customer', $customer, "Mengubah customer {$customer->kode_customer}", $request);

        return $customer->refresh();
    }
}
