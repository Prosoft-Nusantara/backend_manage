<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_proyek',
        'client',
        'total_nilai_kontrak',
        'rencana_biaya',
        'realisasi_budget',
        'tanggal_pembayaran',
        'invoice', // file
        'start_date',
        'end_date',
        'lampiran_proyek', // file
        'status',
        'kategori',
        'hasil_proyek', // file
        'biaya_akomodasi',
        'pihak_pemberi_biaya',
        'id_manager',
    ];

    public function manager()
    {
        return $this->belongsTo(User::class, 'id_manager');
    }

    public function timProject()
    {
        return $this->hasMany(TimProject::class, 'id_project');
    }

    public function aktifitasProject()
    {
        return $this->hasMany(Aktifitas::class, 'id_project');
    }
}
