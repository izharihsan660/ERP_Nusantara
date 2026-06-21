<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Http\Requests\Katalog\ImportKatalogRequest;
use App\Http\Requests\Katalog\StoreKatalogRequest;
use App\Http\Requests\Katalog\UpdateKatalogRequest;
use App\Models\Katalog;
use App\Services\KatalogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class KatalogController extends Controller
{
    public function __construct(private readonly KatalogService $katalogService) {}

    public function index(Request $request): Response
    {
        return Inertia::render('MasterData/Katalog/Index', [
            'items' => $this->katalogService->paginate($request->query()),
            'categories' => $this->katalogService->categories(),
            'filters' => $request->only(['search', 'kategori', 'status', 'sort', 'direction', 'per_page']),
        ]);
    }

    public function search(Request $request): JsonResponse
    {
        $query = trim((string) $request->query('q', ''));
        $limit = min(max((int) $request->query('limit', 10), 1), 10);

        $items = Katalog::query()
            ->when($query !== '', function ($katalogQuery) use ($query): void {
                $katalogQuery->where(function ($searchQuery) use ($query): void {
                    $searchQuery->where('part_no', 'like', "%{$query}%")
                        ->orWhere('nama_barang', 'like', "%{$query}%");
                });
            })
            ->orderBy('part_no')
            ->limit($limit)
            ->get(['id', 'part_no', 'nama_barang', 'satuan', 'harga_jual_default', 'hpp']);

        return response()->json($items);
    }

    public function create(): Response
    {
        return Inertia::render('MasterData/Katalog/Form', ['item' => null]);
    }

    public function store(StoreKatalogRequest $request): RedirectResponse
    {
        $this->katalogService->create($request->validated(), $request);

        return to_route('katalog.index')->with('success', 'Barang berhasil dibuat.');
    }

    public function edit(Katalog $katalog): Response
    {
        return Inertia::render('MasterData/Katalog/Form', ['item' => $katalog]);
    }

    public function update(UpdateKatalogRequest $request, Katalog $katalog): RedirectResponse
    {
        $this->katalogService->update($katalog, $request->validated(), $request);

        return to_route('katalog.index')->with('success', 'Barang berhasil diperbarui.');
    }

    public function destroy(Request $request, Katalog $katalog): RedirectResponse
    {
        abort_unless($request->user()?->can('hapus_katalog'), 403);

        $this->katalogService->deactivate($katalog, $request);

        return back()->with('success', 'Barang berhasil dinonaktifkan.');
    }

    public function import(ImportKatalogRequest $request): RedirectResponse
    {
        $path = $request->file('file')->store('imports');
        $this->katalogService->import($path, $request);

        return back()->with('success', 'Import katalog berhasil diproses.');
    }
}
