<?php

namespace App\Notifications;

use App\Models\PurchaseOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PoNajSubmittedNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly PurchaseOrder $purchaseOrder) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Purchase Order menunggu approval',
            'message' => "Ada Purchase Order menunggu approval: {$this->purchaseOrder->no_purchase_order}",
            'type' => 'po_naj_submitted',
            'url' => route('purchase-orders.show', $this->purchaseOrder),
            'icon' => 'ClipboardList',
        ];
    }
}
