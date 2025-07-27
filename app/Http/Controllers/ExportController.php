<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exports\ProjectsExport;
use Maatwebsite\Excel\Facades\Excel;

class ExportController extends Controller
{
    public function export($status = null)
    {
        $filename = 'projects_export_' . now()->format('Ymd_His') . '.xlsx';
        return Excel::download(new ProjectsExport($status), $filename);
    }
}
