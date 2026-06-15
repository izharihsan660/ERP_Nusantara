<?php

namespace App\Http\Controllers\Transaction;

use App\Enums\SpbStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Spb\StoreSpbRequest;
use App\Http\Requests\Spb\VoidSpbRequest;
use App\Models\PurchaseOrder;
use App\Models\Spb;
use App\Models\WipOrder;
use App\Services\SpbPDFService;
use App\Services\SpbService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SpbController extends Controller
{
    public function __construct(
        private readonly SpbService $spbService,
        private readonly SpbPDFService $spbPDFService,
    ) {}

    public function store(StoreSpbRequest $request, string $type, int $id): RedirectResponse
    {
        $spbAble = $this->resolveSpbAble($type, $id);
        $spb = $this->spbService->create($request->validated(), $spbAble, $request->user());

        return $this->redirectToParent($spb)->with('success', 'SPB berhasil dibuat.');
    }

    public function storeFromWip(StoreSpbRequest $request, WipOrder $wipOrder): RedirectResponse
    {
        $wipOrder->loadMissing(['salesOrder.customer']);
        $spb = $this->spbService->create($request->validated(), $wipOrder, $request->user());

        return $this->redirectToParent($spb)->with('success', 'SPB berhasil dibuat.');
    }

    public function storeFromPurchaseOrder(StoreSpbRequest $request, PurchaseOrder $purchaseOrder): RedirectResponse
    {
        $spb = $this->spbService->create($request->validated(), $purchaseOrder, $request->user());

        return $this->redirectToParent($spb)->with('success', 'SPB berhasil dibuat.');
    }

    public function void(VoidSpbRequest $request, Spb $spb): RedirectResponse
    {
        $spb = $this->spbService->void($spb, $request->validated('alasan_void'), $request->user());

        return $this->redirectToParent($spb)->with('success', 'SPB berhasil divoid.');
    }

    public function download(Spb $spb): BinaryFileResponse
    {
        abort_if($spb->status === SpbStatus::Void, 403);

        $path = $this->spbPDFService->path($spb);

        if (! Storage::disk('local')->exists($path)) {
            $path = $this->spbPDFService->generate($spb);
        }

        $fileName = str_replace('/', '-', $spb->no_spb).'.pdf';

        return response()->file(Storage::disk('local')->path($path), [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$fileName.'"',
        ]);
    }

    private function resolveSpbAble(string $type, int $id): Model
    {
        return match ($type) {
            'wip' => WipOrder::query()->with(['salesOrder.customer'])->findOrFail($id),
            'purchase-order' => PurchaseOrder::query()->findOrFail($id),
            default => abort(404),
        };
    }

    private function redirectToParent(Spb $spb): RedirectResponse
    {
        $spbAble = $spb->spbAble;

        if ($spbAble instanceof WipOrder) {
            return to_route('quotations.show', $spbAble->salesOrder->quotation_id);
        }

        return to_route('purchase-orders.show', $spbAble);
    }
}
