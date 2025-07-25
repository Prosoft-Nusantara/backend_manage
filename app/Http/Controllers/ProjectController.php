<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\TimProject;
use App\Models\Aktifitas;
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


    public function createProject(Request $request)
    {
        // Decode JSON string jika ada
        $request->merge([
            'tim_project' => is_string($request->tim_project) ? json_decode($request->tim_project, true) : $request->tim_project,
            'aktifitas'   => is_string($request->aktifitas)   ? json_decode($request->aktifitas, true)   : $request->aktifitas,
        ]);

        try {
            $validated = $this->validateCreate($request);

            DB::beginTransaction();

            // Upload lampiran
            $lampiranPath = null;
            if ($request->hasFile('lampiran_proyek')) {
                $lampiranPath = $request->file('lampiran_proyek')->store('lampiran_proyek', 'public');
            }

            // Data proyek
            $data = [
                'nama_proyek'         => $validated['nama_proyek'],
                'client'              => $validated['client'],
                'total_nilai_kontrak' => $validated['total_nilai_kontrak'],
                'rencana_biaya'       => $validated['rencana_biaya'],
                'realisasi_budget'    => 0,
                'start_date'          => $validated['start_date'],
                'end_date'            => $validated['end_date'],
                'id_manager'          => $validated['id_manager'],
                'kategori'            => $validated['kategori'],
                'lampiran_proyek'     => $lampiranPath,
            ];

            if ($validated['kategori'] == '0') {
                $data['biaya_akomodasi']     = $validated['biaya_akomodasi'];
                $data['pihak_pemberi_biaya'] = $validated['pihak_pemberi_biaya'];
            }

            $project = Project::create($data);

            // Tim
            if (!empty($validated['tim_project'])) {
                $rows = collect($validated['tim_project'])
                    ->map(fn($t) => ['id_project' => $project->id, 'id_karyawan' => $t['id_karyawan']])
                    ->toArray();
                TimProject::insert($rows);
            }

            // Aktifitas
            if (!empty($validated['aktifitas'])) {
                $rows = collect($validated['aktifitas'])
                    ->map(fn($a) => array_merge($a, ['id_project' => $project->id]))
                    ->toArray();
                Aktifitas::insert($rows);
            }

            DB::commit();
            return response()->json(['id' => '1', 'message' => 'Proyek berhasil dibuat.', 'data' => $project], 201);
        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json(['id' => '0', 'data' => $e->errors()], 422);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['id' => '0', 'data' => 'Gagal membuat proyek. Error: ' . $th->getMessage()], 500);
        }
    }

    /* ---------- UPDATE ---------- */
    public function updateProject(Request $request, $id)
    {
        $request->merge([
            'tim_project' => is_string($request->tim_project) ? json_decode($request->tim_project, true) : $request->tim_project,
            'aktifitas'   => is_string($request->aktifitas)   ? json_decode($request->aktifitas, true)   : $request->aktifitas,
        ]);

        try {
            $validated = $this->validateUpdate($request);

            DB::beginTransaction();

            $project = Project::findOrFail($id);

            // Data umum
            $data = $request->except(['lampiran_proyek', 'tim_project', 'aktifitas']);

            // Upload & hapus lampiran lama
            if ($request->hasFile('lampiran_proyek')) {
                if ($project->lampiran_proyek) {
                    Storage::disk('public')->delete($project->lampiran_proyek);
                }
                $data['lampiran_proyek'] = $request->file('lampiran_proyek')->store('lampiran_proyek', 'public');
            }

            $project->update($data);

            // Tim
            if ($request->has('tim_project')) {
                TimProject::where('id_project', $project->id)->delete();
                if (!empty($validated['tim_project'])) {
                    $rows = collect($validated['tim_project'])
                        ->map(fn($t) => ['id_project' => $project->id, 'id_karyawan' => $t['id_karyawan']])
                        ->toArray();
                    TimProject::insert($rows);
                }
            }

            // Aktifitas
            if ($request->has('aktifitas')) {
                Aktifitas::where('id_project', $project->id)->delete();
                if (!empty($validated['aktifitas'])) {
                    $rows = collect($validated['aktifitas'])
                        ->map(fn($a) => array_merge($a, ['id_project' => $project->id]))
                        ->toArray();
                    Aktifitas::insert($rows);
                }
            }

            DB::commit();
            return response()->json(['id' => '1', 'message' => 'Proyek berhasil diperbarui.', 'data' => $project]);
        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json(['id' => '0', 'data' => $e->errors()], 422);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['id' => '0', 'data' => 'Gagal mengupdate proyek. Error: ' . $th->getMessage()], 500);
        }
    }

    /* ---------- VALIDASI CREATE ---------- */
    private function validateCreate(Request $request)
    {
        return $request->validate([
            'nama_proyek'         => 'required|string|max:255',
            'client'              => 'required|string|max:255',
            'total_nilai_kontrak' => 'required|numeric|min:0',
            'rencana_biaya'       => 'required|numeric|min:0',
            'start_date'          => 'required|date',
            'end_date'            => 'required|date|after_or_equal:start_date',
            'kategori'            => 'required|in:0,1',
            'id_manager'          => 'required|exists:users,id',
            'lampiran_proyek'     => 'nullable|file|mimes:pdf,doc,docx,xlsx,jpg,png|max:5120',

            'biaya_akomodasi'     => 'required_if:kategori,0|numeric|min:0',
            'pihak_pemberi_biaya' => 'required_if:kategori,0|string|max:255',

            'tim_project'               => 'nullable|array',
            'tim_project.*'             => 'array:id_karyawan',
            'tim_project.*.id_karyawan' => 'required|exists:karyawans,id',

            'aktifitas'                        => 'nullable|array',
            'aktifitas.*'                      => 'array:aktivitas,pic,biaya,start_date,end_date',
            'aktifitas.*.aktivitas'            => 'required|string|max:255',
            'aktifitas.*.pic'                  => 'required|string|max:255',
            'aktifitas.*.biaya'                => 'required|numeric|min:0',
            'aktifitas.*.start_date'           => 'required|date',
            'aktifitas.*.end_date'             => 'required|date|after_or_equal:aktifitas.*.start_date',
        ]);
    }

    /* ---------- VALIDASI UPDATE ---------- */
    private function validateUpdate(Request $request)
    {
        return $request->validate([
            'nama_proyek'         => 'sometimes|string|max:255',
            'client'              => 'sometimes|string|max:255',
            'total_nilai_kontrak' => 'sometimes|numeric|min:0',
            'rencana_biaya'       => 'sometimes|numeric|min:0',
            'realisasi_budget'    => 'sometimes|numeric|min:0',
            'start_date'          => 'sometimes|date',
            'end_date'            => 'sometimes|date|after_or_equal:start_date',
            'kategori'            => 'required|in:0,1',
            'lampiran_proyek'     => 'nullable|file|mimes:pdf,doc,docx,xlsx,jpg,png|max:5120',

            'biaya_akomodasi'     => 'sometimes|required_if:kategori,0|numeric|min:0',
            'pihak_pemberi_biaya' => 'sometimes|required_if:kategori,0|string|max:255',

            'tim_project'               => 'nullable|array',
            'tim_project.*'             => 'array:id_karyawan',
            'tim_project.*.id_karyawan' => 'required|exists:karyawans,id',

            'aktifitas'                        => 'nullable|array',
            'aktifitas.*'                      => 'array:aktivitas,pic,biaya,start_date,end_date',
            'aktifitas.*.aktivitas'            => 'required|string|max:255',
            'aktifitas.*.pic'                  => 'required|string|max:255',
            'aktifitas.*.biaya'                => 'required|numeric|min:0',
            'aktifitas.*.start_date'           => 'required|date',
            'aktifitas.*.end_date'             => 'required|date|after_or_equal:aktifitas.*.start_date',
        ]);
    }

    public function selesaikanAktifitas(Request $request, $id)
    {
        try {
            $validateData = $request->validate([
                'file' => 'required|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:2048'
            ]);
            $aktifitas = Aktifitas::findOrFail($id);

            $hasilPath = $request->file('file')->store('file_aktifitas', 'public');
            if ($aktifitas->status == '1') {
                return response()->json(['id' => '0', 'data' => 'Aktifitas sudah selesai.'], 500);
            }
            $aktifitas->update([
                'status' => '1',
                'file' => $hasilPath
            ]);
            return response()->json(['id' => '1', 'data' => 'Aktifitas selesai.']);
        } catch (\Throwable $th) {
            return response()->json(['id' => '0', 'data' => 'Gagal selesaikan aktifitas.'], 500);
        }
    }

    // 3. Membuat proyek (lampiran berupa file)

    // public function createProject(Request $request)
    // {
    //     DB::beginTransaction();

    //     try {
    //         // Jika tim_project dikirim dalam bentuk JSON string
    //         $request->merge([
    //             'tim_project' => is_string($request->tim_project) ? json_decode($request->tim_project, true) : $request->tim_project
    //         ]);

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
    //             'tim_project' => 'nullable|array',
    //             'tim_project.*.id_karyawan' => 'required|exists:karyawans,id',
    //         ]);

    //         // Proses upload lampiran jika ada
    //         $lampiranPath = null;
    //         if ($request->hasFile('lampiran_proyek')) {
    //             $lampiranPath = $request->file('lampiran_proyek')->store('lampiran_proyek', 'public');
    //         }

    //         // Buat data project
    //         $data = [
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
    //         ];

    //         // Jika kategori = '0', tambahkan key tambahan
    //         if ($request->kategori == '0') {
    //             $data['biaya_akomodasi'] = $request->biaya_akomodasi;
    //             $data['pihak_pemberi_biaya'] = $request->pihak_pemberi_biaya;
    //         }

    //         $project = Project::create($data);


    //         // Simpan anggota tim proyek
    //         if ($request->has('tim_project')) {
    //             foreach ($request->tim_project as $tim) {
    //                 TimProject::create([
    //                     'id_project' => $project->id,
    //                     'id_karyawan' => $tim['id_karyawan'],
    //                 ]);
    //                 // Catatan: `jenis_tim` tidak digunakan di tabel `tim_projects`, jadi diabaikan
    //             }
    //         }

    //         DB::commit();
    //         return response()->json(['id' => '1', 'data' => $project], 201);
    //     } catch (ValidationException $e) {
    //         DB::rollBack();
    //         return response()->json(['id' => '0', 'data' => $e->errors()], 422);
    //     } catch (\Throwable $th) {
    //         DB::rollBack();
    //         return response()->json(['id' => '0', 'data' => 'Gagal membuat proyek. Error: ' . $th->getMessage()], 500);
    //     }
    // }

    // public function createProject(Request $request)
    // {
    //     DB::beginTransaction();

    //     try {
    //         // Parsing jika tim_project atau aktifitas berupa string JSON
    //         $request->merge([
    //             'tim_project' => is_string($request->tim_project) ? json_decode($request->tim_project, true) : $request->tim_project,
    //             'aktifitas' => is_string($request->aktifitas) ? json_decode($request->aktifitas, true) : $request->aktifitas
    //         ]);

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
    //             'tim_project' => 'nullable|array',
    //             'tim_project.*.id_karyawan' => 'required|exists:karyawans,id',
    //             'aktifitas' => 'nullable|array',
    //             'aktifitas.*.aktivitas' => 'required|string',
    //             'aktifitas.*.pic' => 'required|string',
    //             'aktifitas.*.biaya' => 'required|numeric',
    //             'aktifitas.*.start_date' => 'required|date',
    //             'aktifitas.*.end_date' => 'required|date|after_or_equal:aktifitas.*.start_date',
    //         ]);

    //         // Upload file jika ada
    //         $lampiranPath = null;
    //         if ($request->hasFile('lampiran_proyek')) {
    //             $lampiranPath = $request->file('lampiran_proyek')->store('lampiran_proyek', 'public');
    //         }

    //         // Buat proyek
    //         $data = [
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
    //         ];

    //         if ($request->kategori == '0') {
    //             $data['biaya_akomodasi'] = $request->biaya_akomodasi;
    //             $data['pihak_pemberi_biaya'] = $request->pihak_pemberi_biaya;
    //         }

    //         $project = Project::create($data);

    //         // Simpan tim proyek
    //         if (!empty($request->tim_project)) {
    //             foreach ($request->tim_project as $tim) {
    //                 TimProject::create([
    //                     'id_project' => $project->id,
    //                     'id_karyawan' => $tim['id_karyawan'],
    //                 ]);
    //             }
    //         }

    //         // Simpan aktifitas proyek
    //         if (!empty($request->aktifitas)) {
    //             foreach ($request->aktifitas as $act) {
    //                 Aktifitas::create([
    //                     'aktivitas' => $act['aktivitas'],
    //                     'pic' => $act['pic'],
    //                     'biaya' => $act['biaya'],
    //                     'start_date' => $act['start_date'],
    //                     'end_date' => $act['end_date'],
    //                     'id_project' => $project->id,
    //                 ]);
    //             }
    //         }

    //         DB::commit();
    //         return response()->json(['id' => '1', 'data' => $project], 201);
    //     } catch (ValidationException $e) {
    //         DB::rollBack();
    //         return response()->json(['id' => '0', 'data' => $e->errors()], 422);
    //     } catch (\Throwable $th) {
    //         DB::rollBack();
    //         return response()->json(['id' => '0', 'data' => 'Gagal membuat proyek. Error: ' . $th->getMessage()], 500);
    //     }
    // }



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
    // public function updateProject(Request $request, $id)
    // {
    //     DB::beginTransaction();

    //     try {
    //         $project = Project::findOrFail($id);

    //         // Jika tim_project dikirim dalam bentuk JSON string
    //         $request->merge([
    //             'tim_project' => is_string($request->tim_project) ? json_decode($request->tim_project, true) : $request->tim_project
    //         ]);

    //         $request->validate([
    //             'nama_proyek' => 'sometimes|string',
    //             'client' => 'sometimes|string',
    //             'total_nilai_kontrak' => 'sometimes|numeric',
    //             'rencana_biaya' => 'sometimes|numeric',
    //             'realisasi_budget' => 'sometimes|numeric',
    //             'start_date' => 'sometimes|date',
    //             'end_date' => 'sometimes|date|after_or_equal:start_date',
    //             'kategori' => 'required|in:0,1',
    //             'lampiran_proyek' => 'nullable|file|mimes:pdf,doc,docx,xlsx,jpg,png',
    //             'biaya_akomodasi' => 'sometimes|numeric',
    //             'pihak_pemberi_biaya' => 'sometimes|string',
    //             'tim_project' => 'nullable|array',
    //             'tim_project.*.id_karyawan' => 'required|exists:karyawans,id',
    //         ]);

    //         $data = $request->except(['lampiran_proyek', 'tim_project']);

    //         // Proses upload lampiran jika ada
    //         if ($request->hasFile('lampiran_proyek')) {
    //             $data['lampiran_proyek'] = $request->file('lampiran_proyek')->store('lampiran_proyek', 'public');
    //         }

    //         // Update data proyek
    //         $project->update($data);

    //         // Update tim proyek (jika ada)
    //         if ($request->has('tim_project')) {
    //             // Hapus tim sebelumnya
    //             TimProject::where('id_project', $project->id)->delete();

    //             // Tambah tim baru
    //             foreach ($request->tim_project as $tim) {
    //                 TimProject::create([
    //                     'id_project' => $project->id,
    //                     'id_karyawan' => $tim['id_karyawan'],
    //                 ]);
    //             }
    //         }

    //         DB::commit();
    //         return response()->json(['id' => '1', 'data' => $project]);
    //     } catch (ValidationException $e) {
    //         DB::rollBack();
    //         return response()->json(['id' => '0', 'data' => $e->errors()], 422);
    //     } catch (\Throwable $th) {
    //         DB::rollBack();
    //         return response()->json(['id' => '0', 'data' => 'Gagal mengupdate proyek. Error: ' . $th->getMessage()], 500);
    //     }
    // }

    // public function updateProject(Request $request, $id)
    // {
    //     DB::beginTransaction();

    //     try {
    //         $project = Project::findOrFail($id);

    //         // Decode JSON jika dikirim dalam bentuk string
    //         $request->merge([
    //             'tim_project' => is_string($request->tim_project) ? json_decode($request->tim_project, true) : $request->tim_project,
    //             'aktifitas' => is_string($request->aktifitas) ? json_decode($request->aktifitas, true) : $request->aktifitas
    //         ]);

    //         $request->validate([
    //             'nama_proyek' => 'sometimes|string',
    //             'client' => 'sometimes|string',
    //             'total_nilai_kontrak' => 'sometimes|numeric',
    //             'rencana_biaya' => 'sometimes|numeric',
    //             'realisasi_budget' => 'sometimes|numeric',
    //             'start_date' => 'sometimes|date',
    //             'end_date' => 'sometimes|date|after_or_equal:start_date',
    //             'kategori' => 'required|in:0,1',
    //             'lampiran_proyek' => 'nullable|file|mimes:pdf,doc,docx,xlsx,jpg,png',
    //             'biaya_akomodasi' => 'sometimes|numeric',
    //             'pihak_pemberi_biaya' => 'sometimes|string',
    //             'tim_project' => 'nullable|array',
    //             'tim_project.*.id_karyawan' => 'required|exists:karyawans,id',
    //             'aktifitas' => 'nullable|array',
    //             'aktifitas.*.aktivitas' => 'required|string',
    //             'aktifitas.*.pic' => 'required|string',
    //             'aktifitas.*.biaya' => 'required|numeric',
    //             'aktifitas.*.start_date' => 'required|date',
    //             'aktifitas.*.end_date' => 'required|date|after_or_equal:aktifitas.*.start_date',
    //         ]);

    //         $data = $request->except(['lampiran_proyek', 'tim_project', 'aktifitas']);

    //         // Upload lampiran jika ada file baru
    //         if ($request->hasFile('lampiran_proyek')) {
    //             $data['lampiran_proyek'] = $request->file('lampiran_proyek')->store('lampiran_proyek', 'public');
    //         }

    //         // Update project
    //         $project->update($data);

    //         // Update tim proyek
    //         if ($request->has('tim_project')) {
    //             TimProject::where('id_project', $project->id)->delete();
    //             foreach ($request->tim_project as $tim) {
    //                 TimProject::create([
    //                     'id_project' => $project->id,
    //                     'id_karyawan' => $tim['id_karyawan'],
    //                 ]);
    //             }
    //         }

    //         // Tambah ulang semua aktivitas baru (jika ada)
    //         if ($request->has('aktifitas')) {
    //             // Hapus semua aktifitas lama
    //             Aktifitas::where('id_project', $project->id)->delete();

    //             foreach ($request->aktifitas as $akt) {
    //                 Aktifitas::create([
    //                     'aktivitas' => $akt['aktivitas'],
    //                     'pic' => $akt['pic'],
    //                     'biaya' => $akt['biaya'],
    //                     'start_date' => $akt['start_date'],
    //                     'end_date' => $akt['end_date'],
    //                     'id_project' => $project->id,
    //                 ]);
    //             }
    //         }

    //         DB::commit();
    //         return response()->json(['id' => '1', 'data' => $project]);
    //     } catch (ValidationException $e) {
    //         DB::rollBack();
    //         return response()->json(['id' => '0', 'data' => $e->errors()], 422);
    //     } catch (\Throwable $th) {
    //         DB::rollBack();
    //         return response()->json(['id' => '0', 'data' => 'Gagal mengupdate proyek. Error: ' . $th->getMessage()], 500);
    //     }
    // }



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
            $project = Project::with('manager', 'timProject', 'aktifitasProject')->findOrFail($id);

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
