<?php

namespace App\Models;

use App\Enums\PurchaseOrderStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseOrder extends Model
{
    protected $fillable = [
        'no_purchase_order',
        'tgl_po',
        'customer_id',
        'vendor_id',
        'no_pr_customer',
        'no_po_customer',
        'status',
        'qr_token',
        'catatan',
        'approved_by',
        'approved_at',
        'voided_by',
        'voided_at',
        'alasan_void',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'tgl_po' => 'date',
            'status' => PurchaseOrderStatus::class,
            'approved_at' => 'datetime',
            'voided_at' => 'datetime',
        ];
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function spb(): HasMany
    {
        return $this->hasMany(Spb::class, 'spb_able_id')
            ->where('spb_able_type', PurchaseOrder::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function voidedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'voided_by');
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', PurchaseOrderStatus::Approved);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', PurchaseOrderStatus::PendingApproval);
    }

    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('status', PurchaseOrderStatus::Draft);
    }

    public function isApprovable(): bool
    {
        return $this->status === PurchaseOrderStatus::PendingApproval;
    }

    public function isVoidable(): bool
    {
        return $this->status !== PurchaseOrderStatus::Void;
    }

    public function getTotalAttribute(): float
    {
        return (float) $this->items->sum('jumlah');
    }
}
