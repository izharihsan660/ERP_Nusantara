<?php

namespace App\Actions\Customer;

use App\Actions\ActivityLog\RecordActivity;
use App\Models\Customer;
use Illuminate\Http\Request;

class DeactivateCustomer
{
    public function __construct(private readonly RecordActivity $recordActivity) {}

    public function handle(Customer $customer, Request $request): Customer
    {
        $customer->update(['is_active' => false]);

        $this->recordActivity->handle('deactivated_customer', $customer, "Menonaktifkan customer {$customer->kode_customer}", $request);

        return $customer;
    }
}
