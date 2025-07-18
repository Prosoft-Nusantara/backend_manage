<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimProject extends Model
{
    use HasFactory;

    protected $table = 'tim_projects';

    protected $fillable = [
        'jenis_tim',
        'id_tim',
        'id_project',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class, 'id_project');
    }
}
