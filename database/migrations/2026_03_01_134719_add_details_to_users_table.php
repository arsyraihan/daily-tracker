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
        Schema::table('users', function (Blueprint $table) {
            $table->string('kode_karyawan', 50)->nullable()->unique()->after('password');
            $table->foreignId('divisi_id')->nullable()->constrained('divisi')->onDelete('set null')->after('kode_karyawan');
            $table->foreignId('jabatan_id')->nullable()->constrained('jabatan')->onDelete('set null')->after('divisi_id');
            $table->foreignId('atasan_id')->nullable()->constrained('users')->onDelete('set null')->after('jabatan_id');
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif')->after('atasan_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['divisi_id']);
            $table->dropForeign(['jabatan_id']);
            $table->dropForeign(['atasan_id']);
            $table->dropColumn(['kode_karyawan', 'divisi_id', 'jabatan_id', 'atasan_id', 'status']);
        });
    }
};
