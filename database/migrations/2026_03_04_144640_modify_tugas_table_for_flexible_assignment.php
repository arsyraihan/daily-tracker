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
        Schema::table('tugas', function (Blueprint $table) {
            // Make ditugaskan_ke nullable to allow for departmental/divisional tasks
            $table->unsignedBigInteger('ditugaskan_ke')->nullable()->change();
            
            // Add divisi_id for departmental tasks
            $table->foreignId('divisi_id')->nullable()->after('sesi_tugas_id')->constrained('divisi')->onDelete('cascade');
            
            // Add assignment type
            $table->enum('tipe_penugasan', ['individu', 'divisi'])->default('individu')->after('ditugaskan_oleh');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tugas', function (Blueprint $table) {
            $table->dropForeign(['divisi_id']);
            $table->dropColumn(['divisi_id', 'tipe_penugasan']);
            $table->unsignedBigInteger('ditugaskan_ke')->nullable(false)->change();
        });
    }
};
