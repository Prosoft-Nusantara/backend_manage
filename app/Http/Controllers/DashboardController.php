<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;

class DashboardController extends Controller
{
    public function highlightProject()
    {
        $projectCount = Project::count();
        $projectOngoing = Project::where('status', '0')->count();
        $projectCompleted = Project::where('status', '1')->count();
        $projectLunas = Project::where('status', '2')->count();
        $totalKeuntungan = Project::sum('total_nilai_kontrak') - Project::sum('realisasi_budget');
        $totalRealisasi = Project::sum('realisasi_budget');

        return response()->json([
            'projectCount' => $projectCount,
            'projectOngoing' => $projectOngoing,
            'projectCompleted' => $projectCompleted,
            'projectLunas' => $projectLunas,
            'totalKeuntungan' => $totalKeuntungan,
            'totalRealisasi' => $totalRealisasi
        ]);
    }
}
