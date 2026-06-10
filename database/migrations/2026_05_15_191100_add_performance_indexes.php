<?php

// Migration untuk menambah index performa pada tabel responses dan pertanyaans.

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
        // Index gabungan untuk query "completed responses milik penilai per kuesioner"
        // Query: WHERE penilai_id = ? AND kuesioner_id = ? AND status = 'submitted'
        Schema::table('responses', function (Blueprint $table) {
            $table->index(
                ['penilai_id', 'kuesioner_id', 'status'],
                'responses_penilai_kuesioner_status_idx'
            );
        });

        // Index gabungan untuk query load pertanyaan aktif per kuesioner
        // Query: WHERE kuesioner_id = ? AND is_active = 1 ORDER BY urutan
        Schema::table('pertanyaans', function (Blueprint $table) {
            $table->index(
                ['kuesioner_id', 'is_active', 'urutan'],
                'pertanyaans_kuesioner_active_urutan_idx'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('responses', function (Blueprint $table) {
            $table->dropIndex('responses_penilai_kuesioner_status_idx');
        });

        Schema::table('pertanyaans', function (Blueprint $table) {
            $table->dropIndex('pertanyaans_kuesioner_active_urutan_idx');
        });
    }
};
