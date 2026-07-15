<?php

namespace App\Console\Commands;

use App\Enums\InvoiceStatus;
use App\Enums\StatusPembayaran;
use App\Models\Invoice;
use Illuminate\Console\Command;

class RecheckInvoicePayments extends Command
{
    protected $signature = 'invoice:recheck-payments {--apply : Perbaiki status pembayaran yang tidak sesuai}';

    protected $description = 'Periksa ulang status pembayaran invoice berdasarkan jumlah bayar dan grand total';

    public function handle(): int
    {
        $mismatches = Invoice::query()->where('status', '!=', InvoiceStatus::Void->value)->orderBy('id')->get()
            ->map(function (Invoice $invoice): ?array {
                $expected = match (true) {
                    (float) $invoice->jumlah_bayar > 0 && (float) $invoice->jumlah_bayar >= (float) $invoice->grand_total => StatusPembayaran::Lunas,
                    (float) $invoice->jumlah_bayar > 0 => StatusPembayaran::Sebagian,
                    default => StatusPembayaran::Belum,
                };

                return $invoice->status_pembayaran === $expected ? null : compact('invoice', 'expected');
            })->filter()->values();

        $this->table(['ID', 'No. Dokumen', 'Jumlah Bayar', 'Grand Total', 'Status Saat Ini', 'Status Seharusnya'], $mismatches->map(fn (array $row): array => [
            $row['invoice']->id, $row['invoice']->no_dokumen, $row['invoice']->jumlah_bayar, $row['invoice']->grand_total,
            $row['invoice']->status_pembayaran->value, $row['expected']->value,
        ]));

        if ($this->option('apply')) {
            $mismatches->each(fn (array $row) => $row['invoice']->update(['status_pembayaran' => $row['expected']]));
            $this->info("{$mismatches->count()} status pembayaran diperbaiki.");
        } else {
            $this->info("Dry-run: {$mismatches->count()} invoice perlu diperiksa. Gunakan --apply untuk memperbaiki.");
        }

        return self::SUCCESS;
    }
}
