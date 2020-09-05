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
            $table->bigInteger('klasifikasi_id');
            $table->string('nomor_agenda')->nullable();
            $table->string('nomor_surat')->nullable();
            $table->dateTime('tgl_surat')->nullable();
            $table->string('jenis_surat');
            $table->string('sifat_surat');
            $table->string('tujuan_surat');
            $table->string('hal_surat');
            $table->bigInteger('sign_user_id');
            $table->string('lampiran_surat');
            $table->bigInteger('approval_user_id');
            $table->bigInteger('file_id')->nullable();
            $table->string('status');
            $table->string('surat_log')->nullable();

            $table->boolean('is_approve')->nullable();
            $table->bigInteger('approved_by')->nullable();
            $table->dateTime('approved_at')->nullable();

            $table->boolean('is_verify')->nullable();
            $table->bigInteger('verified_by')->nullable();
            $table->dateTime('verified_at')->nullable();

            $table->boolean('is_agenda')->nullable();
            $table->bigInteger('agenda_by')->nullable();
            $table->dateTime('agenda_at')->nullable();
            $table->integer('agenda_file_id')->nullable();

            $table->boolean('is_sign');
            $table->bigInteger('signed_by')->nullable();
            $table->dateTime('signed_at')->nullable();

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
        Schema::dropIfExists('surat_keluar');
    }
}
