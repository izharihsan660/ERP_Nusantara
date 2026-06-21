<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WipItem extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'wip_order_id',
        'katalog_id',
        'part_no',
        'deskripsi',
        'qty',
    ];

    protected function casts(): array
    {
        return [
            'qty' => 'integer',
        ];
    }

    public function wipOrder(): BelongsTo
    {
        return $this->belongsTo(WipOrder::class);
    }
}
