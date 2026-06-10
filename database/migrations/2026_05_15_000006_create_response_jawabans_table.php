<?php

// Migration untuk membuat tabel jawaban detail dari setiap response penilaian.

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
        Schema::create('response_jawabans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('response_id')->constrained('responses')->cascadeOnDelete();
            $table->foreignId('pertanyaan_id')->constrained('pertanyaans');
            $table->tinyInteger('nilai');
            $table->timestamps();

            $table->unique(['response_id', 'pertanyaan_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('response_jawabans');
    }
};
