<?php

namespace App\Models;

use App\Enums\StatusSupply;
use App\Enums\TipeOrder;
use App\Enums\WIPStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WipOrder extends Model
{
    protected $fillable = [
        'sales_order_id',
        'no_wip',
        'tipe_order',
        'nama_ekspedisi',
        'status_supply',
        'tersupply_at',
        'status',
        'alasan_void',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'tipe_order' => TipeOrder::class,
            'status_supply' => StatusSupply::class,
            'tersupply_at' => 'datetime',
            'status' => WIPStatus::class,
        ];
    }

    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function spb(): HasMany
    {
        return $this->hasMany(Spb::class, 'spb_able_id')
            ->where('spb_able_type', WipOrder::class);
    }

    public function isVoidable(): bool
    {
        return $this->status === WIPStatus::Active && $this->status_supply !== StatusSupply::Tersupply;
    }

    public function isVOR(): bool
    {
        return $this->tipe_order === TipeOrder::VOR;
    }
}
