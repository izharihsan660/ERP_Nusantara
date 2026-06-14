<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Http\Requests\Site\StoreSiteRequest;
use App\Http\Requests\Site\UpdateSiteRequest;
use App\Models\Customer;
use App\Models\Site;
use App\Services\SiteService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SiteController extends Controller
{
    public function __construct(private readonly SiteService $siteService) {}

    public function index(Request $request): Response
    {
        return Inertia::render('MasterData/Sites/Index', [
            'sites' => $this->siteService->paginate($request->query()),
            'filters' => $request->only(['search', 'customer_id', 'sort', 'direction', 'per_page']),
            'customers' => $this->customers(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('MasterData/Sites/Form', [
            'site' => null,
            'customers' => $this->customers(),
        ]);
    }

    public function store(StoreSiteRequest $request): RedirectResponse
    {
        $this->siteService->create($request->validated(), $request);

        return to_route('sites.index')->with('success', 'Site berhasil dibuat.');
    }

    public function edit(Site $site): Response
    {
        return Inertia::render('MasterData/Sites/Form', [
            'site' => $site,
            'customers' => $this->customers(),
        ]);
    }

    public function update(UpdateSiteRequest $request, Site $site): RedirectResponse
    {
        $this->siteService->update($site, $request->validated(), $request);

        return to_route('sites.index')->with('success', 'Site berhasil diperbarui.');
    }

    public function destroy(Request $request, Site $site): RedirectResponse
    {
        abort_unless($request->user()?->can('Site hapus'), 403);

        $this->siteService->delete($site, $request);

        return back()->with('success', 'Site berhasil dihapus.');
    }

    private function customers(): array
    {
        return Customer::query()
            ->active()
            ->orderBy('nama_customer')
            ->get(['id', 'kode_customer', 'nama_customer'])
            ->all();
    }
}
