<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use App\Models\PermintaanDana;
use App\Models\PurchaseOrder;
use App\Models\Quotation;
use App\Services\PermintaanDanaService;
use App\Services\PurchaseOrderService;
use App\Services\QuotationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ApprovalController extends Controller
{
    public function approve(Request $request, string $type, int $id): RedirectResponse|Response
    {
        if (! $request->hasValidSignature()) {
            abort(403, 'Link approval sudah kedaluwarsa atau tidak valid.');
        }

        $model = match ($type) {
            'quotation' => Quotation::query()->findOrFail($id),
            'purchase-order' => PurchaseOrder::query()->findOrFail($id),
            'permintaan-dana' => PermintaanDana::query()->findOrFail($id),
            default => abort(404),
        };

        $service = match ($type) {
            'quotation' => app(QuotationService::class),
            'purchase-order' => app(PurchaseOrderService::class),
            'permintaan-dana' => app(PermintaanDanaService::class),
        };

        $user = $request->user() ?? $model->createdBy;

        if (! $user) {
            abort(403, 'User tidak ditemukan untuk melakukan approval.');
        }

        $service->approve($model, $user);

        return Inertia::render('ApprovalConfirm', [
            'success' => true,
            'document' => [
                'tipe' => str($type)->replace('-', ' ')->title(),
                'nomor' => $model->{'no_'.str($type)->replace('-', '_')},
                'customer' => $model->customer?->nama_customer ?? $model->tujuan ?? 'N/A',
                'url' => match ($type) {
                    'quotation' => route('quotations.show', $model),
                    'purchase-order' => route('purchase-orders.show', $model),
                    'permintaan-dana' => route('permintaan-dana.show', $model),
                },
            ],
        ]);
    }
}
