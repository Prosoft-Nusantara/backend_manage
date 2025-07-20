<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\TimProject;
use App\Models\Karyawan;
use App\Models\Operasional;
use App\Models\Coordinator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ProjectController extends Controller
{
    // 1. Melihat semua proyek
    public function getAllProjects()
    {
        try {
            $projects = Project::with('manager')->get();
            return response()->json(['id' => '1', 'data' => $projects]);
        } catch (\Throwable $th) {
            return response()->json(['id' => '0', 'data' => 'Gagal mengambil proyek']);
        }
    }

    public function getProjectsOnGoing()
    {
        try {
            $projects = Project::with('manager')->where('status', '0')->get();
            return response()->json(['id' => '1', 'data' => $projects]);
        } catch (\Throwable $th) {
            return response()->json(['id' => '0', 'data' => 'Gagal mengambil proyek']);
        }
    }

    public function getProjectsPiutang()
    {
        try {
            $projects = Project::with('manager')->where('status', '1')->get();
            return response()->json(['id' => '1', 'data' => $projects]);
        } catch (\Throwable $th) {
            return response()->json(['id' => '0', 'data' => 'Gagal mengambil proyek']);
        }
    }

    public function getProjectsLunas()
    {
        try {
            $projects = Project::with('manager')->where('status', '2')->get();
            return response()->json(['id' => '1', 'data' => $projects]);
        } catch (\Throwable $th) {
            return response()->json(['id' => '0', 'data' => 'Gagal mengambil proyek']);
        }
    }

    // 2. Melihat proyek berdasarkan manager
    public function getProjectsByManager($id)
    {
        try {
            $projects = Project::where('id_manager', $id)->with('manager')->get();
            return response()->json(['id' => '1', 'data' => $projects]);
        } catch (\Throwable $th) {
            return response()->json(['id' => '0', 'data' => 'Gagal mengambil proyek']);
        }
    }

    // 3. Membuat proyek (lampiran berupa file)
    // public function createProject(Request $request)
    // {
    //     try {
    //         $request->validate([
    //             'nama_proyek' => 'required|string',
    //             'client' => 'required|string',
    //             'total_nilai_kontrak' => 'required|numeric',
    //             'rencana_biaya' => 'required|numeric',
    //             'start_date' => 'required|date',
    //             'end_date' => 'required|date|after_or_equal:start_date',
    //             'kategori' => 'required|in:0,1',
    //             'id_manager' => 'required|exists:users,id',
    //             'lampiran_proyek' => 'nullable|file|mimes:pdf,doc,docx,xlsx,jpg,png',
    //         ]);

    //         $lampiranPath = null;
    //         if ($request->hasFile('lampiran_proyek')) {
    //             $lampiranPath = $request->file('lampiran_proyek')->store('lampiran_proyek', 'public');
    //         }

    //         $project = Project::create([
    //             'nama_proyek' => $request->nama_proyek,
    //             'client' => $request->client,
    //             'total_nilai_kontrak' => $request->total_nilai_kontrak,
    //             'realisasi_budget' => 0,
    //             'rencana_biaya' => $request->rencana_biaya,
    //             'start_date' => $request->start_date,
    //             'end_date' => $request->end_date,
    //             'id_manager' => $request->id_manager,
    //             'kategori' => $request->kategori,
    //             'lampiran_proyek' => $lampiranPath,
    //         ]);

    //         return response()->json(['id' => '1', 'data' => $project], 201);
    //     } catch (ValidationException $e) {
    //         return response()->json(['id' => '0', 'data' => $e->errors()], 422);
    //     } catch (\Throwable $th) {
    //         return response()->json(['id' => '0', 'data' => 'Gagal membuat proyek. Error: ' . $th->getMessage()], 500);
    //     }
    // }

public function createProject(Request $request)
{
    DB::beginTransaction();

    try {
        // Jika tim_project dikirim dalam bentuk JSON string
        $request->merge([
            'tim_project' => is_string($request->tim_project) ? json_decode($request->tim_project, true) : $request->tim_project
        ]);

        $request->validate([
            'nama_proyek' => 'required|string',
            'client' => 'required|string',
            'total_nilai_kontrak' => 'required|numeric',
            'rencana_biaya' => 'required|numeric',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'kategori' => 'required|in:0,1',
            'id_manager' => 'required|exists:users,id',
            'lampiran_proyek' => 'nullable|file|mimes:pdf,doc,docx,xlsx,jpg,png',
            'tim_project' => 'nullable|array',
            'tim_project.*.id_karyawan' => 'required|exists:karyawans,id',
        ]);

        // Proses upload lampiran jika ada
        $lampiranPath = null;
        if ($request->hasFile('lampiran_proyek')) {
            $lampiranPath = $request->file('lampiran_proyek')->store('lampiran_proyek', 'public');
        }

        // Buat data project
        $project = Project::create([
            'nama_proyek' => $request->nama_proyek,
            'client' => $request->client,
            'total_nilai_kontrak' => $request->total_nilai_kontrak,
            'realisasi_budget' => 0,
            'rencana_biaya' => $request->rencana_biaya,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'id_manager' => $request->id_manager,
            'kategori' => $request->kategori,
            'lampiran_proyek' => $lampiranPath,
        ]);

        // Simpan anggota tim proyek
        if ($request->has('tim_project')) {
            foreach ($request->tim_project as $tim) {
                TimProject::create([
                    'id_project' => $project->id,
                    'id_karyawan' => $tim['id_karyawan'],
                ]);
                // Catatan: `jenis_tim` tidak digunakan di tabel `tim_projects`, jadi diabaikan
            }
        }

        DB::commit();
        return response()->json(['id' => '1', 'data' => $project], 201);
    } catch (ValidationException $e) {
        DB::rollBack();
        return response()->json(['id' => '0', 'data' => $e->errors()], 422);
    } catch (\Throwable $th) {
        DB::rollBack();
        return response()->json(['id' => '0', 'data' => 'Gagal membuat proyek. Error: ' . $th->getMessage()], 500);
    }
}


    // 4. Menyelesaikan proyek (upload hasil)
    public function completeProject(Request $request, $id)
    {
        try {
            $project = Project::findOrFail($id);

            $request->validate([
                'hasil_proyek' => 'required|file|mimes:pdf,doc,docx,xlsx,jpg,png',
                'realisasi_budget' => 'required|numeric|min:0',
            ]);

            $hasilPath = $request->file('hasil_proyek')->store('hasil_proyek', 'public');

            $project->update([
                'status' => '1',
                'hasil_proyek' => $hasilPath,
                'realisasi_budget' => $request->realisasi_budget,
            ]);

            return response()->json(['id' => '1', 'data' => $project]);
        } catch (ValidationException $e) {
            return response()->json(['id' => '0', 'data' => $e->errors()], 422);
        } catch (\Throwable $th) {
            return response()->json(['id' => '0', 'data' => 'Gagal menyelesaikan proyek. Error: ' . $th->getMessage()], 500);
        }
    }

    // Invoice
    public function paymentProyek(Request $request, $id)
    {
        try {
            $project = Project::findOrFail($id);

            $request->validate([
                'invoice' => 'required|file|mimes:pdf,doc,docx,xlsx,jpg,png',
                'tanggal_pembayaran' => 'required|date',
            ]);

            $hasilPath = $request->file('invoice')->store('invoice', 'public');

            $project->update([
                'status' => '2',
                'invoice' => $hasilPath,
                'tanggal_pembayaran' => $request->tanggal_pembayaran,
            ]);

            return response()->json(['id' => '1', 'data' => $project]);
        } catch (ValidationException $e) {
            return response()->json(['id' => '0', 'data' => $e->errors()], 422);
        } catch (\Throwable $th) {
            return response()->json(['id' => '0', 'data' => 'Gagal menyelesaikan proyek. Error: ' . $th->getMessage()], 500);
        }
    }

    // 5. Update proyek
    public function updateProject(Request $request, $id)
    {
        try {
            $project = Project::findOrFail($id);

            $request->validate([
                'nama_proyek' => 'sometimes|string',
                'client' => 'sometimes|string',
                'total_nilai_kontrak' => 'sometimes|numeric',
                'rencana_biaya' => 'sometimes|numeric',
                'realisasi_budget' => 'sometimes|numeric',
                'start_date' => 'sometimes|date',
                'end_date' => 'sometimes|date|after_or_equal:start_date',
                'kategori' => 'required|in:0,1',
                // 'lampiran_proyek' => 'file|mimes:pdf,doc,docx,xlsx,jpg,png',
            ]);

            $data = $request->except('lampiran_proyek');

            if ($request->hasFile('lampiran_proyek')) {
                $data['lampiran_proyek'] = $request->file('lampiran_proyek')->store('lampiran_proyek', 'public');
            }

            $project->update($data);

            return response()->json(['id' => '1', 'data' => $project]);
        } catch (ValidationException $e) {
            return response()->json(['id' => '0', 'data' => $e->errors()], 422);
        } catch (\Throwable $th) {
            return response()->json(['id' => '0', 'data' => 'Gagal mengupdate proyek. Error: ' . $th->getMessage()], 500);
        }
    }

    // 6. Hapus proyek
    public function deleteProject($id)
    {
        try {
            $project = Project::findOrFail($id);
            $project->delete();

            return response()->json(['id' => '1', 'data' => 'Proyek berhasil dihapus']);
        } catch (\Throwable $th) {
            return response()->json(['id' => '0', 'data' => 'Gagal menghapus proyek. Error: ' . $th->getMessage()], 500);
        }
    }

    // 7. Detail proyek + tim
    public function detailProject($id)
    {
        try {
            $project = Project::with('manager', 'timProject')->findOrFail($id);

            // Tambahkan nama tim ke setiap anggota tim
            foreach ($project->timProject as $tim) {
                if ($tim->jenis_tim == '0') {
                    $coor = Coordinator::find($tim->id_tim);
                    $tim->nama_tim = $coor ? $coor->nama : 'Tidak ditemukan';
                } elseif ($tim->jenis_tim == '1') {
                    $ops = Operasional::find($tim->id_tim);
                    $tim->nama_tim = $ops ? $ops->nama : 'Tidak ditemukan';
                } else {
                    $tim->nama_tim = 'Tidak diketahui';
                }
            }

            return response()->json(['id' => '1', 'data' => $project]);
        } catch (\Throwable $th) {
            return response()->json(['id' => '0', 'data' => 'Gagal mengambil detail proyek']);
        }
    }

    public function listKaryawan()
    {
        // Ambil semua coordinator
        $coordinators = Coordinator::all()->map(function ($item) {
            return [
                'id' => $item->id,
                'jenis_nama' => 'coor_' . $item->nama,
                'nama' => $item->nama,
                'email' => $item->email,
                'no_hp' => $item->no_hp,
                'alamat' => $item->alamat,
                'jabatan' => 'Coordinator',
            ];
        });

        // Ambil semua operasional
        $operasionals = Operasional::all()->map(function ($item) {
            return [
                'id' => $item->id,
                'jenis_nama' => 'Ops_' . $item->nama,
                'nama' => $item->nama,
                'email' => $item->email,
                'no_hp' => $item->no_hp,
                'alamat' => $item->alamat,
                'jabatan' => 'Operasional',
            ];
        });

        // Gabungkan data
        $karyawan = $coordinators->merge($operasionals);

        return response()->json($karyawan);
    }

    // 8. Tambah tim ke proyek
    public function addTimToProject(Request $request, $id)
    {
        try {
            $request->validate([
                'jenis_tim' => 'required|in:0,1', // 0 = coordinator, 1 = operasional
                'id_tim' => 'required|integer',
            ]);

            $tim = TimProject::create([
                'id_project' => $id,
                'jenis_tim' => $request->jenis_tim,
                'id_tim' => $request->id_tim,
            ]);

            return response()->json(['id' => '1', 'data' => $tim], 201);
        } catch (ValidationException $e) {
            return response()->json(['id' => '0', 'data' => $e->errors()], 422);
        } catch (\Throwable $th) {
            return response()->json(['id' => '0', 'data' => 'Gagal menambahkan tim ke proyek. Error: ' . $th->getMessage()], 500);
        }
    }

    // 9. Hapus tim dari proyek
    public function deleteTimFromProject($tim_id)
    {
        try {
            $tim = TimProject::findOrFail($tim_id);
            $tim->delete();

            return response()->json(['id' => '1', 'data' => 'Tim berhasil dihapus dari proyek']);
        } catch (\Throwable $th) {
            return response()->json(['id' => '0', 'data' => 'Gagal menghapus tim dari proyek. Error: ' . $th->getMessage()], 500);
        }
    }
}
