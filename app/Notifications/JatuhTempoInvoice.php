<?php

namespace App\Notifications;

use App\Models\Invoice;
use App\Models\PurchaseOrder;
use App\Models\WipOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class JatuhTempoInvoice extends Notification
{
    use Queueable;

    public function __construct(private readonly Invoice $invoice) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $this->invoice->loadMissing(['spb.spbAble']);

        return [
            'invoice_id' => $this->invoice->id,
            'no_dokumen' => $this->invoice->no_dokumen,
            'title' => 'Invoice jatuh tempo H-7',
            'message' => "Invoice {$this->invoice->no_dokumen} jatuh tempo pada {$this->invoice->tgl_jatuh_tempo?->format('Y-m-d')}.",
            'url' => $this->url(),
            'tgl_jatuh_tempo' => $this->invoice->tgl_jatuh_tempo?->format('Y-m-d'),
        ];
    }

    private function url(): string
    {
        $source = $this->invoice->spb?->spbAble;

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
