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
    public function approve(Request $request, string $type, int $id): Response
    {
        return $this->confirmation($request, $type, $id, 'approve');
    }

    public function reject(Request $request, string $type, int $id): Response
    {
        return $this->confirmation($request, $type, $id, 'reject');
    }

    public function executeApprove(Request $request, string $type, int $id): RedirectResponse|Response
    {
        if (! $request->hasValidSignature()) {
            return $this->invalidLink('Link approval sudah kedaluwarsa atau tidak valid.');
        }

        $model = $this->model($type, $id);
        $service = $this->service($type);
        $user = $this->approver($request, $type);

        try {
            $service->approve($model, $user);

            return Inertia::render('ApprovalConfirm', [
                'success' => true,
                'document' => $this->documentSummary($type, $model),
            ]);
        } catch (\Throwable $e) {
            return Inertia::render('ApprovalConfirm', [
                'success' => false,
                'message' => 'Gagal melakukan approval: '.$e->getMessage(),
            ]);
        }
    }

    public function executeReject(Request $request, string $type, int $id): RedirectResponse|Response
    {
        if (! $request->hasValidSignature()) {
            return $this->invalidLink('Link reject sudah kedaluwarsa atau tidak valid.');
        }

        $model = $this->model($type, $id);
        $service = $this->service($type);
        $user = $this->approver($request, $type);

        try {
            $service->reject($model, 'Rejected via email approval link', $user);

            return Inertia::render('ApprovalConfirm', [
                'success' => true,
                'rejected' => true,
                'document' => $this->documentSummary($type, $model),
            ]);
        } catch (\Throwable $e) {
            return Inertia::render('ApprovalConfirm', [
                'success' => false,
                'message' => 'Gagal melakukan reject: '.$e->getMessage(),
            ]);
        }
    }

    private function confirmation(Request $request, string $type, int $id, string $action): Response
    {
        if (! $request->hasValidSignature()) {
            return $this->invalidLink('Link approval sudah kedaluwarsa atau tidak valid.');
        }

        $model = $this->model($type, $id);
        $this->approver($request, $type);

        return Inertia::render('ApprovalConfirm', [
            'confirmation' => true,
            'action' => $action,
            'actionUrl' => $request->fullUrl(),
            'document' => $this->documentSummary($type, $model),
        ]);
    }

    private function model(string $type, int $id): Quotation|PurchaseOrder|PermintaanDana
    {
        return match ($type) {
            'quotation' => Quotation::query()->with('customer')->findOrFail($id),
            'purchase_order' => PurchaseOrder::query()->with('customer')->findOrFail($id),
            'permintaan_dana' => PermintaanDana::query()->findOrFail($id),
            default => abort(404),
        };
    }

    private function service(string $type): QuotationService|PurchaseOrderService|PermintaanDanaService
    {
        return match ($type) {
            'quotation' => app(QuotationService::class),
            'purchase_order' => app(PurchaseOrderService::class),
            'permintaan_dana' => app(PermintaanDanaService::class),
            default => abort(404),
        };
    }

    private function approver(Request $request, string $type): User
    {
        $user = User::query()->findOrFail($request->integer('approver'));
        $permission = match ($type) {
            'quotation' => 'approve_quotation',
            'purchase_order' => 'approve_purchase_order',
            'permintaan_dana' => 'approve_pd',
            default => abort(404),
        };

        abort_unless($user->is_active && $user->can($permission), 403);

        return $user;
    }

    private function documentSummary(string $type, Quotation|PurchaseOrder|PermintaanDana $model): array
    {
        return [
            'tipe' => str($type)->replace('_', ' ')->title(),
            'nomor' => match ($type) {
                'quotation' => $model->no_quotation,
                'purchase_order' => $model->no_purchase_order,
                'permintaan_dana' => $model->no_pd,
            },
            'customer' => $model->customer?->nama_customer ?? $model->tujuan ?? 'N/A',
            'url' => match ($type) {
                'quotation' => route('quotations.show', $model),
                'purchase_order' => route('purchase-orders.show', $model),
                'permintaan_dana' => route('permintaan-dana.show', $model),
            },
        ];
    }

    private function invalidLink(string $message): Response
    {
        return Inertia::render('ApprovalConfirm', [
            'success' => false,
            'message' => $message,
        ]);
    }
}
