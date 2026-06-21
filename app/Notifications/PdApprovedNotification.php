<?php

namespace App\Notifications;

use App\Models\PermintaanDana;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PdApprovedNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly PermintaanDana $permintaanDana) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $message = "Permintaan Dana {$this->permintaanDana->no_pd} untuk {$this->permintaanDana->tujuan} diapprove, silakan cairkan dana dan upload bukti";

        return [
            'title' => 'Permintaan Dana diapprove',
            'message' => $message,
            'type' => 'pd_approved',
            'url' => route('permintaan-dana.show', $this->permintaanDana),
            'icon' => 'HandCoins',
        ];
    }
}
