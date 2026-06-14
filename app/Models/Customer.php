<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    protected $fillable = [
        'kode_customer',
        'nama_customer',
        'alamat',
        'kota',
        'npwp',
        'pic_name',
        'pic_email',
        'pic_phone',
        'template_quotation_id',
        'template_spb_id',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function quotationTemplate(): BelongsTo
    {
        return $this->belongsTo(DocumentTemplate::class, 'template_quotation_id');
    }

    public function spbTemplate(): BelongsTo
    {
        return $this->belongsTo(DocumentTemplate::class, 'template_spb_id');
    }

    public function sites(): HasMany
    {
        return $this->hasMany(Site::class);
    }

    public function quotations(): HasMany
    {
        return $this->hasMany(Quotation::class);
    }
}
