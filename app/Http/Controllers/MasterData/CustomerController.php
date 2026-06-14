<?php

namespace App\Http\Controllers\MasterData;

use App\Enums\DocumentType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\StoreCustomerRequest;
use App\Http\Requests\Customer\UpdateCustomerRequest;
use App\Models\Customer;
use App\Models\DocumentTemplate;
use App\Services\CustomerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CustomerController extends Controller
{
    public function __construct(private readonly CustomerService $customerService) {}

    public function index(Request $request): Response
    {
        return Inertia::render('MasterData/Customers/Index', [
            'customers' => $this->customerService->paginate($request->query()),
            'filters' => $request->only(['search', 'status', 'sort', 'direction', 'per_page']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('MasterData/Customers/Form', [
            'customer' => null,
            'options' => $this->options(),
        ]);
    }

    public function store(StoreCustomerRequest $request): RedirectResponse
    {
        $this->customerService->create($request->validated(), $request);

        return to_route('customers.index')->with('success', 'Customer berhasil dibuat.');
    }

    public function edit(Customer $customer): Response
    {
        return Inertia::render('MasterData/Customers/Form', [
            'customer' => $customer,
            'options' => $this->options(),
        ]);
    }

    public function update(UpdateCustomerRequest $request, Customer $customer): RedirectResponse
    {
        $this->customerService->update($customer, $request->validated(), $request);

        return to_route('customers.index')->with('success', 'Customer berhasil diperbarui.');
    }

    public function destroy(Request $request, Customer $customer): RedirectResponse
    {
        abort_unless($request->user()?->can('Customer hapus'), 403);

        $this->customerService->deactivate($customer, $request);

        return back()->with('success', 'Customer berhasil dinonaktifkan.');
    }

    private function options(): array
    {
        return [
            'quotationTemplates' => DocumentTemplate::query()
                ->where('tipe_dokumen', DocumentType::Quotation)
                ->orderBy('nama_template')
                ->get(['id', 'nama_template']),
            'spbTemplates' => DocumentTemplate::query()
                ->where('tipe_dokumen', DocumentType::Spb)
                ->orderBy('nama_template')
                ->get(['id', 'nama_template']),
        ];
    }
}
