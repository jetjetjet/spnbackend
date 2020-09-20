<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGenErrorlogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gen_errorlog', function (Blueprint $table) {
            $table->id();
            $table->string('action');
            $table->string('modul');
            $table->bigInteger('reference_id')->nullable();
            $table->text('errorlog');
            $table->timestamp('created_at');
            $table->integer('created_by');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('gen_errorlogs');
    }
}
