<?php

namespace App\Services;

use App\Mail\ApprovalRequestMail;
use App\Models\AppSetting;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

class NotificationService
{
    public static function sendApprovalEmail(Model $document, string $type): bool
    {
        $emailKey = match ($type) {
            'quotation' => 'approval_email_quotation',
            'purchase_order' => 'approval_email_po_naj',
            'permintaan_dana' => 'approval_email_pd',
            default => null,
        };

        if (! $emailKey) {
            Log::warning("Unknown document type for approval email: {$type}");

            return false;
        }

        $email = AppSetting::where('key', $emailKey)->value('value');

        if (! $email) {
            Log::warning("No approval email configured for {$type}. Skipping email notification.");

            return false;
        }

        $approver = User::query()->where('email', $email)->where('is_active', true)->first();
        $permission = match ($type) {
            'quotation' => 'approve_quotation',
            'purchase_order' => 'approve_purchase_order',
            'permintaan_dana' => 'approve_pd',
        };

        if (! $approver || ! $approver->can($permission)) {
            Log::warning("No active approver user found for configured email {$email}. Skipping approval email.");

            return false;
        }

        // Generate signed URLs (valid 7 days)
        $approvalUrl = URL::temporarySignedRoute(
            'approval.approve',
            now()->addDays(7),
            ['type' => $type, 'id' => $document->id, 'approver' => $approver->id],
        );

        $rejectUrl = URL::temporarySignedRoute(
            'approval.reject',
            now()->addDays(7),
            ['type' => $type, 'id' => $document->id, 'approver' => $approver->id],
        );

        // Prepare email data
        [$documentTypeName, $documentNumber, $customer, $totalAmount, $pdfPath] = self::getDocumentData($document, $type);

        try {
            Mail::to($email)->queue(
                new ApprovalRequestMail(
                    documentType: $documentTypeName,
                    documentNumber: $documentNumber,
                    createdBy: $document->createdBy->name ?? 'Unknown',
                    createdAt: $document->created_at->format('d M Y H:i'),
                    customer: $customer,
                    totalAmount: $totalAmount,
                    approvalUrl: $approvalUrl,
                    rejectUrl: $rejectUrl,
                    pdfPath: $pdfPath
                )
            );

            Log::info("Approval email queued for {$type} #{$document->id} to {$email}");

            return true;
        } catch (\Throwable $e) {
            Log::error("Failed to send approval email for {$type} #{$document->id}: ".$e->getMessage());

            return false;
        }
    }

    private static function getDocumentData(Model $document, string $type): array
    {
        return match ($type) {
            'quotation' => [
                'Quotation',
                $document->no_quotation,
                $document->customer->name ?? null,
                'Rp '.number_format($document->grand_total, 0, ',', '.'),
                null, // PDF path if needed
            ],
            'purchase_order' => [
                'Purchase Order NAJ',
                $document->no_po_naj,
                $document->customer->name ?? null,
                'Rp '.number_format($document->grand_total, 0, ',', '.'),
                null,
            ],
            'permintaan_dana' => [
                'Permintaan Dana',
                $document->no_pd,
                $document->tujuan ?? null,
                'Rp '.number_format($document->total, 0, ',', '.'),
                null,
            ],
            default => ['Unknown', '', null, null, null],
        };
    }
}
