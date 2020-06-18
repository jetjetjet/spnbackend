<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSuratMasuksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('surat_masuk', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('file_id');
            $table->string('asal_surat');
            $table->string('to_user_id');
            $table->string('perihal')->nullable();
            $table->string('nomor_surat');
            $table->dateTime('tgl_surat');
            $table->dateTime('tgl_diterima');
            $table->string('lampiran')->nullable();
            $table->string('sifat_surat');
            $table->string('klasifikasi');
            $table->string('keterangan')->nullable();
            $table->string('prioritas')->nullable();

            $table->bigInteger('closed_by')->nullable();
            $table->dateTime('closed_at')->nullable();
            $table->boolean('is_closed');

            $table->boolean('active');
            $table->timestamp('created_at');
            $table->integer('created_by');
            $table->timestamp('modified_at')->nullable();
            $table->integer('modified_by')->nullable();            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('surat_masuks');
    }
}
