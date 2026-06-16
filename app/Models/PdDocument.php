<?php

namespace App\Models;

use App\Enums\PdDocumentKategori;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PdDocument extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'permintaan_dana_id',
        'kategori',
        'file_path',
        'nama_file',
    ];

    protected function casts(): array
    {
        return [
            'kategori' => PdDocumentKategori::class,
        ];
    }

    public function permintaanDana(): BelongsTo
    {
        return $this->belongsTo(PermintaanDana::class);
    }
}
