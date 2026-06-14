<?php

namespace App\Models;

use App\Enums\MetodePembayaran;
use App\Enums\POStatus;
use App\Enums\WIPStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class PurchaseOrder extends Model
{
    protected $fillable = [
        'quotation_id',
        'customer_id',
        'no_po_customer',
        'no_pr_customer',
        'tgl_po',
        'metode_pembayaran',
        'top_hari',
        'status',
        'alasan_void',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'tgl_po' => 'date',
            'top_hari' => 'integer',
            'metode_pembayaran' => MetodePembayaran::class,
            'status' => POStatus::class,
        ];
    }

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function wipOrders(): HasMany
    {
        return $this->hasMany(WipOrder::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isVoidable(): bool
    {
        return $this->status === POStatus::Open && ! $this->hasWIP();
    }

    public function hasWIP(): bool
    {
        return $this->wipOrders()
            ->where('status', WIPStatus::Active)
            ->exists();
    }

    public function getTglJatuhTempo(): ?Carbon
    {
        if ($this->metode_pembayaran !== MetodePembayaran::TOP || ! $this->top_hari) {
            return null;
        }

        return $this->tgl_po?->copy()->addDays($this->top_hari);
    }
}
