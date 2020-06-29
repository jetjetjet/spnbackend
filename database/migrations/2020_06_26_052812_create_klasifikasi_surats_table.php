<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKlasifikasiSuratsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gen_klasifikasi_surat', function (Blueprint $table) {
            $table->id();
            $table->string('kode_klasifikasi');
            $table->string('nama_klasifikasi');
            $table->string('detail')->nullable();

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
        Schema::dropIfExists('klasifikasi_surats');
    }
}
