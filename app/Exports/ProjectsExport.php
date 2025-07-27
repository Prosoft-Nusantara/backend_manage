<?php

namespace App\Exports;

use App\Models\Project;
use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Contracts\Support\Responsable;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ProjectsExport implements FromCollection, WithHeadings
{
    protected $status;

    public function __construct($status = null)
    {
        $this->status = $status;
    }

    public function collection()
    {
        $query = Project::query();

        if ($this->status !== null && in_array($this->status, ['0', '1', '2'])) {
            $query->where('status', $this->status);
        }

        return $query->get([
            'id', 'nama_proyek', 'client', 'total_nilai_kontrak', 'rencana_biaya',
            'realisasi_budget', 'tanggal_pembayaran', 'invoice', 'start_date',
            'end_date', 'status', 'kategori', 'created_at'
        ]);
    }

    public function headings(): array
    {
        return [
            'ID', 'Nama Proyek', 'Client', 'Total Nilai Kontrak', 'Rencana Biaya',
            'Realisasi Budget', 'Tanggal Pembayaran', 'Invoice', 'Start Date',
            'End Date', 'Status', 'Kategori', 'Created At'
        ];
    }
}

