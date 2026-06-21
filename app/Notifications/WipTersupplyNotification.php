<?php

namespace App\Notifications;

use App\Models\WipOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class WipTersupplyNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly WipOrder $wipOrder, private readonly string $roleName) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $this->wipOrder->loadMissing('salesOrder.quotation.customer');
        $customer = $this->wipOrder->salesOrder?->quotation?->customer?->nama_customer ?? 'customer';
        $message = $this->roleName === 'Gudang'
            ? "WIP {$this->wipOrder->no_wip} tersupply, segera buat SPB untuk {$customer}"
            : "Barang WIP {$this->wipOrder->no_wip} sudah tersupply, siap buat SPB";

        return [
            'title' => 'WIP tersupply',
            'message' => $message,
            'type' => 'wip_tersupply',
            'url' => route('quotations.show', $this->wipOrder->salesOrder?->quotation_id).'#spb',
            'icon' => 'Truck',
        ];
    }
}
