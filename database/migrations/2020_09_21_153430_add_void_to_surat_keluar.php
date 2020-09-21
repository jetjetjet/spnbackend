<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddVoidToSuratKeluar extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('surat_keluar', function (Blueprint $table) {
            $table->boolean('is_void')->nullable();
            $table->bigInteger('voided_by')->nullable();
            $table->dateTime('voided_at')->nullable();
            $table->string('void_remark')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('surat_keluar', function (Blueprint $table) {
            $table->dropColumn('is_void');
            $table->dropColumn('voided_by');
            $table->dropColumn('voided_at');
            $table->dropColumn('void_remark');
        });
    }
}
