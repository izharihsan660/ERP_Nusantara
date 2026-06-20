<?php

namespace App\Http\Controllers\MasterData;

use App\Enums\VendorType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Vendor\StoreVendorRequest;
use App\Http\Requests\Vendor\UpdateVendorRequest;
use App\Models\Vendor;
use App\Services\VendorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class VendorController extends Controller
{
    public function __construct(private readonly VendorService $vendorService) {}

    public function index(Request $request): Response
    {
        return Inertia::render('MasterData/Vendors/Index', [
            'vendors' => $this->vendorService->paginate($request->query()),
            'filters' => $request->only(['search', 'tipe_vendor', 'sort', 'direction', 'per_page']),
            'vendorTypes' => VendorType::options(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('MasterData/Vendors/Form', [
            'vendor' => null,
            'vendorTypes' => VendorType::options(),
        ]);
    }

    public function store(StoreVendorRequest $request): RedirectResponse
    {
        $this->vendorService->create($request->validated(), $request);

        return to_route('vendors.index')->with('success', 'Vendor berhasil dibuat.');
    }

    public function edit(Vendor $vendor): Response
    {
        return Inertia::render('MasterData/Vendors/Form', [
            'vendor' => $vendor,
            'vendorTypes' => VendorType::options(),
        ]);
    }

    public function update(UpdateVendorRequest $request, Vendor $vendor): RedirectResponse
    {
        $this->vendorService->update($vendor, $request->validated(), $request);

        return to_route('vendors.index')->with('success', 'Vendor berhasil diperbarui.');
    }

    public function destroy(Request $request, Vendor $vendor): RedirectResponse
    {
        abort_unless($request->user()?->can('hapus_vendor'), 403);

        $this->vendorService->delete($vendor, $request);

        return back()->with('success', 'Vendor berhasil dihapus.');
    }
}
