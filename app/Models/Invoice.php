<?php

namespace App\Models;

use App\Enums\InvoiceStatus;
use App\Enums\MetodePembayaran;
use App\Enums\StatusPembayaran;
use App\Enums\TipeDokumen;
use App\Services\InvoicePDFService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class Invoice extends Model
{
    protected $fillable = [
        'no_dokumen',
        'tipe_dokumen',
        'tgl_dokumen',
        'spb_id',
        'customer_id',
        'no_faktur_pajak',
        'total_nilai',
        'total_hpp',
        'total_profit',
        'metode_pembayaran',
        'top_hari',
        'tgl_jatuh_tempo',
        'status_pembayaran',
        'tgl_bayar',
        'jumlah_bayar',
        'file_ttd_gabungan',
        'status',
        'alasan_void',
        'voided_by',
        'voided_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'tipe_dokumen' => TipeDokumen::class,
            'tgl_dokumen' => 'date',
            'total_nilai' => 'decimal:2',
            'total_hpp' => 'decimal:2',
            'total_profit' => 'decimal:2',
            'metode_pembayaran' => MetodePembayaran::class,
            'top_hari' => 'integer',
            'tgl_jatuh_tempo' => 'date',
            'status_pembayaran' => StatusPembayaran::class,
            'tgl_bayar' => 'date',
            'jumlah_bayar' => 'decimal:2',
            'status' => InvoiceStatus::class,
            'voided_at' => 'datetime',
        ];
    }

    public function spb(): BelongsTo
    {
        return $this->belongsTo(Spb::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function voidedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'voided_by');
    }

    public function isVoidable(): bool
    {
        return $this->status !== InvoiceStatus::Void
            && $this->status_pembayaran === StatusPembayaran::Belum
            && (float) $this->jumlah_bayar <= 0;
    }

    public function hitungJatuhTempo(): ?Carbon
    {
        if ($this->metode_pembayaran !== MetodePembayaran::TOP || ! $this->top_hari) {
            return null;
        }

        return $this->tgl_dokumen?->copy()->addDays($this->top_hari);
    }

    public function isJatuhTempoH7(): bool
    {
        return $this->tgl_jatuh_tempo?->isSameDay(now()->addDays(7)) ?? false;
    }

    /**
     * @return array{total_nilai: float, total_hpp: float, total_profit: float}
     */
    public function getTotalFromSpb(): array
    {
        $this->loadMissing(['spb.items', 'spb.spbAble']);

        $totals = app(InvoicePDFService::class)->itemsForSpb($this->spb)
            ->reduce(function (array $carry, array $item): array {
                $carry['total_nilai'] += $item['jumlah'];
                $carry['total_hpp'] += $item['total_hpp'];
                $carry['total_profit'] += $item['profit'];

                return $carry;
            }, ['total_nilai' => 0.0, 'total_hpp' => 0.0, 'total_profit' => 0.0]);

        return $totals;
    }
}
