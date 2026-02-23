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
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('tanggal');
            $table->string('task');
            $table->time('start_time');
            $table->time('end_time');
            $table->integer('durasi_menit')->nullable();
            $table->text('output')->nullable();

            $table->enum('kategori', ['BSC / OKR', 'Daily Task', 'Improvement Goal', 'Cuti', 'Day Off', 'Libur', 'Ibadah']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};
