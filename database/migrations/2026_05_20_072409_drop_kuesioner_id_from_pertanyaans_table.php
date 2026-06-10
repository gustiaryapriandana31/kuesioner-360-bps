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
            $table->dropForeign(['kuesioner_id']);
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
        });
    }
};
