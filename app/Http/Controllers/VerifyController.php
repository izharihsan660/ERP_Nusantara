<?php

namespace App\Http\Controllers;

use App\Models\PermintaanDana;
use App\Models\PurchaseOrder;
use App\Models\Quotation;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class VerifyController extends Controller
{
    public function show(string $qr_token): Response
    {
        $document = $this->findDocument($qr_token);

        if ($document === null) {
            return Inertia::render('Verify', [
                'valid' => false,
                'status' => 'not_found',
                'document' => null,
            ]);
        }

        return Inertia::render('Verify', [
            'valid' => true,
            'status' => 'found',
            'document' => $document,
        ]);
    }

    private function findDocument(string $qrToken): ?array
    {
        $quotation = Quotation::query()
            ->with(['approvedBy', 'customer'])
            ->where('qr_token', $qrToken)
            ->first();

        if ($quotation !== null) {
            return [
                'jenis_dokumen' => 'Quotation',
                'nomor_label' => 'Nomor Quotation',
                'nomor' => $quotation->no_quotation,
                'pihak_label' => 'Customer',
                'pihak' => $quotation->customer?->nama_customer,
                'tanggal_label' => 'Tanggal Quotation',
                'tanggal' => $this->formatDate($quotation->tgl_quotation),
                'approved_by' => $quotation->approvedBy?->name,
                'approved_at' => $this->formatDateTime($quotation->approved_at),
            ];
        }

        $purchaseOrder = PurchaseOrder::query()
            ->with(['approvedBy', 'customer', 'vendor'])
            ->where('qr_token', $qrToken)
            ->first();

        if ($purchaseOrder !== null) {
            return [
                'jenis_dokumen' => 'Purchase Order NAJ',
                'nomor_label' => 'Nomor Purchase Order',
                'nomor' => $purchaseOrder->no_purchase_order,
                'pihak_label' => 'Vendor',
                'pihak' => $purchaseOrder->vendor?->nama_vendor,
                'tanggal_label' => 'Tanggal PO',
                'tanggal' => $this->formatDate($purchaseOrder->tgl_po),
                'approved_by' => $purchaseOrder->approvedBy?->name,
                'approved_at' => $this->formatDateTime($purchaseOrder->approved_at),
            ];
        }

        $permintaanDana = PermintaanDana::query()
            ->with('approvedBy')
            ->where('qr_token', $qrToken)
            ->first();

        if ($permintaanDana !== null) {
            return [
                'jenis_dokumen' => 'Permintaan Dana',
                'nomor_label' => 'Nomor PD',
                'nomor' => $permintaanDana->no_pd,
                'pihak_label' => 'Tujuan',
                'pihak' => $permintaanDana->tujuan,
                'tanggal_label' => 'Plan Pembayaran',
                'tanggal' => $this->formatDate($permintaanDana->plan_pembayaran),
                'approved_by' => $permintaanDana->approvedBy?->name,
                'approved_at' => $this->formatDateTime($permintaanDana->approved_at),
            ];
        }

        return null;
    }

    private function formatDate(mixed $date): ?string
    {
        return $date instanceof Carbon ? $date->format('d/m/Y') : null;
    }

    private function formatDateTime(mixed $dateTime): ?string
    {
        return $dateTime instanceof Carbon ? $dateTime->format('d/m/Y H:i') : null;
    }
}
