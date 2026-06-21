<?php

namespace App\Notifications;

use App\Models\Quotation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class QuotationSubmittedNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly Quotation $quotation) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Quotation menunggu approval',
            'message' => "Ada Quotation menunggu approval: {$this->quotation->no_quotation}",
            'type' => 'quotation_submitted',
            'url' => route('quotations.show', $this->quotation),
            'icon' => 'FileText',
        ];
    }
}
