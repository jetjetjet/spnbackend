<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDisSuratMasuksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dis_surat_masuk', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('surat_masuk_id');
            $table->bigInteger('to_user_id');
            $table->string('arahan')->nullable();
            $table->boolean('is_tembusan')->nullable();
            $table->boolean('is_private')->nullable();
            $table->boolean('is_read')->nullable();
            $table->dateTime('last_read')->nullable();
            $table->string('log')->nullable();
            $table->string('logpos')->nullable();

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
        Schema::dropIfExists('dis_surat_masuks');
    }
}
