<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Aktifitas extends Model
{
    use HasFactory;

    protected $fillable = [
        'aktivitas',
        'pic',
        'status',
        'file',
        'id_project',
    ];

    public function biayaAktivitas()
    {
        return $this->hasMany(BiayaAktivitas::class, 'id_aktivitas');
    }
}
