<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesOrderDocument extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'sales_order_id',
        'file_path',
        'nama_file',
    ];

    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class);
    }
}
