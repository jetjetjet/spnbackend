<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLogSuratKeluarsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Schema::create('log_surat_keluar', function (Blueprint $table) {
        //     $table->id();
        //     $table->bigInteger('surat_keluar_id');
        //     $table->bigInteger('disposisi_id')->nullable();
        //     $table->string('aksi');
        //     $table->string('')
            
        //     $table->boolean('active');
        //     $table->timestamp('created_at');
        //     $table->integer('created_by');
        //     $table->timestamp('modified_at')->nullable();
        //     $table->integer('modified_by')->nullable();
        // });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('log_surat_keluars');
    }
}
