<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAktifitasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('aktifitas', function (Blueprint $table) {
            $table->id();
            $table->string('aktivitas');
            $table->string('pic');
            $table->integer('biaya');
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('status', [
                '0', // belum selesai
                '1', // selesai
            ])->default('0');
            $table->string('file')->nullable();
            $table->foreignId('id_project')->constrained('projects')->onDelete('cascade')->onUpdate('cascade');
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
        Schema::dropIfExists('aktifitas');
    }
}
