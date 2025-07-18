<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTimProjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tim_projects', function (Blueprint $table) {
            $table->id();
            $table->enum('jenis_tim',[
                '0', // tim coordinator
                '1' // tim operasional
            ]);
            $table->string('id_tim');
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
        Schema::dropIfExists('tim_projects');
    }
}
