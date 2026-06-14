<?php

namespace App\Services;

use App\Actions\Customer\CreateCustomer;
use App\Actions\Customer\DeactivateCustomer;
use App\Actions\Customer\UpdateCustomer;
use App\Models\Customer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class CustomerService
{
    public function __construct(
        private readonly CreateCustomer $createCustomer,
        private readonly UpdateCustomer $updateCustomer,
        private readonly DeactivateCustomer $deactivateCustomer,
    ) {}

    public function paginate(array $filters): LengthAwarePaginator
    {
        $sort = in_array($filters['sort'] ?? null, ['kode_customer', 'nama_customer', 'kota', 'created_at'], true)
            ? $filters['sort']
            : 'created_at';

        return Customer::query()
            ->with(['quotationTemplate:id,nama_template', 'spbTemplate:id,nama_template'])
            ->when($filters['search'] ?? null, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('kode_customer', 'like', "%{$search}%")
                        ->orWhere('nama_customer', 'like', "%{$search}%")
                        ->orWhere('kota', 'like', "%{$search}%")
                        ->orWhere('pic_name', 'like', "%{$search}%");
                });
            })
            ->when(($filters['status'] ?? 'all') !== 'all', fn ($query) => $query->where('is_active', $filters['status'] === 'active'))
            ->orderBy($sort, ($filters['direction'] ?? 'desc') === 'asc' ? 'asc' : 'desc')
            ->paginate((int) ($filters['per_page'] ?? 10))
            ->withQueryString();
    }

    public function create(array $data, Request $request): Customer
    {
        return $this->createCustomer->handle($data, $request);
    }

    public function update(Customer $customer, array $data, Request $request): Customer
    {
        return $this->updateCustomer->handle($customer, $data, $request);
    }

    public function deactivate(Customer $customer, Request $request): Customer
    {
        return $this->deactivateCustomer->handle($customer, $request);
    }
}
