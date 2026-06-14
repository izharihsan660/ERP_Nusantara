<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentNumber extends Model
{
    protected $fillable = [
        'tipe_dokumen',
        'tahun',
        'bulan',
        'last_number',
    ];
}
