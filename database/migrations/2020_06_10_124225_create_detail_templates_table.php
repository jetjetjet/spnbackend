<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDetailTemplatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gen_detailtemplate', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('template_id');
            $table->bigInteger('file_id');
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
        Schema::dropIfExists('detail_templates');
    }
}
