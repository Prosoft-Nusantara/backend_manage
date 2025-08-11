<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\TimProject;
use App\Models\Aktifitas;
use App\Models\BiayaAktivitas;
use App\Models\Karyawan;
use App\Models\Operasional;
use App\Models\Coordinator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use App\Http\Controllers\Validator;

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
    public function getProjectsByManager()
    {
        try {
            // $excludedStatuses = ['-', '0-', '-0'];

            $projects = Project::where('id_manager', auth()->user()->id)
                // ->whereNotIn('status', $excludedStatuses)
                ->with('manager')
                ->get();

            return response()->json(['id' => '1', 'data' => $projects]);
        } catch (\Throwable $th) {
            return response()->json(['id' => '0', 'data' => 'Gagal mengambil proyek']);
        }
    }


    // CREATE
    // public function createProject(Request $request)
    // {
    //     // Decode JSON string jika ada
    //     $request->merge([
    //         'tim_project' => is_string($request->tim_project) ? json_decode($request->tim_project, true) : $request->tim_project,
    //         'aktifitas'   => is_string($request->aktifitas)   ? json_decode($request->aktifitas, true)   : $request->aktifitas,
    //     ]);

    //     try {
    //         $validated = $this->validateCreate($request);

    //         DB::beginTransaction();

    //         // Upload lampiran
    //         $lampiranPath = null;
    //         if ($request->hasFile('lampiran_proyek')) {
    //             $lampiranPath = $request->file('lampiran_proyek')->store('lampiran_proyek', 'public');
    //         }

    //         // Data proyek
    //         $data = [
    //             'nama_proyek'         => $validated['nama_proyek'],
    //             'client'              => $validated['client'],
    //             'total_nilai_kontrak' => $validated['total_nilai_kontrak'],
    //             'rencana_biaya'       => $validated['rencana_biaya'],
    //             'realisasi_budget'    => 0,
    //             'start_date'          => $validated['start_date'],
    //             'end_date'            => $validated['end_date'],
    //             'id_manager'          => $validated['id_manager'],
    //             'kategori'            => $validated['kategori'],
    //             'lampiran_proyek'     => $lampiranPath,
    //         ];

    //         if ($validated['kategori'] == '0') {
    //             // $data['biaya_akomodasi']     = $validated['biaya_akomodasi'];
    //             // $data['pihak_pemberi_biaya'] = $validated['pihak_pemberi_biaya'];
    //             $data['biaya_akomodasi']     = $request['biaya_akomodasi'];
    //             $data['pihak_pemberi_biaya'] = $request['pihak_pemberi_biaya'];
    //         }

    //         $project = Project::create($data);

    //         // Tim
    //         if (!empty($validated['tim_project'])) {
    //             $rows = collect($validated['tim_project'])
    //                 ->map(fn($t) => ['id_project' => $project->id, 'id_karyawan' => $t['id_karyawan']])
    //                 ->toArray();
    //             TimProject::insert($rows);
    //         }

    //         // Aktifitas
    //         if (!empty($validated['aktifitas'])) {
    //             $rows = collect($validated['aktifitas'])
    //                 ->map(fn($a) => array_merge($a, ['id_project' => $project->id]))
    //                 ->toArray();
    //             Aktifitas::insert($rows);
    //         }

    //         DB::commit();
    //         return response()->json(['id' => '1', 'message' => 'Proyek berhasil dibuat.', 'data' => $project], 201);
    //     } catch (ValidationException $e) {
    //         DB::rollBack();
    //         return response()->json(['id' => '0', 'data' => $e->errors()], 422);
    //     } catch (\Throwable $th) {
    //         DB::rollBack();
    //         return response()->json(['id' => '0', 'data' => 'Gagal membuat proyek. Error: ' . $th->getMessage()], 500);
    //     }
    // }
    public function createProject(Request $request)
    {
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
                'nomor_pemesanan'     => $validated['nomor_pemesanan'],
                'nama_proyek'         => $validated['nama_proyek'],
                'client'              => $validated['client'],
                'total_nilai_kontrak' => $validated['total_nilai_kontrak'],
                'rencana_biaya'       => $validated['rencana_biaya'],
                'realisasi_budget'    => 0,
                'start_date'          => $validated['start_date'],
                'end_date'            => $validated['end_date'],
                'id_manager'          => auth()->user()->id,
                'kategori'            => $validated['kategori'],
                'lampiran_proyek'     => $lampiranPath,
            ];

            if ($validated['kategori'] == '0') {
                $data['biaya_akomodasi']     = $request->biaya_akomodasi;
                $data['pihak_pemberi_biaya'] = $request->pihak_pemberi_biaya;
            }

            $project = Project::create($data);

            // Tim
            if (!empty($validated['tim_project'])) {
                $rows = collect($validated['tim_project'])
                    ->map(fn($t) => ['id_project' => $project->id, 'id_karyawan' => $t['id_karyawan']])
                    ->toArray();
                TimProject::insert($rows);
            }

            // Aktifitas + Biaya
            if (!empty($validated['aktifitas'])) {
                foreach ($validated['aktifitas'] as $act) {
                    $aktivitas = Aktifitas::create([
                        'aktivitas' => $act['aktivitas'],
                        'pic'       => $act['pic'],
                        'id_project' => $project->id,
                    ]);
                    foreach ($act['biayas'] as $by) {
                        BiayaAktivitas::create([
                            'keterangan'  => $by['keterangan'],
                            'biaya'       => $by['biaya'],
                            'start_date'  => $by['start_date'],
                            'end_date'    => $by['end_date'],
                            'id_aktivitas' => $aktivitas->id,
                        ]);
                    }
                }
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
    // public function updateProject(Request $request, $id)
    // {
    //     $request->merge([
    //         'tim_project' => is_string($request->tim_project) ? json_decode($request->tim_project, true) : $request->tim_project,
    //         'aktifitas'   => is_string($request->aktifitas)   ? json_decode($request->aktifitas, true)   : $request->aktifitas,
    //     ]);

    //     try {
    //         $validated = $this->validateUpdate($request);

    //         DB::beginTransaction();

    //         $project = Project::findOrFail($id);

    //         // Data umum
    //         $data = $request->except(['lampiran_proyek', 'tim_project', 'aktifitas']);

    //         // Upload & hapus lampiran lama
    //         if ($request->hasFile('lampiran_proyek')) {
    //             if ($project->lampiran_proyek) {
    //                 Storage::disk('public')->delete($project->lampiran_proyek);
    //             }
    //             $data['lampiran_proyek'] = $request->file('lampiran_proyek')->store('lampiran_proyek', 'public');
    //         }

    //         $project->update($data);

    //         // Tim
    //         if ($request->has('tim_project')) {
    //             TimProject::where('id_project', $project->id)->delete();
    //             if (!empty($validated['tim_project'])) {
    //                 $rows = collect($validated['tim_project'])
    //                     ->map(fn($t) => ['id_project' => $project->id, 'id_karyawan' => $t['id_karyawan']])
    //                     ->toArray();
    //                 TimProject::insert($rows);
    //             }
    //         }

    //         // Aktifitas
    //         if ($request->has('aktifitas')) {
    //             Aktifitas::where('id_project', $project->id)->delete();
    //             if (!empty($validated['aktifitas'])) {
    //                 $rows = collect($validated['aktifitas'])
    //                     ->map(fn($a) => array_merge($a, ['id_project' => $project->id]))
    //                     ->toArray();
    //                 Aktifitas::insert($rows);
    //             }
    //         }

    //         DB::commit();
    //         return response()->json(['id' => '1', 'message' => 'Proyek berhasil diperbarui.', 'data' => $project]);
    //     } catch (ValidationException $e) {
    //         DB::rollBack();
    //         return response()->json(['id' => '0', 'data' => $e->errors()], 422);
    //     } catch (\Throwable $th) {
    //         DB::rollBack();
    //         return response()->json(['id' => '0', 'data' => 'Gagal mengupdate proyek. Error: ' . $th->getMessage()], 500);
    //     }
    // }

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

            $data = $request->except(['lampiran_proyek', 'tim_project', 'aktifitas']);

            // Lampiran baru
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

            // Aktifitas + Biaya
            if ($request->has('aktifitas')) {
                Aktifitas::where('id_project', $project->id)->delete();
                foreach ($validated['aktifitas'] as $act) {
                    $aktivitas = Aktifitas::create([
                        'aktivitas' => $act['aktivitas'],
                        'pic'       => $act['pic'],
                        'id_project' => $project->id,
                    ]);
                    foreach ($act['biayas'] as $by) {
                        BiayaAktivitas::create([
                            'keterangan'  => $by['keterangan'],
                            'biaya'       => $by['biaya'],
                            'start_date'  => $by['start_date'],
                            'end_date'    => $by['end_date'],
                            'id_aktivitas' => $aktivitas->id,
                        ]);
                    }
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
    // private function validateCreate(Request $request)
    // {
    //     return $request->validate([
    //         'nama_proyek'         => 'required|string|max:255',
    //         'client'              => 'required|string|max:255',
    //         'total_nilai_kontrak' => 'required|numeric|min:0',
    //         'rencana_biaya'       => 'required|numeric|min:0',
    //         'start_date'          => 'required|date',
    //         'end_date'            => 'required|date|after_or_equal:start_date',
    //         'kategori'            => 'required|in:0,1',
    //         'id_manager'          => 'required|exists:users,id',
    //         'lampiran_proyek'     => 'nullable|file|mimes:pdf,doc,docx,xlsx,jpg,png|max:5120',

    //         // 'biaya_akomodasi'     => 'string|min:0',
    //         // 'pihak_pemberi_biaya' => 'string|max:255',

    //         'tim_project'               => 'nullable|array',
    //         'tim_project.*'             => 'array:id_karyawan',
    //         'tim_project.*.id_karyawan' => 'required|exists:karyawans,id',

    //         'aktifitas'                        => 'nullable|array',
    //         'aktifitas.*'                      => 'array:aktivitas,pic,biaya,start_date,end_date',
    //         'aktifitas.*.aktivitas'            => 'required|string|max:255',
    //         'aktifitas.*.pic'                  => 'required|string|max:255',
    //         'aktifitas.*.biaya'                => 'required|numeric|min:0',
    //         'aktifitas.*.start_date'           => 'required|date',
    //         'aktifitas.*.end_date'             => 'required|date|after_or_equal:aktifitas.*.start_date',
    //     ]);
    // }

    private function validateCreate(Request $request)
    {
        return $request->validate([
            'nomor_pemesanan'         => 'required|string|max:255',
            'nama_proyek'         => 'required|string|max:255',
            'client'              => 'required|string|max:255',
            'total_nilai_kontrak' => 'required|numeric|min:0',
            'rencana_biaya'       => 'required|numeric|min:0',
            'start_date'          => 'required|date',
            'end_date'            => 'required|date|after_or_equal:start_date',
            'kategori'            => 'required|in:0,1',
            // 'id_manager'          => 'required|exists:users,id',
            'lampiran_proyek'     => 'nullable|file|mimes:pdf,doc,docx,xlsx,jpg,png|max:5120',

            // 'biaya_akomodasi'     => 'required_if:kategori,0|numeric|min:0',
            // 'pihak_pemberi_biaya' => 'required_if:kategori,0|string|max:255',

            'tim_project'               => 'nullable|array',
            'tim_project.*.id_karyawan' => 'required|exists:karyawans,id',

            'aktifitas' => 'nullable|array',
            'aktifitas.*.aktivitas' => 'required|string|max:255',
            'aktifitas.*.pic'       => 'required|string|max:255',
            'aktifitas.*.biayas'    => 'required|array',
            'aktifitas.*.biayas.*.keterangan' => 'required|string',
            'aktifitas.*.biayas.*.biaya'      => 'required|numeric|min:0',
            'aktifitas.*.biayas.*.start_date' => 'required|date',
            'aktifitas.*.biayas.*.end_date'   => 'required|date|after_or_equal:aktifitas.*.biayas.*.start_date',
        ]);
    }

    /* ---------- VALIDASI UPDATE ---------- */
    // private function validateUpdate(Request $request)
    // {
    //     return $request->validate([
    //         'nama_proyek'         => 'sometimes|string|max:255',
    //         'client'              => 'sometimes|string|max:255',
    //         'total_nilai_kontrak' => 'sometimes|numeric|min:0',
    //         'rencana_biaya'       => 'sometimes|numeric|min:0',
    //         'realisasi_budget'    => 'sometimes|numeric|min:0',
    //         'start_date'          => 'sometimes|date',
    //         'end_date'            => 'sometimes|date|after_or_equal:start_date',
    //         'kategori'            => 'required|in:0,1',
    //         'lampiran_proyek'     => 'nullable|file|mimes:pdf,doc,docx,xlsx,jpg,png|max:5120',

    //         // 'biaya_akomodasi'     => 'sometimes|numeric|min:0',
    //         // 'pihak_pemberi_biaya' => 'sometimes|string|max:255',

    //         'tim_project'               => 'nullable|array',
    //         'tim_project.*'             => 'array:id_karyawan',
    //         'tim_project.*.id_karyawan' => 'required|exists:karyawans,id',

    //         'aktifitas'                        => 'nullable|array',
    //         'aktifitas.*'                      => 'array:aktivitas,pic,biaya,start_date,end_date',
    //         'aktifitas.*.aktivitas'            => 'required|string|max:255',
    //         'aktifitas.*.pic'                  => 'required|string|max:255',
    //         'aktifitas.*.biaya'                => 'required|numeric|min:0',
    //         'aktifitas.*.start_date'           => 'required|date',
    //         'aktifitas.*.end_date'             => 'required|date|after_or_equal:aktifitas.*.start_date',
    //     ]);
    // }

    /* ---------- VALIDASI UPDATE ---------- */
    private function validateUpdate(Request $request)
    {
        return $request->validate([
            'nomor_pemesanan'         => 'required|string|max:255',
            'nama_proyek'         => 'sometimes|string|max:255',
            'client'              => 'sometimes|string|max:255',
            'total_nilai_kontrak' => 'sometimes|numeric|min:0',
            'rencana_biaya'       => 'sometimes|numeric|min:0',
            'realisasi_budget'    => 'sometimes|numeric|min:0',
            'start_date'          => 'sometimes|date',
            'end_date'            => 'sometimes|date|after_or_equal:start_date',
            'kategori'            => 'required|in:0,1',
            'lampiran_proyek'     => 'nullable|file|mimes:pdf,doc,docx,xlsx,jpg,png|max:5120',

            // 'biaya_akomodasi'     => 'sometimes|required_if:kategori,0|numeric|min:0',
            // 'pihak_pemberi_biaya' => 'sometimes|required_if:kategori,0|string|max:255',

            'tim_project'               => 'nullable|array',
            'tim_project.*.id_karyawan' => 'required|exists:karyawans,id',

            'aktifitas' => 'nullable|array',
            'aktifitas.*.aktivitas' => 'required|string|max:255',
            'aktifitas.*.pic'       => 'required|string|max:255',
            'aktifitas.*.biayas'    => 'required|array',
            'aktifitas.*.biayas.*.keterangan' => 'required|string',
            'aktifitas.*.biayas.*.biaya'      => 'required|numeric|min:0',
            'aktifitas.*.biayas.*.start_date' => 'required|date',
            'aktifitas.*.biayas.*.end_date'   => 'required|date|after_or_equal:aktifitas.*.biayas.*.start_date',
        ]);
    }

    public function updateAktifitas(Request $request, $id)
    {
        try {
            $validateData = $request->validate([
                // 'aktivitas' => 'required|string|max:255',
                'pic' => 'required|string|max:255',
                'biaya' => 'required|numeric|min:0',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
            ]);
            $aktifitas = Aktifitas::findOrFail($id);
            $aktifitas->update($validateData);
            return response()->json(['id' => '1', 'data' => 'Aktifitas berhasil diperbarui.']);
        } catch (\Throwable $th) {
            return response()->json(['id' => '0', 'data' => 'Gagal memperbarui aktifitas. Error: ' . $th->getMessage()], 500);
        }
    }

    // public function selesaikanAktifitas(Request $request, $id)
    // {
    //     try {
    //         $validateData = $request->validate([
    //             'file' => 'required|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10048'
    //         ]);
    //         $aktifitas = Aktifitas::findOrFail($id);

    //         $dataProject = Project::findOrFail($aktifitas->id_project);

    //         $dataProject->update([
    //             'realisasi_budget' => $dataProject->realisasi_budget + $aktifitas->biaya
    //         ]);

    //         $hasilPath = $request->file('file')->store('file_aktifitas', 'public');
    //         if ($aktifitas->status == '1') {
    //             return response()->json(['id' => '0', 'data' => 'Aktifitas sudah selesai.'], 500);
    //         }
    //         $aktifitas->update([
    //             'status' => '1',
    //             'file' => $hasilPath
    //         ]);
    //         return response()->json(['id' => '1', 'data' => 'Aktifitas selesai.']);
    //     } catch (\Throwable $th) {
    //         return response()->json(['id' => '0', 'data' => 'Gagal selesaikan aktifitas.'], 500);
    //     }
    // }

    public function actionProject(Request $request)
    {
        try {
            $validated = $request->validate([
                'id_project' => 'required|exists:projects,id',
                'status'     => 'required|in:0,3', // 0 approve, 3 reject
                'keterangan_rejek' => 'string',
            ]);

            $user = auth()->user();
            $project = Project::findOrFail($validated['id_project']);
            $currentStatus = $project->status;

            // Penanganan reject langsung oleh siapa pun
            if ($validated['status'] == '3') {
                $project->update(['status' => '3', 'keterangan_rejek' => $validated['keterangan_rejek']]);

                return response()->json(['id' => '1', 'data' => 'Project berhasil direject.']);
            }

            // Penanganan approve berdasarkan level user
            if ($user->level == '1') { // Divisi
                if ($currentStatus == '-') {
                    $project->update(['status' => '0-']);
                } elseif ($currentStatus == '-0') {
                    $project->update(['status' => '0-0']);
                } elseif (in_array($currentStatus, ['0-', '0-0'])) {
                    // Sudah divalidasi, tidak perlu update lagi
                    return response()->json(['id' => '1', 'data' => 'Project sudah divalidasi oleh divisi.']);
                } else {
                    return response()->json(['id' => '0', 'data' => 'Status project tidak valid untuk divisi.'], 400);
                }

                return response()->json(['id' => '1', 'data' => 'Project berhasil divalidasi oleh divisi.']);
            }

            if ($user->level == '4') { // Kaunit
                if ($currentStatus == '-') {
                    $project->update(['status' => '-0']);
                } elseif ($currentStatus == '0-') {
                    $project->update(['status' => '0-0']);
                } elseif (in_array($currentStatus, ['-0', '0-0'])) {
                    return response()->json(['id' => '1', 'data' => 'Project sudah divalidasi oleh kaunit.']);
                } else {
                    return response()->json(['id' => '0', 'data' => 'Status project tidak valid untuk kaunit.'], 400);
                }

                return response()->json(['id' => '1', 'data' => 'Project berhasil divalidasi oleh kaunit.']);
            }

            return response()->json(['id' => '0', 'data' => 'Anda tidak memiliki akses.'], 403);
        } catch (\Throwable $th) {
            return response()->json([
                'id' => '0',
                'data' => 'Gagal memproses project. Error: ' . $th->getMessage()
            ], 500);
        }
    }


    public function selesaikanAktifitas(Request $request, $id)
    {
        try {
            /* ---------- 1. Validasi & pesan rinci ---------- */
            $validator = Validator::make($request->all(), [
                'aktivitas' => 'required|string|max:255',
                'pic'       => 'required|string|max:255',
                'biayas'    => 'required|array',
                'biayas.*.keterangan' => 'required|string',
                'biayas.*.realisasi_biaya'      => 'required|numeric|min:0',
                'biayas.*.realisasi_start_date' => 'required|date',
                'biayas.*.realisasi_end_date'   => 'required|date|after_or_equal:biayas.*.start_date',
                'file'      => 'required|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10048',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'id' => '0',
                    'data' => $validator->errors()->first()
                ], 422);
            }

            /* ---------- 2. Cek aktivitas ---------- */
            $aktivitas = Aktifitas::find($id);
            if (!$aktivitas) {
                return response()->json(['id' => '0', 'data' => 'Aktivitas tidak ditemukan'], 404);
            }

            /* ---------- 3. Cek status ---------- */
            if ($aktivitas->status === '1') {
                return response()->json(['id' => '0', 'data' => 'Aktivitas sudah selesai'], 422);
            }

            /* ---------- 4. Update aktivitas ---------- */
            $aktivitas->update([
                'aktivitas' => $request->aktivitas,
                'pic'       => $request->pic,
                'status'    => '1',
            ]);

            /* ---------- 5. Hapus biaya lama, insert baru ---------- */
            // $aktivitas->biayaAktivitas()->delete();
            $total = 0;
            foreach ($request->biayas as $by) {

                BiayaAktivitas::where('id_aktivitas', $aktivitas->id)->update([
                    'keterangan'  => $by['keterangan'],
                    'realisasi_biaya'       => $by['realisasi_biaya'],
                    'realisasi_start_date'  => $by['realisasi_start_date'],
                    'realisasi_end_date'    => $by['realisasi_end_date'],
                    'id_aktivitas' => $aktivitas->id,
                ]);
                $total += $by['realisasi_biaya'];
            }

            /* ---------- 6. Upload file ---------- */
            $hasilPath = $request->file('file')->store('file_aktivitas', 'public');
            $aktivitas->update(['file' => $hasilPath]);

            /* ---------- 7. Update realisasi budget ---------- */
            Project::where('id', $aktivitas->id_project)->increment('realisasi_budget', $total);

            return response()->json(['id' => '1', 'data' => 'Aktivitas & biaya diperbarui, status selesai.']);
        } catch (\Throwable $th) {
            return response()->json([
                'id' => '0',
                'data' => 'Gagal: ' . $th->getMessage()
            ], 500);
        }
    }

    // 3. Membuat proyek (lampiran berupa file)


    // 4. Menyelesaikan proyek (upload hasil)
    public function completeProject(Request $request, $id)
    {
        try {
            $project = Project::findOrFail($id);

            $request->validate([
                'bast_kontrak' => 'required|file|mimes:pdf,doc,docx,xlsx,jpg,png',
                'invoice' => 'required|file|mimes:pdf,doc,docx,xlsx,jpg,png',
                // 'realisasi_budget' => 'required|numeric|min:0',
            ]);

            $hasilPathBastKontrak = $request->file('bast_kontrak')->store('bast_kontrak', 'public');
            $hasilPathInvoice = $request->file('invoice')->store('invoice', 'public');

            $project->update([
                'status' => '1',
                'bast_kontrak' => $hasilPathBastKontrak,
                'invoice' => $hasilPathInvoice,
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
                'surat_pembayaran' => 'required|file|mimes:pdf,doc,docx,xlsx,jpg,png',
                'tanggal_pembayaran' => 'required|date',
            ]);

            $hasilPath = $request->file('surat_pembayaran')->store('surat_pembayaran', 'public');

            $project->update([
                'status' => '2',
                'surat_pembayaran' => $hasilPath,
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
    // public function detailProject($id)
    // {
    //     try {
    //         $project = Project::with('manager', 'timProject', 'aktifitasProject')->findOrFail($id);

    //         // Tambahkan nama tim ke setiap anggota tim
    //         foreach ($project->timProject as $tim) {
    //             if ($tim->jenis_tim == '0') {
    //                 $coor = Coordinator::find($tim->id_tim);
    //                 $tim->nama_tim = $coor ? $coor->nama : 'Tidak ditemukan';
    //             } elseif ($tim->jenis_tim == '1') {
    //                 $ops = Operasional::find($tim->id_tim);
    //                 $tim->nama_tim = $ops ? $ops->nama : 'Tidak ditemukan';
    //             } else {
    //                 $tim->nama_tim = 'Tidak diketahui';
    //             }
    //         }

    //         return response()->json(['id' => '1', 'data' => $project]);
    //     } catch (\Throwable $th) {
    //         return response()->json(['id' => '0', 'data' => 'Gagal mengambil detail proyek']);
    //     }
    // }

    // public function detailProject($id)
    // {
    //     try {
    //         $project = Project::with('manager', 'timProject', 'aktifitasProject')->findOrFail($id);

    //         // --- Tambah nama tim ---
    //         foreach ($project->timProject as $tim) {
    //             if ($tim->jenis_tim == '0') {
    //                 $coor = Coordinator::find($tim->id_tim);
    //                 $tim->nama_tim = $coor ? $coor->nama : 'Tidak ditemukan';
    //             } elseif ($tim->jenis_tim == '1') {
    //                 $ops = Operasional::find($tim->id_tim);
    //                 $tim->nama_tim = $ops ? $ops->nama : 'Tidak ditemukan';
    //             } else {
    //                 $tim->nama_tim = 'Tidak diketahui';
    //             }
    //         }

    //         // --- Hitung keterangan tambahan ---
    //         $aktivitasLebihDeadline = $project->aktifitasProject
    //             ->some(fn($act) => $act->end_date > $project->end_date);

    //         $totalBiayaAktivitas = $project->aktifitasProject->sum('biaya');
    //         $biayaLebihRencana = $totalBiayaAktivitas > $project->rencana_biaya;

    //         // --- Tambahkan ke response ---
    //         $project->aktivitas_lebih_deadline   = $aktivitasLebihDeadline;
    //         $project->biaya_aktivitas_lebih_rencana = $biayaLebihRencana;

    //         return response()->json(['id' => '1', 'data' => $project]);
    //     } catch (\Throwable $th) {
    //         return response()->json(['id' => '0', 'data' => 'Gagal mengambil detail proyek'], 500);
    //     }
    // }

    public function detailProject($id)
    {
        try {
            $project = Project::with([
                'manager',
                'timProject',
                'aktifitasProject.biayaAktivitas'
            ])->findOrFail($id);

            // Tambah nama tim
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

            // Hitung total biaya dari semua detail biaya aktivitas
            $totalBiayaAktivitas = $project->aktifitasProject
                ->flatMap(fn($act) => $act->biayaAktivitas)
                ->sum('biaya');

            // Cek deadline & rencana
            $aktivitasLebihDeadline = $project->aktifitasProject
                ->flatMap(fn($act) => $act->biayaAktivitas)
                ->some(fn($by) => $by->end_date > $project->end_date);

            $biayaLebihRencana = $totalBiayaAktivitas > $project->rencana_biaya;

            // Sertakan ke response
            $project->aktivitas_lebih_deadline   = $aktivitasLebihDeadline;
            $project->biaya_aktivitas_lebih_rencana = $biayaLebihRencana;

            return response()->json(['id' => '1', 'data' => $project]);
        } catch (\Throwable $th) {
            return response()->json(['id' => '0', 'data' => 'Gagal mengambil detail proyek' . $th], 500);
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
