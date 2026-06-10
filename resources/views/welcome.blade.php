<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Selamat Datang - Kuesioner 360 BPS</title>

        <!-- Fonts (Plus Jakarta Sans for Bold Neobrutalism) -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">

        <!-- Styles / Scripts -->
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.jsx'])
        @else
            <!-- Fallback Tailwind CSS in case build doesn't exist -->
            <script src="https://cdn.tailwindcss.com"></script>
        @endif

        <style>
            body {
                font-family: 'Plus Jakarta Sans', sans-serif;
                background-color: #FFFDF6;
            }
            .neobrutalism-shadow {
                box-shadow: 6px 6px 0px 0px #000000;
            }
            .neobrutalism-shadow-sm {
                box-shadow: 3px 3px 0px 0px #000000;
            }
            .neobrutalism-shadow-lg {
                box-shadow: 10px 10px 0px 0px #000000;
            }
            .neobrutalism-btn:hover {
                box-shadow: 2px 2px 0px 0px #000000;
                transform: translate(4px, 4px);
            }
            .neobrutalism-btn-lg:hover {
                box-shadow: 2px 2px 0px 0px #000000;
                transform: translate(6px, 6px);
            }
        </style>
    </head>
    <body class="text-black antialiased min-h-screen flex flex-col justify-between selection:bg-[#FF6B00] selection:text-white">
        
        <!-- Header / Navigation -->
        <header class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex flex-col sm:flex-row justify-between items-center gap-4 bg-white border-4 border-black p-4 neobrutalism-shadow">
                
                <!-- Logo -->
                <a href="/" class="flex items-center gap-2">
                    <div class="bg-[#FF6B00] text-white font-extrabold px-3 py-1.5 border-2 border-black rotate-[-2deg] neobrutalism-shadow-sm text-lg sm:text-xl tracking-tight">
                        360°
                    </div>
                    <span class="font-extrabold text-xl sm:text-2xl tracking-tight text-black">
                        KUESIONER BPS
                    </span>
                </a>

                <!-- Navigation Auth Buttons -->
                <nav class="flex items-center gap-3">
                    @if (Route::has('login'))
                        @auth
                            <a
                                href="{{ route('kuesioner.index') }}"
                                class="inline-block bg-[#FFCA28] text-black font-extrabold border-2 border-black px-5 py-2.5 text-sm neobrutalism-shadow-sm neobrutalism-btn transition-all"
                            >
                                Halaman Dashboard
                            </a>
                        @else
                            <a
                                href="{{ route('login') }}"
                                class="inline-block bg-[#FF6B00] text-black font-extrabold border-2 border-black px-6 py-2.5 text-sm neobrutalism-shadow-sm neobrutalism-btn transition-all"
                            >
                                Masuk Sistem
                            </a>
                        @endauth
                    @endif
                </nav>

            </div>
        </header>

        <!-- Main Hero Content -->
        <main class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-16 flex-grow">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
                
                <!-- Hero Left Column -->
                <div class="lg:col-span-7 space-y-6">
                    <div class="bg-[#4CAF50] text-black text-xs font-black uppercase tracking-widest px-3 py-1 inline-block border-2 border-black neobrutalism-shadow-sm rotate-[-1deg]">
                        Platform Evaluasi Kinerja Pegawai
                    </div>
                    
                    <h1 class="text-4xl sm:text-6xl font-black leading-none tracking-tight text-black">
                        Beri Penilaian <span class="bg-[#FFCA28] px-2 border-2 border-black inline-block rotate-[1deg] my-1">Objektif</span>, Tumbuh Bersama!
                    </h1>
                    
                    <p class="text-lg sm:text-xl font-bold text-gray-800 leading-relaxed border-l-8 border-[#FF6B00] pl-4">
                        Sistem Kuesioner 360 BPS Ogan Ilir adalah wadah evaluasi terpadu untuk saling memberikan penilaian umpan balik antar pegawai guna mendorong peningkatan kualitas kerja secara objektif, transparan, dan berkelanjutan.
                    </p>

                    <div class="flex flex-col sm:flex-row gap-4 pt-4">
                        @auth
                            <a
                                href="{{ route('kuesioner.index') }}"
                                class="bg-[#FF6B00] text-black border-4 border-black text-xl font-extrabold px-8 py-4 neobrutalism-shadow-lg neobrutalism-btn-lg transition-all text-center"
                            >
                                Ke Dashboard Saya &rarr;
                            </a>
                        @else
                            <a
                                href="{{ route('login') }}"
                                class="bg-[#FF6B00] text-black border-4 border-black text-xl font-extrabold px-8 py-4 neobrutalism-shadow-lg neobrutalism-btn-lg transition-all text-center"
                            >
                                Mulai Beri Penilaian &rarr;
                            </a>
                        @endauth
                    </div>
                </div>

                <!-- Hero Right Column (Brutalist Mockup Card) -->
                <div class="lg:col-span-5 relative">
                    <div class="bg-white border-4 border-black p-6 sm:p-8 neobrutalism-shadow lg:rotate-[1.5deg] space-y-6">
                        
                        <div class="border-b-4 border-black pb-4 flex justify-between items-center">
                            <div>
                                <h3 class="font-extrabold text-xl text-black">Aspek Kompetensi</h3>
                                <p class="text-sm font-semibold text-gray-600">Core Values ASN BerAKHLAK</p>
                            </div>
                            <span class="bg-[#FFCA28] border-2 border-black font-extrabold text-xs px-2 py-1 neobrutalism-shadow-sm">
                                360° EVAL
                            </span>
                        </div>

                        <!-- Core Values Mockup list -->
                        <div class="space-y-3">
                            <div class="flex items-center justify-between border-2 border-black p-3 bg-[#E8F5E9] font-bold text-sm">
                                <span>Berorientasi Pelayanan</span>
                                <span class="bg-black text-white px-2 py-0.5 text-xs font-black">AKTIF</span>
                            </div>
                            <div class="flex items-center justify-between border-2 border-black p-3 bg-[#FFF3E0] font-bold text-sm">
                                <span>Akuntabel & Kompeten</span>
                                <span class="bg-black text-white px-2 py-0.5 text-xs font-black">AKTIF</span>
                            </div>
                            <div class="flex items-center justify-between border-2 border-black p-3 bg-[#E3F2FD] font-bold text-sm">
                                <span>Harmonis & Loyal</span>
                                <span class="bg-black text-white px-2 py-0.5 text-xs font-black">AKTIF</span>
                            </div>
                            <div class="flex items-center justify-between border-2 border-black p-3 bg-[#F3E5F5] font-bold text-sm">
                                <span>Adaptif & Kolaboratif</span>
                                <span class="bg-black text-white px-2 py-0.5 text-xs font-black">AKTIF</span>
                            </div>
                        </div>

                        <!-- Decription badge -->
                        <div class="bg-[#FFCA28] border-2 border-black p-3 text-xs font-bold text-black rotate-[-1deg] text-center">
                            Setiap pegawai berhak memberikan & menerima umpan balik untuk kemajuan bersama!
                        </div>

                    </div>

                    <!-- Decorative shapes in background -->
                    <div class="absolute -top-6 -left-6 w-12 h-12 bg-[#FF6B00] border-2 border-black -z-10 rotate-[15deg]"></div>
                    <div class="absolute -bottom-6 -right-6 w-16 h-16 bg-[#4CAF50] border-2 border-black -z-10 rounded-full"></div>
                </div>

            </div>
        </main>

        <!-- Information Grid Section -->
        <section class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="border-t-4 border-black pt-12">
                <h2 class="text-3xl sm:text-4xl font-black text-center mb-10 tracking-tight">
                    Mengapa Menggunakan Evaluasi 360°?
                </h2>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    
                    <!-- Card 1 -->
                    <div class="bg-white border-4 border-black p-6 neobrutalism-shadow hover:translate-x-1 hover:translate-y-1 hover:shadow-none transition-all">
                        <div class="bg-[#FF6B00] text-black w-12 h-12 flex items-center justify-center font-extrabold text-xl border-2 border-black mb-4 neobrutalism-shadow-sm">
                            1
                        </div>
                        <h3 class="font-extrabold text-xl mb-2">Penilaian Komprehensif</h3>
                        <p class="font-semibold text-gray-700 text-sm leading-relaxed">
                            Mendapatkan umpan balik multi-arah dari rekan sejawat, atasan, dan bawahan, memberikan gambaran kompetensi yang lebih utuh dan objektif.
                        </p>
                    </div>

                    <!-- Card 2 -->
                    <div class="bg-white border-4 border-black p-6 neobrutalism-shadow hover:translate-x-1 hover:translate-y-1 hover:shadow-none transition-all">
                        <div class="bg-[#FFCA28] text-black w-12 h-12 flex items-center justify-center font-extrabold text-xl border-2 border-black mb-4 neobrutalism-shadow-sm">
                            2
                        </div>
                        <h3 class="font-extrabold text-xl mb-2">Pengembangan Kompetensi</h3>
                        <p class="font-semibold text-gray-700 text-sm leading-relaxed">
                            Membantu mengidentifikasi area kekuatan dan aspek yang memerlukan perbaikan (gap kompetensi) guna merancang program pengembangan karir yang lebih terarah.
                        </p>
                    </div>

                    <!-- Card 3 -->
                    <div class="bg-white border-4 border-black p-6 neobrutalism-shadow hover:translate-x-1 hover:translate-y-1 hover:shadow-none transition-all">
                        <div class="bg-[#4CAF50] text-black w-12 h-12 flex items-center justify-center font-extrabold text-xl border-2 border-black mb-4 neobrutalism-shadow-sm">
                            3
                        </div>
                        <h3 class="font-extrabold text-xl mb-2">Budaya Kerja Transparan</h3>
                        <p class="font-semibold text-gray-700 text-sm leading-relaxed">
                            Mendorong komunikasi dua arah yang sehat di lingkungan kerja, membangun rasa saling percaya, serta menanamkan Core Values ASN BerAKHLAK.
                        </p>
                    </div>

                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="border-4 border-black bg-black text-white p-6 sm:p-8 neobrutalism-shadow flex flex-col md:flex-row justify-between items-center gap-4 text-center md:text-left">
                <div>
                    <h4 class="font-black text-lg sm:text-xl text-[#FFCA28]">KUESIONER 360 BPS OGAN ILIR</h4>
                    <p class="text-xs font-semibold text-gray-400 mt-1">Inovasi untuk Evaluasi & Peningkatan Kinerja Pegawai BPS yang BerAKHLAK.</p>
                </div>
                <div class="text-xs font-bold text-white border-2 border-white px-3 py-1.5 rotate-[-1deg] bg-[#FF6B00]">
                    © 2026 BPS Ogan Ilir
                </div>
            </div>
        </footer>

    </body>
</html>
