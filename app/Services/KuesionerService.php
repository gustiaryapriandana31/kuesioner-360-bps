<?php

// Service untuk operasi bisnis kuesioner seperti copy periode triwulan.

namespace App\Services;

use App\Models\Kuesioner;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class KuesionerService
{
    /**
     * Copy kuesioner sumber ke triwulan berikutnya beserta pertanyaan aktifnya.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function copyFromTW(Kuesioner $source): Kuesioner
    {
        $nextTriwulan = $source->triwulan === 4 ? 1 : $source->triwulan + 1;
        $nextTahun = $source->triwulan === 4 ? $source->tahun + 1 : $source->tahun;

        $exists = Kuesioner::query()
            ->byTrwulan($nextTriwulan, $nextTahun)
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'kuesioner' => "Kuesioner TW{$nextTriwulan}-{$nextTahun} sudah ada.",
            ]);
        }

        return DB::transaction(function () use ($source, $nextTriwulan, $nextTahun) {
            $copy = Kuesioner::query()->create([
                'kode' => "TW{$nextTriwulan}-{$nextTahun}",
                'judul' => "Kuesioner 360 TW{$nextTriwulan} {$nextTahun}",
                'deskripsi' => $source->deskripsi,
                'triwulan' => $nextTriwulan,
                'tahun' => $nextTahun,
                'copied_from_id' => $source->id,
                'status' => 'active',
                'dibuka_pada' => null,
                'ditutup_pada' => null,
                'created_by' => auth()->id() ?? $source->created_by,
                'excluded_pegawai_ids' => [],
                'assign_all' => $source->assign_all,
            ]);

            // Copy many-to-many relationship (pertanyaans)
            $copy->pertanyaans()->attach(
                $source->pertanyaans()->pluck('pertanyaans.id')->toArray()
            );

            return $copy;
        });
    }
}
