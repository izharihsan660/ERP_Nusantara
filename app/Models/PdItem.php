<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PdItem extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'permintaan_dana_id',
        'no_po',
        'no_part',
        'description',
        'qty',
        'harga',
        'total',
    ];

    protected function casts(): array
    {
        return [
            'qty' => 'integer',
            'harga' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    public function permintaanDana(): BelongsTo
    {
        return $this->belongsTo(PermintaanDana::class);
    }
}
