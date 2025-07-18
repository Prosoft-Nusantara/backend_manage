<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Divisi;
use App\Models\Manager;
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
                'password' => bcrypt($request->password)
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
