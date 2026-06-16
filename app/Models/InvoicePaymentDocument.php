<?php

namespace App\Models;

use App\Enums\InvoicePaymentDocumentType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoicePaymentDocument extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'invoice_id',
        'tipe_dokumen',
        'file_path',
        'nama_file',
    ];

    protected function casts(): array
    {
        return [
            'tipe_dokumen' => InvoicePaymentDocumentType::class,
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}
