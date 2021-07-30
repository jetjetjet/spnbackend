<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDisSuratKeluarsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dis_surat_keluar', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('surat_keluar_id');
            $table->bigInteger('tujuan_user_id')->nullable();
            $table->bigInteger('file_id')->nullable();
            $table->boolean('is_read');
            $table->dateTime('last_read')->nullable();
            $table->string('keterangan')->nullable();
            $table->string('log')->nullable();
            $table->string('logpos')->nullable();

            $table->boolean('active');
            $table->dateTime('created_at');
            $table->integer('created_by');
            $table->dateTime('modified_at')->nullable();
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
        Schema::dropIfExists('dis_surat_keluar');
    }
}
