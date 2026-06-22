<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuotationItem extends Model
{
    protected $fillable = [
        'quotation_id',
        'katalog_id',
        'part_no',
        'deskripsi',
        'qty',
        'satuan',
        'harga_satuan',
        'hpp_satuan',
        'jumlah',
        'status',
        'profit',
    ];

    protected function casts(): array
    {
        return [
            'qty' => 'integer',
            'harga_satuan' => 'decimal:2',
            'hpp_satuan' => 'decimal:2',
            'jumlah' => 'decimal:2',
            'profit' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (QuotationItem $item): void {
            $item->jumlah = $item->qty * $item->harga_satuan;
            $item->profit = $item->jumlah - ($item->qty * $item->hpp_satuan);
        });
    }

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    public function katalog(): BelongsTo
    {
        return $this->belongsTo(Katalog::class);
    }
}
