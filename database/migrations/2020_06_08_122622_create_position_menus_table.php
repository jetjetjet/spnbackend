<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePositionMenusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gen_positionmenu', function (Blueprint $table) {
            $table->id();
            $table->integer('position_id');
            $table->integer('menu_id');
            $table->string('permissions', 800)->nullable();

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
        Schema::dropIfExists('position_menus');
    }
}
