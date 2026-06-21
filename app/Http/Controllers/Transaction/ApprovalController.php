<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use App\Models\PermintaanDana;
use App\Models\PurchaseOrder;
use App\Models\Quotation;
use App\Models\User;
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
            return Inertia::render('ApprovalConfirm', [
                'success' => false,
                'message' => 'Link approval sudah kedaluwarsa atau tidak valid.',
            ]);
        }

        $model = match ($type) {
            'quotation' => Quotation::query()->findOrFail($id),
            'purchase_order' => PurchaseOrder::query()->findOrFail($id),
            'permintaan_dana' => PermintaanDana::query()->findOrFail($id),
            default => abort(404),
        };

        $service = match ($type) {
            'quotation' => app(QuotationService::class),
            'purchase_order' => app(PurchaseOrderService::class),
            'permintaan_dana' => app(PermintaanDanaService::class),
        };

        // Use first Manager or Superadmin user as approver if not authenticated
        $user = $request->user();
        if (! $user) {
            $user = User::role(['Manager', 'Superadmin'])->first();
            if (! $user) {
                return Inertia::render('ApprovalConfirm', [
                    'success' => false,
                    'message' => 'Tidak ada user dengan role Manager/Superadmin untuk melakukan approval.',
                ]);
            }
        }

        try {
            $service->approve($model, $user);

            $documentField = 'no_'.($type === 'purchase_order' ? 'po_naj' : ($type === 'permintaan_dana' ? 'pd' : 'quotation'));

            return Inertia::render('ApprovalConfirm', [
                'success' => true,
                'document' => [
                    'tipe' => str($type)->replace('_', ' ')->title(),
                    'nomor' => $model->{$documentField},
                    'customer' => $model->customer?->nama_customer ?? $model->tujuan ?? 'N/A',
                    'url' => match ($type) {
                        'quotation' => route('quotations.show', $model),
                        'purchase_order' => route('purchase-orders.show', $model),
                        'permintaan_dana' => route('permintaan-dana.show', $model),
                    },
                ],
            ]);
        } catch (\Exception $e) {
            return Inertia::render('ApprovalConfirm', [
                'success' => false,
                'message' => 'Gagal melakukan approval: '.$e->getMessage(),
            ]);
        }
    }

    public function reject(Request $request, string $type, int $id): RedirectResponse|Response
    {
        if (! $request->hasValidSignature()) {
            return Inertia::render('ApprovalConfirm', [
                'success' => false,
                'message' => 'Link reject sudah kedaluwarsa atau tidak valid.',
            ]);
        }

        $model = match ($type) {
            'quotation' => Quotation::query()->findOrFail($id),
            'purchase_order' => PurchaseOrder::query()->findOrFail($id),
            'permintaan_dana' => PermintaanDana::query()->findOrFail($id),
            default => abort(404),
        };

        $service = match ($type) {
            'quotation' => app(QuotationService::class),
            'purchase_order' => app(PurchaseOrderService::class),
            'permintaan_dana' => app(PermintaanDanaService::class),
        };

        // Use first Manager or Superadmin user as approver if not authenticated
        $user = $request->user();
        if (! $user) {
            $user = User::role(['Manager', 'Superadmin'])->first();
            if (! $user) {
                return Inertia::render('ApprovalConfirm', [
                    'success' => false,
                    'message' => 'Tidak ada user dengan role Manager/Superadmin untuk melakukan reject.',
                ]);
            }
        }

        try {
            $service->reject($model, $user, 'Rejected via email approval link');

            $documentField = 'no_'.($type === 'purchase_order' ? 'po_naj' : ($type === 'permintaan_dana' ? 'pd' : 'quotation'));

            return Inertia::render('ApprovalConfirm', [
                'success' => true,
                'rejected' => true,
                'document' => [
                    'tipe' => str($type)->replace('_', ' ')->title(),
                    'nomor' => $model->{$documentField},
                    'customer' => $model->customer?->nama_customer ?? $model->tujuan ?? 'N/A',
                    'url' => match ($type) {
                        'quotation' => route('quotations.show', $model),
                        'purchase_order' => route('purchase-orders.show', $model),
                        'permintaan_dana' => route('permintaan-dana.show', $model),
                    },
                ],
            ]);
        } catch (\Exception $e) {
            return Inertia::render('ApprovalConfirm', [
                'success' => false,
                'message' => 'Gagal melakukan reject: '.$e->getMessage(),
            ]);
        }
    }
}
