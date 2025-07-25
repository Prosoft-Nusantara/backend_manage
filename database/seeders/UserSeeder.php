<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Divisi;
use App\Models\Manager;
use App\Models\Karyawan;
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

        Divisi::create([
            'nama_divisi' => 'IT',
            'deskripsi' => 'IT',
            'id_kepala_divisi' => '2',
        ]);
        Manager::create([
            'nama_manager' => 'Software',
            'deskripsi' => 'Software',
            'id_divisi' => '2',
            'id_manager' => '3',
        ]);
        Karyawan::create([
            'nama' => 'Andi',
            'alamat' => 'Surabaya',
            'email' => 'andi@gmail.com',
            'no_hp' => '0819',
            'jabatan' => '0',
            'id_manager' => '1',
        ]);
    }
}
