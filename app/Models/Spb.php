<?php

namespace App\Models;

use App\Enums\ReferensiTipe;
use App\Enums\SpbStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Spb extends Model
{
    protected $table = 'spb';

    protected $fillable = [
        'no_spb',
        'tgl_spb',
        'customer_id',
        'site_id',
        'template_id',
        'spb_able_type',
        'spb_able_id',
        'referensi_tipe',
        'no_referensi',
        'nama_ekspedisi',
        'etd',
        'eta',
        'catatan',
        'status',
        'voided_by',
        'voided_at',
        'alasan_void',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'tgl_spb' => 'date',
            'etd' => 'date',
            'eta' => 'date',
            'referensi_tipe' => ReferensiTipe::class,
            'status' => SpbStatus::class,
            'voided_at' => 'datetime',
        ];
    }

    public function spbAble(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'spb_able_type', 'spb_able_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(DocumentTemplate::class, 'template_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(SpbItem::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function voidedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'voided_by');
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }

    public function isVoidable(): bool
    {
        return $this->status !== SpbStatus::Void && ! $this->hasActiveInvoice();
    }

    public function isParsial(): bool
    {
        if (! $this->spb_able_type || ! $this->spb_able_id) {
            return false;
        }

        return static::query()
            ->where('spb_able_type', $this->spb_able_type)
            ->where('spb_able_id', $this->spb_able_id)
            ->where('status', '!=', SpbStatus::Void->value)
            ->count() > 1;
    }

    public function scopeShipped(Builder $query): Builder
    {
        return $query->where('status', SpbStatus::Shipped->value);
    }

    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('status', SpbStatus::Draft->value);
    }

    private function hasActiveInvoice(): bool
    {
        if (! class_exists(Invoice::class)) {
            return false;
        }

        return $this->invoice()
            ->where('status', '!=', 'VOID')
            ->exists();
    }
}
