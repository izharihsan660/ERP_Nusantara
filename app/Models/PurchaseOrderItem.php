<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseOrderItem extends Model
{
    protected $fillable = [
        'purchase_order_id',
        'katalog_id',
        'deskripsi',
        'qty',
        'satuan',
        'harga_satuan',
        'jumlah',
    ];

    protected function casts(): array
    {
        return [
            'qty' => 'integer',
            'harga_satuan' => 'decimal:2',
            'jumlah' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (PurchaseOrderItem $item): void {
            $item->jumlah = $item->qty * $item->harga_satuan;
        });
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function katalog(): BelongsTo
    {
        return $this->belongsTo(Katalog::class);
    }

    public function getJumlahAttribute(mixed $value): float
    {
        if (! array_key_exists('qty', $this->attributes) || ! array_key_exists('harga_satuan', $this->attributes)) {
            return (float) $value;
        }

        return (float) $this->attributes['qty'] * (float) $this->attributes['harga_satuan'];
    }
}
