<?php

// Controller Inertia untuk halaman daftar dan detail pengisian Kuesioner 360.

namespace App\Http\Controllers\Kuesioner;

use App\Http\Controllers\Controller;
use App\Models\Kuesioner;
use App\Models\Pegawai;
use App\Models\Response as KuesionerResponse;
use App\Exports\Penilaian360Export;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class KuesionerController extends Controller
{
    /**
     * Tampilkan daftar kuesioner aktif dan tertutup untuk user.
     * Optimasi: satu batch query untuk semua completedData (eliminasi N+1).
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        $userId = $user->id;
        $pegawai = $user->pegawai;
        $pegawaiId = $pegawai ? $pegawai->id : null;

        // Cache daftar kuesioner — data admin, jarang berubah
        $kuesioners = Cache::remember('kuesioners:list:active_closed', now()->addMinutes(10), function () {
            return Kuesioner::query()
                ->select(['id', 'kode', 'judul', 'deskripsi', 'triwulan', 'tahun', 'status', 'dibuka_pada', 'ditutup_pada', 'excluded_pegawai_ids', 'assign_all'])
                ->whereIn('status', ['active', 'closed'])
                ->withCount('pertanyaans')
                ->orderByDesc('tahun')
                ->orderBy('triwulan')
                ->get();
        });

        // Filter kuesioner berdasarkan hak akses pegawai (assign_all dan excluded)
        $filteredKuesioners = $kuesioners->filter(function (Kuesioner $k) use ($pegawaiId) {
            if (! $k->assign_all) {
                return false;
            }
            if ($pegawaiId && ! empty($k->excluded_pegawai_ids) && in_array($pegawaiId, $k->excluded_pegawai_ids)) {
                return false;
            }
            return true;
        });

        $employeeIds = Pegawai::query()
            ->active()
            ->assessable()
            ->where('user_id', '!=', $userId)
            ->pluck('id');
        $totalPegawai = $employeeIds->count();

        // OPTIMASI: satu query untuk semua progress periode, bukan N+1 loop.
        // Progress harus sama dengan halaman detail: draft dan submitted sama-sama dihitung.
        $kuesionerIds = $filteredKuesioners->pluck('id');
        $allCompleted = KuesionerResponse::query()
            ->select(['responses.kuesioner_id', 'responses.target_id'])
            ->join('pegawais', 'responses.target_id', '=', 'pegawais.id')
            ->whereNull('pegawais.deleted_at')
            ->where('pegawais.is_active', true)
            ->whereNotIn('pegawais.nip', Pegawai::EXCLUDED_ASSESSMENT_NIPS)
            ->where('pegawais.user_id', '!=', $userId)
            ->whereIn('responses.kuesioner_id', $kuesionerIds)
            ->where('responses.penilai_id', $userId)
            ->whereIn('responses.status', ['draft', 'submitted'])
            ->get();

        // Kelompokkan per kuesioner_id di PHP (tanpa query tambahan) dan hindari duplikasi target.
        $completedData = $kuesionerIds->mapWithKeys(fn ($id) => [$id => []])->all();
        foreach ($allCompleted as $resp) {
            $completedData[$resp->kuesioner_id][] = $resp->target_id;
        }
        $completedData = collect($completedData)
            ->map(fn (array $targetIds) => array_values(array_unique($targetIds)))
            ->all();

        return Inertia::render('Kuesioner/Index', [
            'periods'       => $filteredKuesioners->map(function (Kuesioner $k) use ($employeeIds) {
                if (! $k->assign_all) {
                    return $this->formatPeriod($k, 0);
                }
                $excludedIds = $k->excluded_pegawai_ids ?? [];
                $periodTotal = $employeeIds->filter(fn ($id) => !in_array($id, $excludedIds))->count();
                return $this->formatPeriod($k, $periodTotal);
            })->values(),
            'completedData' => $completedData,
            'totalEmployees' => $totalPegawai,
        ]);
    }

    /**
     * Tampilkan halaman pilih pegawai dan isi kuesioner untuk periode tertentu.
     * Route: /kuesioner/{kode}/{pegawai_nama?}
     * Optimasi: caching kuesioner+soal, caching employees, whereIn bukan whereHas.
     */
    public function show(Request $request, string $kode, ?string $pegawai_nama = null): Response
    {
        $user = $request->user();
        $userId = $user->id;

        // Cache kuesioner + pertanyaannya — data admin, jarang berubah
        // Invalidasi cache ketika admin update kuesioner (lewat Filament event)
        $kuesioner = Cache::remember("kuesioner:kode:{$kode}:with_pertanyaans", now()->addMinutes(15), function () use ($kode) {
            return Kuesioner::query()
                ->select(['id', 'kode', 'judul', 'deskripsi', 'triwulan', 'tahun', 'status', 'dibuka_pada', 'ditutup_pada', 'excluded_pegawai_ids', 'assign_all'])
                ->where('kode', $kode)
                ->with([
                    'pertanyaans' => fn ($q) => $q
                        ->select(['pertanyaans.id', 'judul', 'isi', 'urutan', 'poin_min', 'poin_max'])
                        ->where('is_active', true)
                        ->orderBy('urutan'),
                ])
                ->firstOrFail();
        });

        $pertanyaans = $kuesioner->pertanyaans;

        abort_unless($kuesioner->isActive() || $kuesioner->isClosed(), 404);

        $pegawai = $user->pegawai;
        $pegawaiId = $pegawai ? $pegawai->id : null;

        if (! $kuesioner->assign_all) {
            abort(403, 'Anda tidak diberikan akses ke kuesioner ini.');
        }
        if ($pegawaiId && ! empty($kuesioner->excluded_pegawai_ids) && in_array($pegawaiId, $kuesioner->excluded_pegawai_ids)) {
            abort(403, 'Anda dikecualikan dari kuesioner ini.');
        }

        // Cache daftar pegawai assessable (SEMUA, tanpa filter user — filter di PHP)
        // Satu cache entry untuk semua user, TTL 10 menit
        $allEmployees = Cache::remember('pegawais:active:assessable:ordered', now()->addMinutes(10), function () {
            return Pegawai::query()
                ->select(['id', 'user_id', 'nama', 'jabatan', 'departemen', 'foto'])
                ->active()
                ->assessable()
                ->orderBy('nama')
                ->get();
        });

        // Filter di PHP: buang diri sendiri dan pegawai yang dikecualikan
        $excludedIds = $kuesioner->excluded_pegawai_ids ?? [];
        $employees = $allEmployees->filter(function (Pegawai $p) use ($userId, $excludedIds, $kuesioner) {
            if ($p->user_id === $userId) {
                return false;
            }
            if (! $kuesioner->assign_all) {
                return false;
            }
            return ! in_array($p->id, $excludedIds);
        })->values();
        $employeeIds = $employees->pluck('id');

        // OPTIMASI: Join pegawais untuk validasi status aktif/assessable tanpa passing array ID besar
        $completedEmployees = KuesionerResponse::query()
            ->select(['responses.target_id'])
            ->join('pegawais', 'responses.target_id', '=', 'pegawais.id')
            ->whereNull('pegawais.deleted_at')
            ->where('pegawais.is_active', true)
            ->whereNotIn('pegawais.nip', Pegawai::EXCLUDED_ASSESSMENT_NIPS)
            ->where('pegawais.user_id', '!=', $userId)
            ->where('responses.kuesioner_id', $kuesioner->id)
            ->where('responses.penilai_id', $userId)
            ->where('responses.status', 'submitted')
            ->pluck('responses.target_id')
            ->all();

        $total      = $employees->count();
        $completed  = count($completedEmployees);
        $percentage = $total > 0 ? (int) round(($completed / $total) * 100) : 0;

        // Precompute base URL storage — satu kali, bukan tiap iterasi
        $storageBase   = rtrim(\Illuminate\Support\Facades\Storage::disk('public')->url(''), '/');
        $defaultAvatar = asset('images/default-avatar.svg');

        // Map employees dengan kolom minimal yang dibutuhkan frontend
        $employeeData = $employees->map(fn (Pegawai $p) => [
            'id'         => $p->id,
            'name'       => $p->nama,
            'nama'       => $p->nama,
            'position'   => $p->jabatan,
            'jabatan'    => $p->jabatan,
            'department' => $p->departemen,
            'departemen' => $p->departemen,
            'avatar'     => '👤',
            'foto_url'   => $p->foto ? "{$storageBase}/{$p->foto}" : $defaultAvatar,
            'nama_slug'  => Str::slug($p->nama),
        ])->values();

        // Cari pegawai yang sesuai nama slug dari URL (opsional)
        // Bisa masuk form bahkan jika sudah draft atau submitted (untuk preview/edit ulang)
        $initialEmployee = null;
        if ($pegawai_nama) {
            $matched = $employeeData->first(fn ($p) => $p['nama_slug'] === $pegawai_nama);
            if ($matched) {
                $initialEmployee = $matched;
            }
        }

        // completedDrafts: semua target_id yang sudah punya response (draft atau submitted)
        $completedDrafts = KuesionerResponse::query()
            ->select(['responses.target_id'])
            ->join('pegawais', 'responses.target_id', '=', 'pegawais.id')
            ->whereNull('pegawais.deleted_at')
            ->where('pegawais.is_active', true)
            ->whereNotIn('pegawais.nip', Pegawai::EXCLUDED_ASSESSMENT_NIPS)
            ->where('pegawais.user_id', '!=', $userId)
            ->where('responses.kuesioner_id', $kuesioner->id)
            ->where('responses.penilai_id', $userId)
            ->whereIn('responses.status', ['draft', 'submitted'])
            ->pluck('responses.target_id')
            ->all();

        $allDraftsComplete = count($completedDrafts) >= $total;

        return Inertia::render('Kuesioner/Show', [
            'kuesioner' => [
                ...$this->formatPeriod($kuesioner),
                'kode'           => $kuesioner->kode,
                'judul'          => $kuesioner->judul,
                'label_triwulan' => $kuesioner->label_triwulan,
                'questions'      => $pertanyaans->map(fn ($p) => [
                    'id'       => $p->id,
                    'judul'    => $p->judul,
                    'isi'      => $p->isi,
                    'text'     => $p->teks, // Accessor virtual getTeksAttribute()
                    'teks'     => $p->teks,
                    'urutan'   => $p->urutan,
                    'poin_min' => $p->poin_min,
                    'poin_max' => $p->poin_max,
                ])->values(),
            ],
            'employees'           => $employeeData,
            'completedEmployees'  => $completedEmployees,
            'completedDrafts'     => $completedDrafts,
            'allDraftsComplete'   => $allDraftsComplete,
            'progress'            => [
                'completed'  => $completed,
                'total'      => $total,
                'percentage' => $percentage,
            ],
            'initialEmployee'     => $initialEmployee,
        ]);
    }

    /**
     * Submit semua response berstatus draft menjadi submitted sekaligus.
     * Route: POST /kuesioner/{kuesioner}/submit-all
     */
    public function submitAll(Request $request, Kuesioner $kuesioner): \Illuminate\Http\RedirectResponse
    {
        abort_unless($kuesioner->isActive(), 404);

        $userId = $request->user()->id;

        // Validasi: semua pegawai aktif yang bisa dinilai harus sudah punya response minimal draft.
        $excludedIds = $kuesioner->excluded_pegawai_ids ?? [];
        $employeeSubquery = Pegawai::query()
            ->select('id')
            ->active()
            ->assessable()
            ->where('user_id', '!=', $userId)
            ->when(! $kuesioner->assign_all, fn ($q) => $q->whereRaw('1 = 0'))
            ->when(! empty($excludedIds), fn ($q) => $q->whereNotIn('id', $excludedIds));

        $totalHarusNilai = $employeeSubquery->count();

        $totalDraft = KuesionerResponse::query()
            ->where('kuesioner_id', $kuesioner->id)
            ->where('penilai_id', $userId)
            ->whereIn('target_id', $employeeSubquery)
            ->whereIn('status', ['draft', 'submitted'])
            ->count();

        if ($totalDraft < $totalHarusNilai) {
            return back()->withErrors(['submit' => 'Belum semua pegawai dinilai. Lengkapi dulu penilaian yang tersisa.']);
        }

        // Batch update semua draft → submitted
        KuesionerResponse::query()
            ->where('kuesioner_id', $kuesioner->id)
            ->where('penilai_id', $userId)
            ->whereIn('target_id', $employeeSubquery)
            ->where('status', 'draft')
            ->update([
                'status'       => 'submitted',
                'submitted_at' => now(),
            ]);

        // Invalidasi cache progress
        Cache::forget("kuesioner:kode:{$kuesioner->kode}:with_pertanyaans");

        return redirect()->route('kuesioner.index')
            ->with('success', 'Semua penilaian berhasil dikirim!');
    }

    /**
     * Ekspor hasil kuesioner ke Excel.
     * Route: GET /kuesioner/{kuesioner}/export
     */
    public function export(Kuesioner $kuesioner)
    {
        // Penamaan file: Export_Penilaian360_[NamaKuesioner]_[Tanggal].xlsx
        $cleanJudul = str_replace(['/', '\\', '?', '%', '*', ':', '|', '"', '<', '>', ' '], '_', $kuesioner->judul);
        $fileName = "Export_Penilaian360_{$cleanJudul}_" . now()->format('Y-m-d') . ".xlsx";

        return Excel::download(new Penilaian360Export($kuesioner), $fileName);
    }

    /**
     * Format model kuesioner menjadi struktur period yang dipakai React.
     *
     * @return array<string, mixed>
     */
    private function formatPeriod(Kuesioner $kuesioner, ?int $totalEmployees = null): array
    {
        $res = [
            'id'             => $kuesioner->id,
            'code'           => "TW{$kuesioner->triwulan}",
            'kode'           => $kuesioner->kode,
            'title'          => $kuesioner->judul,
            'judul'          => $kuesioner->judul,
            'month'          => "{$kuesioner->label_triwulan} {$kuesioner->tahun}",
            'triwulan'       => $kuesioner->triwulan,
            'tahun'          => $kuesioner->tahun,
            'status'         => $kuesioner->status === 'closed' ? 'completed' : 'active',
            'backend_status' => $kuesioner->status,
            'description'    => $kuesioner->deskripsi ?: "Penilaian periode {$kuesioner->label_triwulan} {$kuesioner->tahun}",
            'deskripsi'      => $kuesioner->deskripsi,
        ];

        if ($totalEmployees !== null) {
            $res['total_employees'] = $totalEmployees;
        }

        return $res;
    }
}
