<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Divisi;
use App\Models\KaUnit;
use App\Models\Manager;
use App\Models\Karyawan;
use App\Models\Coordinator;
use App\Models\Operasional;
use Illuminate\Validation\ValidationException;

class KaryawanController extends Controller
{
    // ========================= DIVISI =========================

    public function getAllDivisi()
    {
        try {
            $divisis = Divisi::with('kepalaDivisi')->get();
            return response()->json(['id' => '1', 'data' => $divisis]);
        } catch (\Throwable $th) {
            return response()->json(['id' => '0', 'data' => 'Gagal mengambil data divisi']);
        }
    }

    public function createDivisi(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string',
                'nama_divisi' => 'required|string|max:255',
                'deskripsi' => 'required|string',
            ]);

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => bcrypt($request->password),
                'level' => '1'
            ]);

            $divisi = Divisi::create([
                'nama_divisi' => $request->nama_divisi,
                'deskripsi' => $request->deskripsi,
                'id_kepala_divisi' => $user->id
            ]);

            return response()->json([
                'id' => '1',
                'data' => $divisi
            ], 201);
        } catch (ValidationException $e) {
            // Jika validasi gagal, tampilkan semua error
            return response()->json([
                'id' => '0',
                'data' => $e->errors() // berisi array: ['email' => ['Email sudah dipakai.'], ...]
            ], 422);
        } catch (\Throwable $th) {
            // Untuk error selain validasi (misal DB error, dll)
            return response()->json([
                'id' => '0',
                'data' => 'Gagal membuat divisi. Error: ' . $th->getMessage()
            ], 500);
        }
    }


    public function updateDivisi(Request $request, $id)
    {
        try {
            $divisi = Divisi::findOrFail($id);

            $request->validate([
                'nama_divisi' => 'sometimes|string|max:255',
                'deskripsi' => 'sometimes|string',
                'id_kepala_divisi' => 'sometimes|exists:users,id',
            ]);

            $divisi->update($request->all());

            return response()->json(['id' => '1', 'data' => $divisi]);
        } catch (ValidationException $e) {
            // Jika validasi gagal, tampilkan semua error
            return response()->json([
                'id' => '0',
                'data' => $e->errors() // berisi array: ['email' => ['Email sudah dipakai.'], ...]
            ], 422);
        } catch (\Throwable $th) {
            return response()->json(['id' => '0', 'data' => 'Gagal mengupdate divisi']);
        }
    }

    public function deleteDivisi($id)
    {
        try {
            $divisi = Divisi::findOrFail($id);
            User::where('id', $divisi->id_kepala_divisi)->delete();
            $divisi->delete();

            return response()->json(['id' => '1', 'data' => 'Divisi berhasil dihapus']);
        } catch (\Throwable $th) {
            return response()->json(['id' => '0', 'data' => 'Gagal menghapus divisi']);
        }
    }

    // ========================= UNIT =========================

     public function getAllUnit()
    {
        try {
            $units = KaUnit::with('kepalaDivisi')->get();
            return response()->json(['id' => '1', 'data' => $units]);
        } catch (\Throwable $th) {
            return response()->json(['id' => '0', 'data' => 'Gagal mengambil data divisi']);
        }
    }

    public function createUnit(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string',
                'nama_unit' => 'required|string|max:255',
                'deskripsi' => 'required|string',
            ]);

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => bcrypt($request->password),
                'level' => '4'
            ]);

            $unit = KaUnit::create([
                'nama_unit' => $request->nama_unit,
                'deskripsi' => $request->deskripsi,
                'id_kepala_unit' => $user->id
            ]);

            return response()->json([
                'id' => '1',
                'data' => $unit
            ], 201);
        } catch (ValidationException $e) {
            // Jika validasi gagal, tampilkan semua error
            return response()->json([
                'id' => '0',
                'data' => $e->errors() // berisi array: ['email' => ['Email sudah dipakai.'], ...]
            ], 422);
        } catch (\Throwable $th) {
            // Untuk error selain validasi (misal DB error, dll)
            return response()->json([
                'id' => '0',
                'data' => 'Gagal membuat unit. Error: ' . $th->getMessage()
            ], 500);
        }
    }


    public function updateUnit(Request $request, $id)
    {
        try {
            $unit = KaUnit::findOrFail($id);

            $request->validate([
                'nama_unit' => 'sometimes|string|max:255',
                'deskripsi' => 'sometimes|string',
                'id_kepala_unit' => 'sometimes|exists:users,id',
            ]);

            $unit->update($request->all());

            return response()->json(['id' => '1', 'data' => $unit]);
        } catch (ValidationException $e) {
            // Jika validasi gagal, tampilkan semua error
            return response()->json([
                'id' => '0',
                'data' => $e->errors() // berisi array: ['email' => ['Email sudah dipakai.'], ...]
            ], 422);
        } catch (\Throwable $th) {
            return response()->json(['id' => '0', 'data' => 'Gagal mengupdate unit']);
        }
    }

    public function deleteUnit($id)
    {
        try {
            $unit = KaUnit::findOrFail($id);
            User::where('id', $unit->id_kepala_unit)->delete();
            $unit->delete();

            return response()->json(['id' => '1', 'data' => 'unit berhasil dihapus']);
        } catch (\Throwable $th) {
            return response()->json(['id' => '0', 'data' => 'Gagal menghapus unit']);
        }
    }

    // ========================= MANAGER =========================

    public function getAllManager()
    {
        try {
            $managers = Manager::with('user', 'divisi')->get();
            return response()->json(['id' => '1', 'data' => $managers]);
        } catch (\Throwable $th) {
            return response()->json(['id' => '0', 'data' => 'Gagal mengambil data manager']);
        }
    }

    public function createManager(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string',
                'nama_manager' => 'required|string',
                'deskripsi' => 'required|string',
                'id_divisi' => 'required|exists:divisis,id'
            ]);

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => bcrypt($request->password),
                'level' => '2'
            ]);

            $manager = Manager::create([
                'nama_manager' => $request->nama_manager,
                'deskripsi' => $request->deskripsi,
                'id_manager' => $user->id,
                'id_divisi' => $request->id_divisi
            ]);

            return response()->json(['id' => '1', 'data' => $manager], 201);
        } catch (ValidationException $e) {
            // Jika validasi gagal, tampilkan semua error
            return response()->json([
                'id' => '0',
                'data' => $e->errors() // berisi array: ['email' => ['Email sudah dipakai.'], ...]
            ], 422);
        } catch (\Throwable $th) {
            return response()->json(['id' => '0', 'data' => 'Gagal membuat manager']);
        }
    }

    public function updateManager(Request $request, $id)
    {
        try {
            $manager = Manager::findOrFail($id);

            $request->validate([
                'nama_manager' => 'sometimes|string',
                'deskripsi' => 'sometimes|string',
            ]);

            $manager->update($request->only('nama_manager', 'deskripsi'));

            return response()->json(['id' => '1', 'data' => $manager]);
        } catch (ValidationException $e) {
            // Jika validasi gagal, tampilkan semua error
            return response()->json([
                'id' => '0',
                'data' => $e->errors() // berisi array: ['email' => ['Email sudah dipakai.'], ...]
            ], 422);
        } catch (\Throwable $th) {
            return response()->json(['id' => '0', 'data' => 'Gagal mengupdate manager']);
        }
    }

    public function deleteManager($id)
    {
        try {
            $manager = Manager::findOrFail($id);
            $manager->delete();

            return response()->json(['id' => '1', 'data' => 'Manager berhasil dihapus']);
        } catch (\Throwable $th) {
            return response()->json(['id' => '0', 'data' => 'Gagal menghapus manager']);
        }
    }


    // ========================= KARYAWAN =========================

    public function getAllKaryawan()
    {
        try {
            $karyawans = Karyawan::with('manager')->get();
            return response()->json(['id' => '1', 'data' => $karyawans]);
        } catch (\Throwable $th) {
            return response()->json(['id' => '0', 'data' => 'Gagal mengambil data karyawan']);
        }
    }

    public function getMyKaryawan()
    {
        try {
            $karyawans = Karyawan::with('manager')->where('id_manager', auth()->user()->id)->get();
            return response()->json(['id' => '1', 'data' => $karyawans]);
        } catch (\Throwable $th) {
            return response()->json(['id' => '0', 'data' => 'Gagal mengambil data karyawan']);
        }
    }

    public function createKaryawan(Request $request)
    {
        try {
            $validated = $request->validate([
                'nama' => 'required|string|max:255',
                'alamat' => 'required|string',
                'email' => 'required|email',
                'no_hp' => 'required|max:20',
                'jabatan' => 'required|string|max:100',
                'id_manager' => 'required',
            ]);

            Karyawan::create([
                'nama' => $validated['nama'],
                'alamat' => $validated['alamat'],
                'email' => $validated['email'],
                'no_hp' => $validated['no_hp'],
                'jabatan' => $validated['jabatan'],
                'id_manager' => $validated['id_manager'],
            ]);

            return response()->json([
                'id' => '1',
                'data' => 'Karyawan berhasil dibuat.'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'id' => '0',
                'data' => $e->getMessage()
            ], 500);
        }
    }

    public function updateKaryawan(Request $request, $id)
    {
        try {
            $karyawan = Karyawan::findOrFail($id);

            $validated = $request->validate([
                'nama' => 'sometimes|required|string|max:255',
                'alamat' => 'sometimes|required|string',
                'email' => 'sometimes|required|email|unique:karyawans,email,' . $id,
                'no_hp' => 'sometimes|required|string|max:20',
                'jabatan' => 'sometimes|required|string|max:100',
                'id_manager' => 'sometimes|required|exists:managers,id',
            ]);

            $karyawan->update($validated);

            return response()->json([
                'id' => '1',
                'data' => 'Karyawan berhasil diperbarui.'
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'id' => '0',
                'data' => 'Karyawan tidak ditemukan.'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'id' => 'Terjadi kesalahan saat memperbarui karyawan.',
                'data' => $e->getMessage()
            ], 500);
        }
    }

    public function deleteKaryawan($id)
    {
        try {
            $karyawan = Karyawan::findOrFail($id);
            $karyawan->delete();

            return response()->json([
                'id' => '1',
                'data' => 'Karyawan berhasil dihapus.'
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'id' => '0',
                'data' => 'Karyawan tidak ditemukan.'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'id' => '1',
                'data' => $e->getMessage()
            ], 500);
        }
    }


    // ========================= COORDINATOR =========================

    public function getAllCoordinator()
    {
        try {
            $coordinators = Coordinator::with('manager')->get();
            return response()->json(['id' => '1', 'data' => $coordinators]);
        } catch (\Throwable $th) {
            return response()->json(['id' => '0', 'data' => 'Gagal mengambil data coordinator']);
        }
    }

    public function createCoordinator(Request $request)
    {
        try {
            $request->validate([
                'nama' => 'required|string',
                'alamat' => 'required|string',
                'email' => 'required|email',
                'no_hp' => 'required|string',
                'id_manager' => 'required|exists:managers,id'
            ]);

            $coordinator = Coordinator::create($request->all());

            return response()->json(['id' => '1', 'data' => $coordinator], 201);
        } catch (ValidationException $e) {
            // Jika validasi gagal, tampilkan semua error
            return response()->json([
                'id' => '0',
                'data' => $e->errors() // berisi array: ['email' => ['Email sudah dipakai.'], ...]
            ], 422);
        } catch (\Throwable $th) {
            return response()->json(['id' => '0', 'data' => 'Gagal membuat coordinator']);
        }
    }

    public function updateCoordinator(Request $request, $id)
    {
        try {
            $coordinator = Coordinator::findOrFail($id);

            $request->validate([
                'nama' => 'sometimes|string',
                'alamat' => 'sometimes|string',
                'email' => 'sometimes|email',
                'no_hp' => 'sometimes|string',
            ]);

            $coordinator->update($request->all());

            return response()->json(['id' => '1', 'data' => $coordinator]);
        } catch (ValidationException $e) {
            // Jika validasi gagal, tampilkan semua error
            return response()->json([
                'id' => '0',
                'data' => $e->errors() // berisi array: ['email' => ['Email sudah dipakai.'], ...]
            ], 422);
        } catch (\Throwable $th) {
            return response()->json(['id' => '0', 'data' => 'Gagal mengupdate coordinator']);
        }
    }

    public function deleteCoordinator($id)
    {
        try {
            $coordinator = Coordinator::findOrFail($id);
            $coordinator->delete();

            return response()->json(['id' => '1', 'data' => 'Coordinator berhasil dihapus']);
        } catch (\Throwable $th) {
            return response()->json(['id' => '0', 'data' => 'Gagal menghapus coordinator']);
        }
    }

    // ========================= OPERASIONAL =========================

    public function getAllOperasional()
    {
        try {
            $operasionals = Operasional::with('manager')->get();
            return response()->json(['id' => '1', 'data' => $operasionals]);
        } catch (\Throwable $th) {
            return response()->json(['id' => '0', 'data' => 'Gagal mengambil data operasional']);
        }
    }

    public function createOperasional(Request $request)
    {
        try {
            $request->validate([
                'nama' => 'required|string',
                'alamat' => 'required|string',
                'email' => 'required|email',
                'no_hp' => 'required|string',
                'id_manager' => 'required|exists:managers,id'
            ]);

            $operasional = Operasional::create($request->all());

            return response()->json(['id' => '1', 'data' => $operasional], 201);
        } catch (ValidationException $e) {
            // Jika validasi gagal, tampilkan semua error
            return response()->json([
                'id' => '0',
                'data' => $e->errors() // berisi array: ['email' => ['Email sudah dipakai.'], ...]
            ], 422);
        } catch (\Throwable $th) {
            return response()->json(['id' => '0', 'data' => 'Gagal membuat operasional']);
        }
    }

    public function updateOperasional(Request $request, $id)
    {
        try {
            $operasional = Operasional::findOrFail($id);

            $request->validate([
                'nama' => 'sometimes|string',
                'alamat' => 'sometimes|string',
                'email' => 'sometimes|email',
                'no_hp' => 'sometimes|string',
            ]);

            $operasional->update($request->all());

            return response()->json(['id' => '1', 'data' => $operasional]);
        } catch (ValidationException $e) {
            // Jika validasi gagal, tampilkan semua error
            return response()->json([
                'id' => '0',
                'data' => $e->errors() // berisi array: ['email' => ['Email sudah dipakai.'], ...]
            ], 422);
        } catch (\Throwable $th) {
            return response()->json(['id' => '0', 'data' => 'Gagal mengupdate operasional']);
        }
    }

    public function deleteOperasional($id)
    {
        try {
            $operasional = Operasional::findOrFail($id);
            $operasional->delete();

            return response()->json(['id' => '1', 'data' => 'Operasional berhasil dihapus']);
        } catch (\Throwable $th) {
            return response()->json(['id' => '0', 'data' => 'Gagal menghapus operasional']);
        }
    }
}
