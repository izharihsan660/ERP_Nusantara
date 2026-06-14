<?php

namespace App\Models;

use App\Enums\DocumentType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class DocumentTemplate extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'nama_template',
        'kode_template',
        'tipe_dokumen',
        'blade_file',
        'is_default',
        'keterangan',
    ];

    protected function casts(): array
    {
        return [
            'tipe_dokumen' => DocumentType::class,
            'is_default' => 'boolean',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query;
    }

    public function customersUsingQuotation(): HasMany
    {
        return $this->hasMany(Customer::class, 'template_quotation_id');
    }

    public function customersUsingSpb(): HasMany
    {
        return $this->hasMany(Customer::class, 'template_spb_id');
    }
}
