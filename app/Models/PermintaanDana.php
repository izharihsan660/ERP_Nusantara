<?php

namespace App\Models;

use App\Enums\KategoriPD;
use App\Enums\PDStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PermintaanDana extends Model
{
    protected $table = 'permintaan_dana';

    protected $fillable = [
        'no_pd',
        'tgl_pd',
        'kategori',
        'nominal',
        'keterangan',
        'referensi_dokumen',
        'status',
        'submitted_at',
        'qr_token',
        'catatan_rejection',
        'approved_by',
        'approved_at',
        'tgl_realisasi',
        'jumlah_realisasi',
        'file_bukti',
        'voided_by',
        'voided_at',
        'alasan_void',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'tgl_pd' => 'date',
            'kategori' => KategoriPD::class,
            'nominal' => 'decimal:2',
            'status' => PDStatus::class,
            'submitted_at' => 'datetime',
            'approved_at' => 'datetime',
            'tgl_realisasi' => 'date',
            'jumlah_realisasi' => 'decimal:2',
            'voided_at' => 'datetime',
        ];
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
        return $query->where('status', PDStatus::Approved);
    }

    public function isApprovable(): bool
    {
        return $this->status === PDStatus::PendingApproval;
    }

    public function isVoidable(): bool
    {
        return ! in_array($this->status, [PDStatus::Void, PDStatus::Paid], true);
    }

    public function isPaid(): bool
    {
        return $this->status === PDStatus::Paid;
    }
}
