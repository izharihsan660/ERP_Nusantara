<?php

namespace App\Actions\Customer;

use App\Actions\ActivityLog\RecordActivity;
use App\Models\Customer;
use Illuminate\Http\Request;

class CreateCustomer
{
    public function __construct(private readonly RecordActivity $recordActivity) {}

    public function handle(array $data, Request $request): Customer
    {
        $customer = Customer::create($data);

        $this->recordActivity->handle('Customer tambah', $customer, "Menambah customer {$customer->kode_customer}", $request);

        return $customer;
    }
}
