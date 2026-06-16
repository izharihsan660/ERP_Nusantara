<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SpbItem extends Model
{
    protected $fillable = [
        'spb_id',
        'part_no',
        'deskripsi',
        'qty',
        'berat',
        'volume',
        'dimensi',
        'sku',
    ];

    protected function casts(): array
    {
        return [
            'qty' => 'integer',
            'berat' => 'decimal:2',
            'volume' => 'decimal:2',
        ];
    }

    public function spb(): BelongsTo
    {
        return $this->belongsTo(Spb::class);
    }
}
