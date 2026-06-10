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
        Schema::table('pertanyaans', function (Blueprint $table) {
            // Drop indexes first
            $table->dropUnique(['kuesioner_id', 'urutan']);
            $table->dropIndex('pertanyaans_kuesioner_active_urutan_idx');
            
            // Drop foreign key
            $table->dropForeign(['kuesioner_id']);
            
            // Drop column
            $table->dropColumn('kuesioner_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pertanyaans', function (Blueprint $table) {
            $table->foreignId('kuesioner_id')->nullable()->constrained('kuesioners')->cascadeOnDelete();
            
            // Re-create indexes
            $table->unique(['kuesioner_id', 'urutan']);
            $table->index(
                ['kuesioner_id', 'is_active', 'urutan'],
                'pertanyaans_kuesioner_active_urutan_idx'
            );
        });
    }
};
