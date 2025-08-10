<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_pemesanan');
            $table->string('nama_proyek');
            $table->string('client');
            $table->integer('total_nilai_kontrak');
            $table->integer('rencana_biaya');
            $table->integer('realisasi_budget')->nullable();
            $table->date('tanggal_pembayaran')->nullable();
            $table->string('invoice')->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->string('lampiran_proyek')->nullable();
            $table->text('keterangan_rejek')->nullable();
            $table->enum('status',[
                '-', // belum divalidasi divisi dan kaunit
                '0-', // sudah divalidasi divisi dan belum di validasi kaunit
                '-0', // sudah divalidasi kaunit dan belum di validasi divisi
                '0-0', // belum selesai -> accepted
                '1', // piutang
                '2', // lunas
                '3' // rejek
            ])->default('-');
            $table->enum('kategori',[
                '0', //  TIK 1A
                '1' //  TIK 1B
            ]);
            $table->string('bast_kontrak')->nullable(); // alis bast kontrak
            $table->string('surat_pembayaran')->nullable(); // alis bast kontrak
            $table->integer('biaya_akomodasi')->nullable();
            $table->string('pihak_pemberi_biaya')->nullable();
            $table->foreignId('id_manager')->constrained('users')->onDelete('cascade')->onUpdate('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('projects');
    }
}
