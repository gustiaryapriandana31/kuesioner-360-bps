<?php

// Controller API untuk membuat response, menyimpan jawaban, dan membaca progress.

namespace App\Http\Controllers\Kuesioner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Kuesioner\StoreResponseRequest;
use App\Http\Requests\Kuesioner\UpdateResponseRequest;
use App\Models\Kuesioner;
use App\Models\Pegawai;
use App\Models\Pertanyaan;
use App\Models\Response as KuesionerResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ResponseController extends Controller
{
    /**
     * Buat atau ambil draft response untuk kombinasi kuesioner, penilai, dan target.
     */
    public function store(StoreResponseRequest $request): JsonResponse
    {
        $user = $request->user();
        $kuesioner = Kuesioner::query()->findOrFail($request->integer('kuesioner_id'));
        $target = Pegawai::query()->active()->assessable()->find($request->integer('target_id'));

        if (! $target) {
            return $this->apiResponse(false, [], 'Pegawai target tidak aktif atau tidak ditemukan.', 422);
        }

        if (! $kuesioner->isActive()) {
            return $this->apiResponse(false, [], 'Kuesioner tidak sedang aktif.', 422);
        }

        if (! $kuesioner->assign_all || in_array($target->id, $kuesioner->excluded_pegawai_ids ?? [])) {
            return $this->apiResponse(false, [], 'Pegawai target dikecualikan dari kuesioner ini.', 422);
        }

        if ($target->user_id === $user->id) {
            return $this->apiResponse(false, [], 'Anda tidak dapat menilai diri sendiri.', 422);
        }

        $submittedExists = KuesionerResponse::query()
            ->where('kuesioner_id', $kuesioner->id)
            ->where('penilai_id', $user->id)
            ->where('target_id', $target->id)
            ->where('status', 'submitted')
            ->exists();

        if ($submittedExists) {
            return $this->apiResponse(false, [], 'Pegawai ini sudah dinilai pada kuesioner ini.', 422);
        }

        $response = KuesionerResponse::query()->firstOrCreate(
            [
                'kuesioner_id' => $kuesioner->id,
                'penilai_id' => $user->id,
                'target_id' => $target->id,
            ],
            [
                'status' => 'draft',
            ],
        );

        return $this->apiResponse(true, [
            'response_id' => $response->id,
            'response' => [
                'id' => $response->id,
                'status' => $response->status,
                'progress' => $response->progress,
            ],
        ], 'Draft response siap digunakan.');
    }

    /**
     * Simpan jawaban response dan submit jika diminta.
     */
    public function update(UpdateResponseRequest $request, KuesionerResponse $response): JsonResponse
    {
        if ($response->penilai_id !== $request->user()->id) {
            return $this->apiResponse(false, [], 'Response ini bukan milik Anda.', 403);
        }

        if ($response->status === 'submitted') {
            return $this->apiResponse(false, [], 'Response yang sudah dikirim tidak dapat diubah.', 422);
        }

        if (! $response->kuesioner?->isActive()) {
            return $this->apiResponse(false, [], 'Kuesioner tidak sedang aktif.', 422);
        }

        try {
            DB::transaction(function () use ($request, $response) {
                foreach ($request->validated('jawabans') as $jawaban) {
                    $pertanyaan = Pertanyaan::query()
                        ->active()
                        ->whereHas('kuesioners', fn ($q) => $q->where('kuesioners.id', $response->kuesioner_id))
                        ->find($jawaban['pertanyaan_id']);

                    if (! $pertanyaan) {
                        throw ValidationException::withMessages([
                            'pertanyaan_id' => 'Pertanyaan tidak aktif atau tidak sesuai dengan kuesioner ini.',
                        ]);
                    }

                    $nilai = (int) $jawaban['nilai'];

                    if ($nilai < $pertanyaan->poin_min || $nilai > $pertanyaan->poin_max) {
                        throw ValidationException::withMessages([
                            'nilai' => "Nilai untuk pertanyaan urutan {$pertanyaan->urutan} harus berada di antara {$pertanyaan->poin_min} dan {$pertanyaan->poin_max}.",
                        ]);
                    }

                    $response->jawabans()->updateOrCreate(
                        ['pertanyaan_id' => $pertanyaan->id],
                        ['nilai' => $nilai],
                    );
                }

                if ($request->boolean('submit')) {
                    $response->refresh()->load('kuesioner');

                    if (! $response->isComplete()) {
                        throw ValidationException::withMessages([
                            'jawabans' => 'Semua pertanyaan aktif harus dijawab sebelum response dikirim.',
                        ]);
                    }

                    $response->update([
                        'status' => 'submitted',
                        'submitted_at' => now(),
                    ]);
                }
            });
        } catch (ValidationException $exception) {
            return $this->apiResponse(false, [
                'errors' => $exception->errors(),
            ], collect($exception->errors())->flatten()->first() ?: 'Validasi jawaban gagal.', 422);
        }

        $response->refresh()->load(['jawabans', 'kuesioner']);

        return $this->apiResponse(true, [
            'response' => [
                'id' => $response->id,
                'status' => $response->status,
                'progress' => $response->progress,
                'submitted_at' => $response->submitted_at?->toISOString(),
            ],
        ], $response->status === 'submitted' ? 'Response berhasil dikirim.' : 'Jawaban berhasil disimpan.');
    }

    /**
     * Ambil jawaban tersimpan untuk mode pratinjau/edit ulang.
     */
    public function savedAnswers(\Illuminate\Http\Request $request): JsonResponse
    {
        $validated = $request->validate([
            'kuesioner_id' => ['required', 'integer', 'exists:kuesioners,id'],
            'target_id' => ['required', 'integer', 'exists:pegawais,id'],
        ]);

        $user = $request->user();
        $kuesioner = Kuesioner::query()->findOrFail($validated['kuesioner_id']);
        $target = Pegawai::query()->active()->assessable()->find($validated['target_id']);

        if (! $target) {
            return $this->apiResponse(false, [], 'Pegawai target tidak aktif atau tidak ditemukan.', 422);
        }

        if (! ($kuesioner->isActive() || $kuesioner->isClosed())) {
            return $this->apiResponse(false, [], 'Kuesioner tidak sedang aktif.', 422);
        }

        if ($target->user_id === $user->id) {
            return $this->apiResponse(false, [], 'Anda tidak dapat menilai diri sendiri.', 422);
        }

        $response = KuesionerResponse::query()
            ->select(['id', 'status', 'submitted_at'])
            ->with(['jawabans:id,response_id,pertanyaan_id,nilai'])
            ->where('kuesioner_id', $kuesioner->id)
            ->where('penilai_id', $user->id)
            ->where('target_id', $target->id)
            ->whereIn('status', ['draft', 'submitted'])
            ->first();

        if (! $response) {
            return $this->apiResponse(false, [], 'Jawaban tersimpan tidak ditemukan.', 404);
        }

        return $this->apiResponse(true, [
            'response_id' => $response->id,
            'status' => $response->status,
            'answers' => $response->jawabans
                ->mapWithKeys(fn ($jawaban) => [$jawaban->pertanyaan_id => $jawaban->nilai])
                ->all(),
        ], 'Jawaban tersimpan berhasil diambil.');
    }

    /**
     * Update satu jawaban saja dari halaman pratinjau.
     */
    public function updateSingleAnswer(\Illuminate\Http\Request $request): JsonResponse
    {
        $validated = $request->validate([
            'kuesioner_id' => ['required', 'integer', 'exists:kuesioners,id'],
            'target_id' => ['required', 'integer', 'exists:pegawais,id'],
            'pertanyaan_id' => ['required', 'integer', 'exists:pertanyaans,id'],
            'nilai' => ['required', 'integer', 'min:1', 'max:10'],
        ]);

        $user = $request->user();
        $kuesioner = Kuesioner::query()->findOrFail($validated['kuesioner_id']);
        $target = Pegawai::query()->active()->assessable()->find($validated['target_id']);

        if (! $target) {
            return $this->apiResponse(false, [], 'Pegawai target tidak aktif atau tidak ditemukan.', 422);
        }

        if (! $kuesioner->isActive()) {
            return $this->apiResponse(false, [], 'Kuesioner tidak sedang aktif.', 422);
        }

        if ($target->user_id === $user->id) {
            return $this->apiResponse(false, [], 'Anda tidak dapat menilai diri sendiri.', 422);
        }

        $pertanyaan = Pertanyaan::query()
            ->active()
            ->whereHas('kuesioners', fn ($q) => $q->where('kuesioners.id', $kuesioner->id))
            ->find($validated['pertanyaan_id']);

        if (! $pertanyaan) {
            return $this->apiResponse(false, [], 'Pertanyaan tidak aktif atau tidak sesuai dengan kuesioner ini.', 422);
        }

        $nilai = (int) $validated['nilai'];
        if ($nilai < $pertanyaan->poin_min || $nilai > $pertanyaan->poin_max) {
            return $this->apiResponse(false, [], "Nilai untuk pertanyaan urutan {$pertanyaan->urutan} harus antara {$pertanyaan->poin_min} dan {$pertanyaan->poin_max}.", 422);
        }

        $response = KuesionerResponse::query()
            ->where('kuesioner_id', $kuesioner->id)
            ->where('penilai_id', $user->id)
            ->where('target_id', $target->id)
            ->first();

        if (! $response) {
            return $this->apiResponse(false, [], 'Jawaban tersimpan tidak ditemukan.', 404);
        }

        if ($response->status === 'submitted') {
            return $this->apiResponse(false, [], 'Penilaian yang sudah dikirim final tidak dapat diubah.', 422);
        }

        $response->jawabans()->updateOrCreate(
            ['pertanyaan_id' => $pertanyaan->id],
            ['nilai' => $nilai],
        );

        return $this->apiResponse(true, [
            'pertanyaan_id' => $pertanyaan->id,
            'nilai' => $nilai,
        ], 'Poin soal berhasil diperbarui.');
    }

    /**
     * Single-round-trip: buat/ambil response dan batch upsert semua jawaban sebagai draft.
     * Menggantikan POST /api/responses + PUT /api/responses/{id} yang butuh 2 round trip.
     * Optimasi: dari ~13 queries menjadi 4 queries total.
     */
    public function quickSubmit(\Illuminate\Http\Request $request): JsonResponse
    {
        $validated = $request->validate([
            'kuesioner_id' => ['required', 'integer', 'exists:kuesioners,id'],
            'target_id'    => ['required', 'integer', 'exists:pegawais,id'],
            'jawabans'     => ['required', 'array', 'min:1'],
            'jawabans.*.pertanyaan_id' => ['required', 'integer'],
            'jawabans.*.nilai'         => ['required', 'integer', 'min:1', 'max:10'],
        ]);

        $user      = $request->user();
        $kuesioner = Kuesioner::query()->findOrFail($validated['kuesioner_id']);
        $target    = Pegawai::query()->active()->assessable()->find($validated['target_id']);

        if (! $target) {
            return $this->apiResponse(false, [], 'Pegawai target tidak aktif atau tidak ditemukan.', 422);
        }

        if (! $kuesioner->isActive()) {
            return $this->apiResponse(false, [], 'Kuesioner tidak sedang aktif.', 422);
        }

        if (! $kuesioner->assign_all || in_array($target->id, $kuesioner->excluded_pegawai_ids ?? [])) {
            return $this->apiResponse(false, [], 'Pegawai target dikecualikan dari kuesioner ini.', 422);
        }

        if ($target->user_id === $user->id) {
            return $this->apiResponse(false, [], 'Anda tidak dapat menilai diri sendiri.', 422);
        }

        $pertanyaans = Pertanyaan::query()
            ->select(['pertanyaans.id', 'poin_min', 'poin_max', 'urutan'])
            ->active()
            ->whereHas('kuesioners', fn ($q) => $q->where('kuesioners.id', $kuesioner->id))
            ->get()
            ->keyBy('id');

        $totalPertanyaan = $pertanyaans->count();

        // Validasi semua jawaban di PHP (tanpa extra query)
        foreach ($validated['jawabans'] as $jawaban) {
            $pertanyaan = $pertanyaans->get($jawaban['pertanyaan_id']);

            if (! $pertanyaan) {
                return $this->apiResponse(false, [], "Pertanyaan ID {$jawaban['pertanyaan_id']} tidak valid untuk kuesioner ini.", 422);
            }

            $nilai = (int) $jawaban['nilai'];

            if ($nilai < $pertanyaan->poin_min || $nilai > $pertanyaan->poin_max) {
                return $this->apiResponse(false, [], "Nilai untuk pertanyaan urutan {$pertanyaan->urutan} harus antara {$pertanyaan->poin_min} dan {$pertanyaan->poin_max}.", 422);
            }
        }

        if (count($validated['jawabans']) < $totalPertanyaan) {
            return $this->apiResponse(false, [], 'Semua pertanyaan aktif harus dijawab.', 422);
        }

        try {
            $result = DB::transaction(function () use ($user, $kuesioner, $target, $validated, $pertanyaans) {
                // Query 2: firstOrCreate response (draft)
                $response = KuesionerResponse::query()->firstOrCreate(
                    [
                        'kuesioner_id' => $kuesioner->id,
                        'penilai_id'   => $user->id,
                        'target_id'    => $target->id,
                    ],
                    ['status' => 'draft']
                );

                if ($response->status === 'submitted') {
                    return ['already_submitted' => true, 'response' => $response];
                }

                $now = now();

                // Query 3: batch upsert semua jawaban sekaligus (1 query untuk N soal)
                $upsertRows = collect($validated['jawabans'])->map(fn ($j) => [
                    'response_id'    => $response->id,
                    'pertanyaan_id'  => $j['pertanyaan_id'],
                    'nilai'          => (int) $j['nilai'],
                    'created_at'     => $now,
                    'updated_at'     => $now,
                ])->all();

                DB::table('response_jawabans')->upsert(
                    $upsertRows,
                    ['response_id', 'pertanyaan_id'],
                    ['nilai', 'updated_at']
                );

                // Query 4: pastikan status tetap draft. Submit final dilakukan lewat "Kirim Semua Penilaian".
                $response->update([
                    'status'       => 'draft',
                    'submitted_at' => null,
                ]);

                return ['already_submitted' => false, 'response' => $response];
            });
        } catch (\Throwable $e) {
            return $this->apiResponse(false, [], 'Gagal menyimpan penilaian: ' . $e->getMessage(), 500);
        }

        if ($result['already_submitted']) {
            return $this->apiResponse(false, [], 'Pegawai ini sudah dinilai pada kuesioner ini.', 422);
        }

        return $this->apiResponse(true, [
            'response_id' => $result['response']->id,
        ], 'Penilaian berhasil disimpan sebagai draft.');
    }

    /**
     * Hitung progress penilaian user pada satu kuesioner.
     */
    public function getProgress(Kuesioner $kuesioner): JsonResponse
    {
        $user = auth()->user();
        $excludedIds = $kuesioner->excluded_pegawai_ids ?? [];
        $total = Pegawai::query()
            ->active()
            ->assessable()
            ->where('user_id', '!=', $user->id)
            ->when(! $kuesioner->assign_all, fn ($q) => $q->whereRaw('1 = 0'))
            ->when(! empty($excludedIds), fn ($q) => $q->whereNotIn('id', $excludedIds))
            ->count();

        $employeeSubquery = Pegawai::query()
            ->select('id')
            ->active()
            ->assessable()
            ->where('user_id', '!=', $user->id)
            ->when(! $kuesioner->assign_all, fn ($q) => $q->whereRaw('1 = 0'))
            ->when(! empty($excludedIds), fn ($q) => $q->whereNotIn('id', $excludedIds));

        $completed = $kuesioner->responses()
            ->where('penilai_id', $user->id)
            ->whereIn('status', ['draft', 'submitted'])
            ->whereIn('target_id', $employeeSubquery)
            ->count();

        $percentage = $total > 0 ? (int) round(($completed / $total) * 100) : 0;

        return $this->apiResponse(true, [
            'completed' => $completed,
            'total' => $total,
            'percentage' => $percentage,
        ], 'Progress berhasil diambil.');
    }

    /**
     * Format response JSON API yang konsisten.
     *
     * @param  array<string, mixed>  $data
     */
    private function apiResponse(bool $success, array $data, string $message, int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => $success,
            'data' => $data,
            'message' => $message,
        ], $status);
    }
}
