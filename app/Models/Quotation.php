<?php

namespace App\Models;

use App\Enums\QuotationStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Quotation extends Model
{
    protected $fillable = [
        'no_quotation',
        'tgl_quotation',
        'customer_id',
        'template_id',
        'revisi',
        'status',
        'catatan',
        'perihal',
        'metode_pembayaran',
        'masa_berlaku',
        'catatan_rejection',
        'qr_token',
        'generated_pdf_path',
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
            'tgl_quotation' => 'date',
            'masa_berlaku' => 'date',
            'revisi' => 'integer',
            'status' => QuotationStatus::class,
            'approved_at' => 'datetime',
            'voided_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(DocumentTemplate::class, 'template_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(QuotationItem::class);
    }

    public function salesOrder(): HasOne
    {
        return $this->hasOne(SalesOrder::class);
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
        return $query->where('status', QuotationStatus::Approved);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', QuotationStatus::PendingApproval);
    }

    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('status', QuotationStatus::Draft);
    }

    public function isVoidable(): bool
    {
        return $this->status !== QuotationStatus::Void;
    }

    public function isApprovable(): bool
    {
        return $this->status === QuotationStatus::PendingApproval;
    }

    public function getTotalAttribute(): float
    {
        return (float) $this->items->sum('jumlah');
    }

    public function getTotalHppAttribute(): float
    {
        return (float) $this->items->sum(fn (QuotationItem $item): float => $item->qty * (float) $item->hpp_satuan);
    }

    public function getTotalProfitAttribute(): float
    {
        return (float) $this->items->sum('profit');
    }
}
