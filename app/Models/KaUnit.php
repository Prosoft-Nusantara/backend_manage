<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KaUnit extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_unit',
        'deskripsi',
        'id_kepala_unit'
    ];
}
