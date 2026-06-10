<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-50 dark:bg-gray-900">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Import Response Excel - Kuesioner 360</title>
    @vite(['resources/css/app.css'])
</head>
<body class="h-full font-sans antialiased text-gray-900 dark:text-gray-100">
    <div class="min-h-full flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8 bg-white dark:bg-gray-800 p-8 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700">
            <div>
                <h2 class="mt-2 text-center text-3xl font-extrabold tracking-tight">
                    Import Excel 360
                </h2>
                <p class="mt-2 text-center text-sm text-gray-500 dark:text-gray-400">
                    Upload hasil Kuesioner 360 dari Google Forms
                </p>
            </div>

            @if(session('success'))
                <div class="rounded-md bg-green-50 dark:bg-green-900/50 p-4 border border-green-200 dark:border-green-800">
                    <div class="flex">
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-green-800 dark:text-green-200">
                                Berhasil
                            </h3>
                            <div class="mt-2 text-sm text-green-700 dark:text-green-300">
                                <p>{{ session('success') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            @if($errors->any())
                <div class="rounded-md bg-red-50 dark:bg-red-900/50 p-4 border border-red-200 dark:border-red-800">
                    <div class="flex">
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800 dark:text-red-200">
                                Terjadi Kesalahan
                            </h3>
                            <div class="mt-2 text-sm text-red-700 dark:text-red-300">
                                <ul class="list-disc pl-5 space-y-1">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Ringkasan Hasil Import Terakhir -->
            @if(isset($importResult))
                <div class="rounded-md bg-blue-50 dark:bg-blue-900/30 p-4 border border-blue-200 dark:border-blue-800 mb-6">
                    <h3 class="text-sm font-bold text-blue-800 dark:text-blue-300 border-b border-blue-200 dark:border-blue-800 pb-2 mb-2">
                        Hasil Import Terakhir ({{ \Carbon\Carbon::parse($importResult['selesai_pada'])->translatedFormat('d M Y H:i:s') }})
                    </h3>
                    <ul class="text-sm text-blue-700 dark:text-blue-200 space-y-1">
                        <li>✅ Total Penilai Diproses: <strong>{{ $importResult['total_penilai'] }}</strong></li>
                        <li>✅ Total Response Dibuat: <strong>{{ $importResult['total_response'] }}</strong></li>
                        <li>✅ Total Jawaban Disimpan: <strong>{{ $importResult['total_jawaban'] }}</strong></li>
                        
                        @if($importResult['nilai_invalid'] > 0)
                            <li class="text-amber-600 dark:text-amber-400">⚠️ Nilai Invalid (Dilewati): <strong>{{ $importResult['nilai_invalid'] }}</strong></li>
                        @endif
                    </ul>

                    @if(count($importResult['penilai_tidak_ditemukan']) > 0)
                        <div class="mt-3">
                            <span class="text-xs font-semibold text-red-600 dark:text-red-400">Penilai Tidak Ditemukan:</span>
                            <div class="mt-1 max-h-24 overflow-y-auto text-xs text-red-500 bg-white/50 dark:bg-black/20 p-2 rounded">
                                {{ implode(', ', $importResult['penilai_tidak_ditemukan']) }}
                            </div>
                        </div>
                    @endif

                    @if(count($importResult['target_tidak_ditemukan']) > 0)
                        <div class="mt-3">
                            <span class="text-xs font-semibold text-red-600 dark:text-red-400">Target Pegawai Tidak Ditemukan:</span>
                            <div class="mt-1 max-h-24 overflow-y-auto text-xs text-red-500 bg-white/50 dark:bg-black/20 p-2 rounded">
                                {{ implode(', ', $importResult['target_tidak_ditemukan']) }}
                            </div>
                        </div>
                    @endif
                </div>
            @endif

            <form class="mt-8 space-y-6" action="{{ route('import.response.store') }}" method="POST" enctype="multipart/form-data" id="import-form">
                @csrf
                
                <div class="space-y-4">
                    <div>
                        <label for="kuesioner_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Pilih Kuesioner Tujuan</label>
                        <select id="kuesioner_id" name="kuesioner_id" required class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 dark:border-gray-600 dark:bg-gray-700 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                            <option value="">-- Pilih Kuesioner --</option>
                            @foreach($kuesioners as $k)
                                <option value="{{ $k->id }}" data-has-responses="{{ $k->responses_count > 0 ? 'true' : 'false' }}" data-responses-count="{{ $k->responses_count }}">
                                    [{{ $k->kode }}] {{ $k->judul }} ({{ $k->responses_count }} respon)
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Box Peringatan Overwrite Data Respon -->
                    <div id="warning-replace-container" class="hidden rounded-md bg-amber-50 dark:bg-amber-900/40 p-4 border border-amber-200 dark:border-amber-800 space-y-3">
                        <div class="flex items-start">
                            <div class="shrink-0 text-amber-600 dark:text-amber-400">
                                ⚠️
                            </div>
                            <div class="ml-3 text-sm text-amber-850 dark:text-amber-200">
                                <p class="font-bold text-amber-900 dark:text-amber-300">Kuesioner ini sudah memiliki data respon!</p>
                                <p class="mt-1 leading-relaxed">
                                    Kuesioner terpilih saat ini memiliki <strong id="warning-responses-count" class="underline font-black text-amber-950 dark:text-amber-100">0</strong> data respon.
                                    Melakukan import baru akan <strong class="text-red-600 dark:text-red-400 font-extrabold uppercase">menghapus permanen</strong> data respon lama kuesioner ini beserta seluruh detail jawabannya dan mereplace dengan data baru.
                                </p>
                            </div>
                        </div>
                        <div class="flex items-start pt-2 border-t border-amber-200/55">
                            <div class="flex items-center h-5">
                                <input id="agree_replace" name="agree_replace" type="checkbox" value="1" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded cursor-pointer">
                            </div>
                            <div class="ml-3 text-sm">
                                <label for="agree_replace" class="font-medium text-gray-800 dark:text-gray-200 cursor-pointer select-none">
                                    Saya mengerti dan menyetujui untuk menghapus seluruh data respon lama kuesioner ini.
                                </label>
                            </div>
                        </div>
                    </div>



                    <div>
                        <label for="file" class="block text-sm font-medium text-gray-700 dark:text-gray-300">File Excel (.xlsx)</label>
                        <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 dark:border-gray-600 border-dashed rounded-md relative group hover:border-indigo-500 transition-colors">
                            <div class="space-y-1 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400 group-hover:text-indigo-500 transition-colors" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <div class="flex text-sm text-gray-600 dark:text-gray-400 justify-center">
                                    <label for="file" class="relative cursor-pointer bg-white dark:bg-gray-800 rounded-md font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
                                        <span>Upload file</span>
                                        <input id="file" name="file" type="file" accept=".xlsx" class="sr-only" required onchange="document.getElementById('filename-display').textContent = this.files[0].name">
                                    </label>
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    Format XLSX max 20MB
                                </p>
                                <p id="filename-display" class="text-sm font-semibold text-indigo-600 dark:text-indigo-400 mt-2 truncate max-w-[200px] mx-auto"></p>
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <button type="submit" id="btn-import" disabled class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all disabled:opacity-70 disabled:cursor-not-allowed">
                        <span id="btn-text">Mulai Import</span>
                        <svg id="btn-spinner" class="animate-spin ml-2 h-5 w-5 text-white hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </button>
                </div>
            </form>
            
            <div class="text-center mt-4">
                <a href="{{ route('kuesioner.index') }}" class="text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-500">
                    &larr; Kembali ke Dashboard
                </a>
            </div>
        </div>
    </div>

    <script>
        function updateSubmitButtonState() {
            const select = document.getElementById('kuesioner_id');
            const selectedOption = select.options[select.selectedIndex];
            const btn = document.getElementById('btn-import');
            
            if (!selectedOption || !selectedOption.value) {
                btn.disabled = true;
                return;
            }

            const hasResponses = selectedOption.getAttribute('data-has-responses') === 'true';
            const checkbox = document.getElementById('agree_replace');

            if (hasResponses) {
                btn.disabled = !checkbox.checked;
            } else {
                btn.disabled = false;
            }
        }

        document.getElementById('kuesioner_id').addEventListener('change', function () {
            const selectedOption = this.options[this.selectedIndex];
            if (!selectedOption || !selectedOption.value) {
                document.getElementById('warning-replace-container').classList.add('hidden');
                document.getElementById('agree_replace').required = false;
                document.getElementById('agree_replace').checked = false;
                updateSubmitButtonState();
                return;
            }

            const hasResponses = selectedOption.getAttribute('data-has-responses') === 'true';
            const responsesCount = selectedOption.getAttribute('data-responses-count') || '0';
            
            const warningContainer = document.getElementById('warning-replace-container');
            const countSpan = document.getElementById('warning-responses-count');
            const checkbox = document.getElementById('agree_replace');
            
            if (hasResponses) {
                warningContainer.classList.remove('hidden');
                countSpan.textContent = responsesCount;
                checkbox.required = true;
            } else {
                warningContainer.classList.add('hidden');
                checkbox.required = false;
                checkbox.checked = false;
            }
            updateSubmitButtonState();
        });

        document.getElementById('agree_replace').addEventListener('change', updateSubmitButtonState);

        document.getElementById('import-form').addEventListener('submit', function() {
            const btn = document.getElementById('btn-import');
            const text = document.getElementById('btn-text');
            const spinner = document.getElementById('btn-spinner');
            
            btn.disabled = true;
            text.textContent = 'Memproses File...';
            spinner.classList.remove('hidden');
        });

        // Initialize state on load
        window.addEventListener('DOMContentLoaded', updateSubmitButtonState);
    </script>
</body>
</html>
