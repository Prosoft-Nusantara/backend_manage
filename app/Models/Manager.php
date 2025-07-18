<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Manager extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_manager',
        'deskripsi',
        'id_manager',
        'id_divisi',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_manager');
    }

    public function divisi()
    {
        return $this->belongsTo(Divisi::class, 'id_divisi');
    }

    public function projects()
    {
        return $this->hasMany(Project::class, 'id_manager');
    }

    public function operasionals()
    {
        return $this->hasMany(Operasional::class, 'id_manager');
    }

    public function coordinators()
    {
        return $this->hasMany(Coordinator::class, 'id_manager');
    }
}
