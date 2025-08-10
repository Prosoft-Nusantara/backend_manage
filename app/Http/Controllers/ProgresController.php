<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Progres;
use Illuminate\Support\Facades\Storage;

class ProgresController extends Controller
{
    public function listProgresByProject($id)
    {
        $data = Progres::where('id_project', $id)->get();
        return response()->json(['status' => 'success', 'data' => $data]);
    }

    public function createProgres(Request $request)
    {
        try {
            $validate = $request->validate([
                'id_project' => 'required|integer',
                'tanggal' => 'required|date',
                'keterangan' => 'required|string',
                'file' => 'required|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10048'
            ]);

            // Simpan file ke storage
            $path = $request->file('file')->store('progres_files', 'public');

            $dataUser = auth()->user();
            // Tentukan label berdasarkan level user
            switch ($dataUser->level) {
                case '0':
                    $label = 'admin';
                    break;
                case '1':
                    $label = 'divisi';
                    break;
                case '2':
                    $label = 'manager';
                    break;
                default:
                    $label = 'coordinator';
                    break;
            }
            // Gabungkan label dan nama user
            $nama = $label . ' - ' . $dataUser->name;
            // Simpan ke database
            $progres = Progres::create([
                'id_project'  => $request->id_project,
                'nama'        => $nama,
                'tanggal'     => $request->tanggal,
                'keterangan'  => $request->keterangan,
                'file'        => $path
            ]);

            return response()->json(['status' => 'success', 'data' => $progres], 201);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function updateProgres(Request $request, $id)
    {
        try {
            $progres = Progres::findOrFail($id);

            $validate = $request->validate([
                'tanggal' => 'sometimes|date',
                'keterangan' => 'sometimes|string',
                'file' => 'sometimes|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10048'
            ]);

            // Update data
            if ($request->has('tanggal')) {
                $progres->tanggal = $request->tanggal;
            }

            if ($request->has('keterangan')) {
                $progres->keterangan = $request->keterangan;
            }

            if ($request->hasFile('file')) {
                // Hapus file lama jika ada
                if ($progres->file && Storage::disk('public')->exists($progres->file)) {
                    Storage::disk('public')->delete($progres->file);
                }

                // Upload file baru
                $path = $request->file('file')->store('progres_files', 'public');
                $progres->file = $path;
            }

            $progres->save();

            return response()->json(['status' => 'success', 'data' => $progres]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function deleteProgres($id)
    {
        try {
            $progres = Progres::findOrFail($id);

            // Hapus file jika ada
            if ($progres->file && Storage::disk('public')->exists($progres->file)) {
                Storage::disk('public')->delete($progres->file);
            }

            // Hapus record
            $progres->delete();

            return response()->json(['status' => 'success', 'message' => 'Data berhasil dihapus']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
