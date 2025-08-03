<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BiayaAktivitas extends Model
{
    use HasFactory;

    protected $fillable = [
        'keterangan',
        'biaya',
        'start_date',
        'end_date',
        'realisasi_biaya',
        'realisasi_start_date',
        'realisasi_end_date',
        'id_aktivitas',
    ];
}
