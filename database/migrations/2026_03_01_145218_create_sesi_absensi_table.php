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
        Schema::create('sesi_absensi', function (Blueprint $table) {
            $table->id();
            $table->string('nama'); // e.g. "Sesi Masuk Pagi"
            $table->enum('tipe', ['masuk', 'istirahat', 'pulang']);
            $table->date('tanggal');
            $table->time('jam_mulai');
            $table->time('jam_selesai'); // deadline
            $table->foreignId('divisi_id')->constrained('divisi')->onDelete('cascade');
            $table->foreignId('dibuat_oleh')->constrained('users')->onDelete('cascade');
            $table->enum('status', ['dibuka', 'ditutup'])->default('dibuka');
            $table->timestamps();

            $table->unique(['tanggal', 'divisi_id', 'tipe'], 'unique_sesi_tipe');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sesi_absensi');
    }
};
