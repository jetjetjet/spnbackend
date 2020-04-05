<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gen_user', function (Blueprint $table) {
            $table->id();
            $table->string('username',50);
            $table->string('email');
            $table->string('full_name', 100)->nullable();
            $table->string('password', 100);
            $table->string('phone', 15)->nullable();
            $table->string('address', 400)->nullable();
            $table->boolean('active');
            $table->dateTime('last_login')->nullable();
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
        Schema::dropIfExists('users');
    }
}
