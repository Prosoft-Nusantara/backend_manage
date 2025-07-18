<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Divisi extends Model
{
    use HasFactory;

    protected $table = 'divisis';

    protected $fillable = [
        'nama_divisi',
        'deskripsi',
        'id_kepala_divisi',
    ];

    public function kepalaDivisi()
    {
        return $this->belongsTo(User::class, 'id_kepala_divisi');
    }
}
