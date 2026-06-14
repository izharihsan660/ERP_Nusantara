<?php

namespace App\Services;

use App\Actions\Katalog\CreateKatalog;
use App\Actions\Katalog\DeactivateKatalog;
use App\Actions\Katalog\ImportKatalog;
use App\Actions\Katalog\UpdateKatalog;
use App\Models\Katalog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class KatalogService
{
    public function __construct(
        private readonly CreateKatalog $createKatalog,
        private readonly UpdateKatalog $updateKatalog,
        private readonly DeactivateKatalog $deactivateKatalog,
        private readonly ImportKatalog $importKatalog,
    ) {}

    public function paginate(array $filters): LengthAwarePaginator
    {
        $sort = in_array($filters['sort'] ?? null, ['part_no', 'nama_barang', 'kategori', 'hpp', 'harga_jual_default', 'created_at'], true)
            ? $filters['sort']
            : 'created_at';

        return Katalog::query()
            ->when($filters['search'] ?? null, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('part_no', 'like', "%{$search}%")
                        ->orWhere('nama_barang', 'like', "%{$search}%")
                        ->orWhere('kategori', 'like', "%{$search}%");
                });
            })
            ->when($filters['kategori'] ?? null, fn ($query, string $kategori) => $query->where('kategori', $kategori))
            ->when(($filters['status'] ?? 'all') !== 'all', fn ($query) => $query->where('is_active', $filters['status'] === 'active'))
            ->orderBy($sort, ($filters['direction'] ?? 'desc') === 'asc' ? 'asc' : 'desc')
            ->paginate((int) ($filters['per_page'] ?? 10))
            ->withQueryString();
    }

    public function categories(): array
    {
        return Katalog::query()
            ->whereNotNull('kategori')
            ->distinct()
            ->orderBy('kategori')
            ->pluck('kategori')
            ->all();
    }

    public function create(array $data, Request $request): Katalog
    {
        return $this->createKatalog->handle($data, $request);
    }

    public function update(Katalog $katalog, array $data, Request $request): Katalog
    {
        return $this->updateKatalog->handle($katalog, $data, $request);
    }

    public function deactivate(Katalog $katalog, Request $request): Katalog
    {
        return $this->deactivateKatalog->handle($katalog, $request);
    }

    public function import(string $path, Request $request): void
    {
        $this->importKatalog->handle($path, $request);
    }
}
