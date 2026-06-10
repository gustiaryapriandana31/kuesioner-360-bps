<?php

// Migration untuk membuat tabel master periode kuesioner 360.

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
        Schema::create('kuesioners', function (Blueprint $table) {
            $table->id();
            $table->string('kode')->unique();
            $table->string('judul');
            $table->text('deskripsi')->nullable();
            $table->tinyInteger('triwulan');
            $table->year('tahun');
            $table->foreignId('copied_from_id')->nullable()->constrained('kuesioners')->nullOnDelete();
            $table->enum('status', ['draft', 'active', 'closed'])->default('draft');
            $table->timestamp('dibuka_pada')->nullable();
            $table->timestamp('ditutup_pada')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['triwulan', 'tahun']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kuesioners');
    }
};
