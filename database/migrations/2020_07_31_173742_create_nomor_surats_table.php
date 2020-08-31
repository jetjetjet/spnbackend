<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNomorSuratsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gen_nomor_surat_keluar', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('klasifikasi_id');
            $table->bigInteger('surat_keluar_id');
            $table->string('prefix');
            $table->bigInteger('urut_surat');
            $table->bigInteger('urut_agenda');
            $table->string('no_surat');
            $table->string('no_agenda');
            $table->integer('periode');

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
        Schema::dropIfExists('nomor_surats');
    }
}
