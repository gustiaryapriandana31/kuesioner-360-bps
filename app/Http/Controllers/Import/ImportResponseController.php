<?php

// Controller halaman import file Excel penilaian 360 ke tabel responses dan response_jawabans.

namespace App\Http\Controllers\Import;

use App\Http\Controllers\Controller;
use App\Jobs\ImportResponseJob;
use App\Models\Kuesioner;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ImportResponseController extends Controller
{
    /**
     * Tampilkan halaman form import dengan daftar kuesioner tersedia.
     */
    public function index(Request $request): View
    {
        $kuesioners = Kuesioner::withCount('responses')
            ->orderByDesc('tahun')
            ->orderBy('triwulan')
            ->get();

        // Ambil hasil import terakhir (jika ada) dari cache
        $importResult = cache()->get("import_response_result_{$request->user()->id}");

        return view('import.response', compact('kuesioners', 'importResult'));
    }

    /**
     * Validasi upload, simpan file sementara, dan dispatch job ke queue.
     */
    public function import(Request $request): RedirectResponse
    {
        $kuesioner = Kuesioner::query()->findOrFail((int) $request->kuesioner_id);
        $hasResponses = $kuesioner->responses()->exists();

        $rules = [
            'kuesioner_id' => ['required', 'exists:kuesioners,id'],
            'file'         => ['required', 'file', 'mimes:xlsx', 'max:20480'],
        ];

        if ($hasResponses) {
            $rules['agree_replace'] = ['required', 'accepted'];
        }

        $request->validate($rules, [
            'kuesioner_id.required' => 'Pilih kuesioner terlebih dahulu.',
            'kuesioner_id.exists'   => 'Kuesioner tidak ditemukan.',
            'file.required'         => 'File Excel wajib diunggah.',
            'file.mimes'            => 'File harus berformat .xlsx.',
            'file.max'              => 'Ukuran file maksimal 20 MB.',
            'agree_replace.required' => 'Kuesioner ini sudah memiliki data respon. Anda harus menyetujui penghapusan data lama.',
            'agree_replace.accepted' => 'Anda harus mencentang persetujuan untuk menimpa data respon lama.',
        ]);

        $status = $kuesioner->status === 'closed' ? 'submitted' : 'draft';
        $deleteExisting = $request->boolean('agree_replace', false);

        // Simpan file di storage/app/imports/temp/
        $path = $request->file('file')->store('imports/temp');

        // Hapus hasil import lama dari cache sebelum memulai yang baru
        cache()->forget("import_response_result_{$request->user()->id}");

        // Dispatch ke queue agar tidak timeout
        ImportResponseJob::dispatch(
            $path,
            (int) $request->kuesioner_id,
            $status,
            $request->user()->id,
            $deleteExisting
        );

        return redirect()
            ->route('import.response.index')
            ->with('success', 'File berhasil diunggah dan sedang diproses. Refresh halaman ini dalam beberapa saat untuk melihat hasilnya.');
    }
}
