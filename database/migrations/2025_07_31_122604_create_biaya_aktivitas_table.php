<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBiayaAktivitasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('biaya_aktivitas', function (Blueprint $table) {
            $table->id();
            $table->text('keterangan');
            $table->integer('biaya');
            $table->date('start_date');
            $table->date('end_date');
            $table->foreignId('id_aktivitas')->constrained('aktifitas')->onDelete('cascade')->onUpdate('cascade');
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
        Schema::dropIfExists('biaya_aktivitas');
    }
}
