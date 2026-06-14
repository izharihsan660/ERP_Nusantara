<?php

namespace App\Services;

use App\Actions\Vendor\CreateVendor;
use App\Actions\Vendor\DeleteVendor;
use App\Actions\Vendor\UpdateVendor;
use App\Models\Vendor;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class VendorService
{
    public function __construct(
        private readonly CreateVendor $createVendor,
        private readonly UpdateVendor $updateVendor,
        private readonly DeleteVendor $deleteVendor,
    ) {}

    public function paginate(array $filters): LengthAwarePaginator
    {
        $sort = in_array($filters['sort'] ?? null, ['nama_vendor', 'tipe_vendor', 'created_at'], true)
            ? $filters['sort']
            : 'created_at';

        return Vendor::query()
            ->when($filters['search'] ?? null, function ($query, string $search): void {
                $query->where('nama_vendor', 'like', "%{$search}%")
                    ->orWhere('pic_name', 'like', "%{$search}%")
                    ->orWhere('pic_email', 'like', "%{$search}%");
            })
            ->when($filters['tipe_vendor'] ?? null, fn ($query, string $type) => $query->where('tipe_vendor', $type))
            ->orderBy($sort, ($filters['direction'] ?? 'desc') === 'asc' ? 'asc' : 'desc')
            ->paginate((int) ($filters['per_page'] ?? 10))
            ->withQueryString();
    }

    public function create(array $data, Request $request): Vendor
    {
        return $this->createVendor->handle($data, $request);
    }

    public function update(Vendor $vendor, array $data, Request $request): Vendor
    {
        return $this->updateVendor->handle($vendor, $data, $request);
    }

    public function delete(Vendor $vendor, Request $request): void
    {
        $this->deleteVendor->handle($vendor, $request);
    }
}
