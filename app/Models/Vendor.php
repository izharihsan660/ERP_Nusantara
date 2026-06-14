<?php

namespace App\Models;

use App\Enums\VendorType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vendor extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'tipe_vendor',
        'nama_vendor',
        'alamat',
        'pic_name',
        'pic_email',
        'rekening',
        'keterangan',
    ];

    protected function casts(): array
    {
        return [
            'tipe_vendor' => VendorType::class,
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query;
    }
}
