<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePositionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gen_position', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('group_id')->nullable();
            $table->string('position_name');
            $table->string('position_type');
            $table->string('detail')->nullable();
            $table->boolean('is_parent')->nullable();
            $table->boolean('is_admin')->nullable();
            $table->boolean('is_kadin')->nullable();
            $table->boolean('is_sekretaris')->nullable();
            $table->boolean('is_subagumum')->nullable();
            $table->boolean('is_officer')->nullable();
            $table->bigInteger('parent_id')->nullable();

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
        Schema::dropIfExists('positions');
    }
}