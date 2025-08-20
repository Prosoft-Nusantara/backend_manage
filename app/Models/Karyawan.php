<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Karyawan extends Model
{
    use HasFactory;

     protected $fillable = [
        'nama',
        'alamat',
        'email',
        'no_hp',
        'jabatan',
        'id_manager',
        'id_user',
    ];

    public function manager()
    {
        return $this->belongsTo(Manager::class, 'id_manager');
    }
}
