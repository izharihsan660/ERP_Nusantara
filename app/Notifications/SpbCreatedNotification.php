<?php

namespace App\Notifications;

use App\Models\PurchaseOrder;
use App\Models\Spb;
use App\Models\WipOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SpbCreatedNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly Spb $spb) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $this->spb->loadMissing('spbAble');

        return [
            'title' => 'SPB siap ditagihkan',
            'message' => "SPB {$this->spb->no_spb} dibuat, siap terbitkan Invoice/Nota",
            'type' => 'spb_created',
            'url' => $this->url(),
            'icon' => 'Truck',
        ];
    }

    private function url(): string
    {
        $source = $this->spb->spbAble;

        if ($source instanceof WipOrder) {
            $source->loadMissing('salesOrder');

            return route('quotations.show', $source->salesOrder->quotation_id).'#tagihan';
        }

        if ($source instanceof PurchaseOrder) {
            return route('purchase-orders.show', $source).'#tagihan';
        }

        return route('dashboard');
    }
}
