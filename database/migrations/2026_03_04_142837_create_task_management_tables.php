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
        Schema::create('sesi_tugas', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->date('tanggal');
            $blueprint->foreignId('divisi_id')->constrained('divisi')->onDelete('cascade');
            $blueprint->foreignId('dibuat_oleh')->constrained('users')->onDelete('cascade');
            $blueprint->enum('status', ['dibuka', 'ditutup'])->default('dibuka');
            $blueprint->timestamps();
            
            $blueprint->unique(['tanggal', 'divisi_id'], 'unique_sesi_tugas');
        });

        Schema::create('tugas', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->foreignId('sesi_tugas_id')->constrained('sesi_tugas')->onDelete('cascade');
            $blueprint->foreignId('ditugaskan_ke')->constrained('users')->onDelete('cascade');
            $blueprint->foreignId('ditugaskan_oleh')->constrained('users')->onDelete('cascade');
            $blueprint->string('judul');
            $blueprint->text('deskripsi')->nullable();
            $blueprint->enum('prioritas', ['rendah', 'sedang', 'tinggi'])->default('sedang');
            $blueprint->enum('status', ['menunggu', 'dikerjakan', 'selesai', 'ditolak'])->default('menunggu');
            $blueprint->enum('status_persetujuan', ['menunggu', 'disetujui', 'ditolak'])->default('menunggu');
            $blueprint->integer('progress')->default(0);
            $blueprint->timestamp('deadline')->nullable();
            $blueprint->timestamps();
        });

        Schema::create('log_tugas', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->foreignId('tugas_id')->constrained('tugas')->onDelete('cascade');
            $blueprint->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $blueprint->text('deskripsi')->nullable();
            $blueprint->integer('progress');
            $blueprint->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('log_tugas');
        Schema::dropIfExists('tugas');
        Schema::dropIfExists('sesi_tugas');
    }
};
