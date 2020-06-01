<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSuratKeluarsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('surat_keluar', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_agenda')->nullable();
            $table->string('nomor_surat')->nullable();
            $table->dateTime('tgl_surat')->nullable();
            $table->string('jenis_surat');
            $table->integer('klasifikasi_surat');
            $table->string('sifat_surat');
            $table->string('tujuan_surat');
            $table->string('hal_surat');
            $table->string('lampiran_surat');
            $table->integer('approval_user');
            $table->integer('file_id')->nullable();

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
        Schema::dropIfExists('surat_keluars');
    }
}
