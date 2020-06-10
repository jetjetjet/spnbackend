<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTemplateSuratsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gen_template', function (Blueprint $table) {
            $table->id();
            $table->string('template_type');
            $table->string('template_name');
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
        Schema::dropIfExists('gen_templatesurat');
    }
}
