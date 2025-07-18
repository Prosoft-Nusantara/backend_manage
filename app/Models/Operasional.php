<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Operasional extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama',
        'alamat',
        'email',
        'no_hp',
        'id_manager',
    ];

    public function manager()
    {
        return $this->belongsTo(Manager::class, 'id_manager');
    }
}
