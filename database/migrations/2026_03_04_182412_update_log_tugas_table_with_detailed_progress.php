<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('log_tugas', function (Blueprint $table) {
            $table->renameColumn('deskripsi', 'keterangan');
            $table->dropColumn('progress');
            $table->integer('progress_sebelum')->after('user_id')->default(0);
            $table->integer('progress_sesudah')->after('progress_sebelum')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('log_tugas', function (Blueprint $table) {
            $table->renameColumn('keterangan', 'deskripsi');
            $table->dropColumn(['progress_sebelum', 'progress_sesudah']);
            $table->integer('progress')->after('deskripsi');
        });
    }
};
