<?php

namespace App\Notifications;

use App\Models\PermintaanDana;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PdSubmittedNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly PermintaanDana $permintaanDana) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $nominal = 'Rp '.number_format((float) $this->permintaanDana->nominal, 0, ',', '.');

        return [
            'title' => 'Permintaan Dana menunggu approval',
            'message' => "Ada Permintaan Dana menunggu approval: {$this->permintaanDana->no_pd} — {$nominal}",
            'type' => 'pd_submitted',
            'url' => route('permintaan-dana.show', $this->permintaanDana),
            'icon' => 'HandCoins',
        ];
    }
}
