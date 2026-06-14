<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Katalog extends Model
{
    protected $table = 'katalog';

    protected $fillable = [
        'part_no',
        'nama_barang',
        'spesifikasi',
        'satuan',
        'hpp',
        'harga_jual_default',
        'kategori',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'hpp' => 'decimal:2',
            'harga_jual_default' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function quotationItems(): HasMany
    {
        return $this->hasMany(QuotationItem::class);
    }
}
