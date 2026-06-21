<?php

namespace App\Notifications;

use App\Models\PermintaanDana;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PdRejectedNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly PermintaanDana $permintaanDana) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Permintaan Dana ditolak',
            'message' => "Permintaan Dana {$this->permintaanDana->no_pd} ditolak: {$this->permintaanDana->catatan_rejection}",
            'type' => 'pd_rejected',
            'url' => route('permintaan-dana.show', $this->permintaanDana),
            'icon' => 'HandCoins',
        ];
    }
}
