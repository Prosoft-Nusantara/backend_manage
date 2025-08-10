<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Divisi;
use App\Models\KaUnit;
use App\Models\Manager;
use App\Models\Karyawan;
use App\Models\Project;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::create([
            'name' => 'admin',
            'email' => 'admin@gmail.com',
            'level' => '0',
            'password' => Hash::make('12345'),
        ]);
        User::create([
            'name' => 'divisi',
            'email' => 'divisi@gmail.com',
            'level' => '1',
            'password' => Hash::make('12345'),
        ]);
        User::create([
            'name' => 'manager',
            'email' => 'manager@gmail.com',
            'level' => '2',
            'password' => Hash::make('12345'),
        ]);
        User::create([
            'name' => 'coordinator',
            'email' => 'coordinator@gmail.com',
            'level' => '3',
            'password' => Hash::make('12345'),
        ]);
        User::create([
            'name' => 'kaunit',
            'email' => 'kaunit@gmail.com',
            'level' => '4',
            'password' => Hash::make('12345'),
        ]);

        Divisi::create([
            'nama_divisi' => 'IT',
            'deskripsi' => 'IT',
            'id_kepala_divisi' => '2',
        ]);
        KaUnit::create([
            'nama_unit' => 'Unit Data',
            'deskripsi' => 'Unit Data',
            'id_kepala_unit' => '5',
        ]);
        Manager::create([
            'nama_manager' => 'Software',
            'deskripsi' => 'Software',
            'id_divisi' => '2',
            'id_manager' => '3',
        ]);
        Karyawan::create([
            'nama' => 'Budi',
            'alamat' => 'Surabaya',
            'email' => 'budi@gmail.com',
            'no_hp' => '0819',
            'jabatan' => '0',
            'id_manager' => '1',
            'id_user' => '4',
        ]);
        Karyawan::create([
            'nama' => 'Andi',
            'alamat' => 'Surabaya',
            'email' => 'andi@gmail.com',
            'no_hp' => '0819',
            'jabatan' => '0',
            'id_manager' => '1',
        ]);

        Project::create([
            'nomor_pemesanan'      => 'PO-001',
            'nama_proyek'          => 'Proyek Surabaya',
            'client'               => 'PT Surya Abadi',
            'total_nilai_kontrak'  => 500000000,
            'rencana_biaya'        => 300000000,
            'realisasi_budget'     => 250000000,
            'tanggal_pembayaran'   => now()->addDays(30),
            'invoice'              => 'INV-001.pdf',
            'start_date'           => now(),
            'end_date'             => now()->addMonths(6),
            'lampiran_proyek'      => 'lampiran-proyek.pdf',
            'status'               => '-', // belum selesai
            'kategori'             => '0', // TIK 1A
            'bast_kontrak'         => 'bast_kontrak.pdf',
            'surat_pembayaran'     => 'surat_pembayaran.pdf',
            'biaya_akomodasi'      => 5000000,
            'pihak_pemberi_biaya'  => 'PT Indah Makmur',
            'id_manager'           => 3, // pastikan user id 1 ada di tabel users
        ]);
    }
}
