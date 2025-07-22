<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;

class DashboardController extends Controller
{
    public function highlightProject()
    {
        // Total semua proyek
        $projectCount = [
            'jumlah_proyek' => Project::count(),
            'total_nilai_kontrak' => Project::sum('total_nilai_kontrak'),
            'realisasi_budget' => Project::sum('realisasi_budget'),
            'margin' => Project::sum('total_nilai_kontrak') - Project::sum('realisasi_budget')
        ];

        // Proyek Ongoing (status = 0)
        $projectOngoing = [
            'jumlah_proyek' => Project::where('status', '0')->count(),
            'total_nilai_kontrak' => Project::where('status', '0')->sum('total_nilai_kontrak'),
            'realisasi_budget' => Project::where('status', '0')->sum('realisasi_budget'),
            'margin' => Project::where('status', '0')->sum('total_nilai_kontrak') - Project::where('status', '0')->sum('realisasi_budget')
        ];

        // Proyek Completed (status = 1)
        $projectCompleted = [
            'jumlah_proyek' => Project::where('status', '1')->count(),
            'total_nilai_kontrak' => Project::where('status', '1')->sum('total_nilai_kontrak'),
            'realisasi_budget' => Project::where('status', '1')->sum('realisasi_budget'),
            'margin' => Project::where('status', '1')->sum('total_nilai_kontrak') - Project::where('status', '1')->sum('realisasi_budget')
        ];

        // Proyek Lunas (status = 2)
        $projectLunas = [
            'jumlah_proyek' => Project::where('status', '2')->count(),
            'total_nilai_kontrak' => Project::where('status', '2')->sum('total_nilai_kontrak'),
            'realisasi_budget' => Project::where('status', '2')->sum('realisasi_budget'),
            'margin' => Project::where('status', '2')->sum('total_nilai_kontrak') - Project::where('status', '2')->sum('realisasi_budget')
        ];

        return response()->json([
            'projectCount' => $projectCount,
            'projectOngoing' => $projectOngoing,
            'projectCompleted' => $projectCompleted,
            'projectLunas' => $projectLunas
        ]);
    }
}
