<?php

namespace App\Console\Commands;

use App\Enums\InvoiceStatus;
use App\Enums\MetodePembayaran;
use App\Enums\StatusPembayaran;
use App\Models\Invoice;
use App\Models\User;
use App\Notifications\JatuhTempoInvoice;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class CheckJatuhTempo extends Command
{
    protected $signature = 'invoices:check-jatuh-tempo';

    protected $description = 'Kirim notifikasi H-7 jatuh tempo invoice TOP ke Finance dan Manager.';

    public function handle(): int
    {
        $targetDate = now()->addDays(7)->toDateString();
        $users = User::query()
            ->whereHas('roles', fn ($query) => $query->whereIn('name', ['Finance', 'Manager']))
            ->get();

        if ($users->isEmpty()) {
            $this->info('Tidak ada user Finance atau Manager untuk menerima notifikasi.');

            return self::SUCCESS;
        }

        $invoices = Invoice::query()
            ->with(['spb.spbAble'])
            ->where('metode_pembayaran', MetodePembayaran::TOP->value)
            ->where('status', InvoiceStatus::Active->value)
            ->where('status_pembayaran', '!=', StatusPembayaran::Lunas->value)
            ->whereDate('tgl_jatuh_tempo', $targetDate)
            ->get();

        $sent = 0;

        foreach ($invoices as $invoice) {
            if ($this->alreadySentToday($invoice)) {
                continue;
            }

            Notification::send($users, new JatuhTempoInvoice($invoice));
            $sent++;
        }

        $this->info("Notifikasi jatuh tempo terkirim untuk {$sent} invoice.");

        return self::SUCCESS;
    }

    private function alreadySentToday(Invoice $invoice): bool
    {
        return DB::table('notifications')
            ->where('type', JatuhTempoInvoice::class)
            ->where('data', 'like', '%"invoice_id":'.$invoice->id.',%')
            ->whereDate('created_at', now()->toDateString())
            ->exists();
    }
}
