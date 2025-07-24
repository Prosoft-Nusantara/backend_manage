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
        'biaya',
        'start_date',
        'end_date',
        'status',
        'id_project',
    ];
}
