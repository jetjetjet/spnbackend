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
            $table->bigInteger('klasifikasi_id');
            $table->string('sifat_surat');
            $table->string('tujuan_surat');
            $table->string('hal_surat');
            $table->string('lampiran_surat');
            $table->integer('to_user');
            $table->integer('file_id')->nullable();

            $table->boolean('is_disposition')->nullable();
            $table->bigInteger('disposition_by')->nullable();
            $table->dateTime('disposition_at')->nullable();

            $table->boolean('is_agenda')->nullable();
            $table->bigInteger('agenda_by')->nullable();
            $table->dateTime('agenda_at')->nullable();

            $table->integer('approval_user');
            $table->bigInteger('approved_by')->nullable();
            $table->dateTime('approved_at')->nullable();
            $table->boolean('is_approved');

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
