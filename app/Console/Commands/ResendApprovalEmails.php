<?php

namespace App\Console\Commands;

use App\Enums\PDStatus;
use App\Enums\PurchaseOrderStatus;
use App\Enums\QuotationStatus;
use App\Models\PermintaanDana;
use App\Models\PurchaseOrder;
use App\Models\Quotation;
use App\Services\NotificationService;
use Illuminate\Console\Command;

class ResendApprovalEmails extends Command
{
    protected $signature = 'approval:resend-emails';

    protected $description = 'Kirim ulang email approval untuk semua dokumen Pending Approval.';

    public function handle(): int
    {
        $counts = [
            'quotation' => $this->resend(Quotation::query()->where('status', QuotationStatus::PendingApproval->value)->get(), 'quotation'),
            'purchase_order' => $this->resend(PurchaseOrder::query()->where('status', PurchaseOrderStatus::PendingApproval->value)->get(), 'purchase_order'),
            'permintaan_dana' => $this->resend(PermintaanDana::query()->where('status', PDStatus::PendingApproval->value)->get(), 'permintaan_dana'),
        ];

        $this->table(
            ['Tipe', 'Email Terkirim'],
            [
                ['Quotation', $counts['quotation']],
                ['Purchase Order', $counts['purchase_order']],
                ['Permintaan Dana', $counts['permintaan_dana']],
            ],
        );

        return self::SUCCESS;
    }

    private function resend(iterable $documents, string $type): int
    {
        $sent = 0;

        foreach ($documents as $document) {
            if (NotificationService::sendApprovalEmail($document, $type)) {
                $sent++;
            }
        }

        return $sent;
    }
}
