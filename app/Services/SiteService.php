<?php

namespace App\Services;

use App\Actions\Site\CreateSite;
use App\Actions\Site\DeleteSite;
use App\Actions\Site\UpdateSite;
use App\Models\Site;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class SiteService
{
    public function __construct(
        private readonly CreateSite $createSite,
        private readonly UpdateSite $updateSite,
        private readonly DeleteSite $deleteSite,
    ) {}

    public function paginate(array $filters): LengthAwarePaginator
    {
        $sort = in_array($filters['sort'] ?? null, ['nama_site', 'created_at'], true)
            ? $filters['sort']
            : 'created_at';

        return Site::query()
            ->with('customer:id,kode_customer,nama_customer')
            ->when($filters['search'] ?? null, function ($query, string $search): void {
                $query->where('nama_site', 'like', "%{$search}%")
                    ->orWhereHas('customer', fn ($query) => $query->where('nama_customer', 'like', "%{$search}%"));
            })
            ->when($filters['customer_id'] ?? null, fn ($query, int|string $customerId) => $query->where('customer_id', $customerId))
            ->orderBy($sort, ($filters['direction'] ?? 'desc') === 'asc' ? 'asc' : 'desc')
            ->paginate((int) ($filters['per_page'] ?? 10))
            ->withQueryString();
    }

    public function create(array $data, Request $request): Site
    {
        return $this->createSite->handle($data, $request);
    }

    public function update(Site $site, array $data, Request $request): Site
    {
        return $this->updateSite->handle($site, $data, $request);
    }

    public function delete(Site $site, Request $request): void
    {
        $this->deleteSite->handle($site, $request);
    }
}
