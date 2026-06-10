// Data dummy untuk periode, pegawai, dan pertanyaan Kuesioner 360.
export const periods = [
    { id: 1, code: 'TW1', title: 'Kuesioner 360 TW1', month: 'Januari 2025', status: 'completed', description: 'Penilaian periode Januari 2025' },
    { id: 2, code: 'TW2', title: 'Kuesioner 360 TW2', month: 'Februari 2025', status: 'completed', description: 'Penilaian periode Februari 2025' },
    { id: 3, code: 'TW3', title: 'Kuesioner 360 TW3', month: 'Maret 2025', status: 'completed', description: 'Penilaian periode Maret 2025' },
    { id: 4, code: 'TW4', title: 'Kuesioner 360 TW4', month: 'April 2025', status: 'completed', description: 'Penilaian periode April 2025' },
    { id: 5, code: 'TW5', title: 'Kuesioner 360 TW5', month: 'Mei 2025', status: 'active', description: 'Penilaian periode Mei 2025 - Sedang Berlangsung' },
    { id: 6, code: 'TW6', title: 'Kuesioner 360 TW6', month: 'Juni 2025', status: 'unavailable', description: 'Belum tersedia' },
    { id: 7, code: 'TW7', title: 'Kuesioner 360 TW7', month: 'Juli 2025', status: 'unavailable', description: 'Belum tersedia' },
    { id: 8, code: 'TW8', title: 'Kuesioner 360 TW8', month: 'Agustus 2025', status: 'unavailable', description: 'Belum tersedia' },
    { id: 9, code: 'TW9', title: 'Kuesioner 360 TW9', month: 'September 2025', status: 'unavailable', description: 'Belum tersedia' },
    { id: 10, code: 'TW10', title: 'Kuesioner 360 TW10', month: 'Oktober 2025', status: 'unavailable', description: 'Belum tersedia' },
    { id: 11, code: 'TW11', title: 'Kuesioner 360 TW11', month: 'November 2025', status: 'unavailable', description: 'Belum tersedia' },
    { id: 12, code: 'TW12', title: 'Kuesioner 360 TW12', month: 'Desember 2025', status: 'unavailable', description: 'Belum tersedia' },
];

export const employees = [
    { id: 1, name: 'Ahmad Fauzi', position: 'Statistisi Ahli Muda', avatar: '👨‍💼', department: 'Statistik Sosial' },
    { id: 2, name: 'Siti Rahayu', position: 'Pranata Komputer', avatar: '👩‍💻', department: 'IPDS' },
    { id: 3, name: 'Budi Santoso', position: 'Statistisi Ahli Pertama', avatar: '👨‍🔬', department: 'Statistik Produksi' },
    { id: 4, name: 'Dewi Lestari', position: 'Pengolah Data', avatar: '👩‍📊', department: 'Statistik Distribusi' },
    { id: 5, name: 'Rudi Hartono', position: 'Koordinator SPBE', avatar: '👨‍💼', department: 'Subbagian Umum' },
];

export const questions = [
    {
        id: 1,
        text: 'Pegawai ini selalu hadir tepat waktu dan menunjukkan kedisiplinan tinggi dalam setiap kegiatan kerja maupun rapat koordinasi tim, serta tidak pernah meninggalkan tempat kerja tanpa izin yang jelas kepada atasan langsung.',
    },
    {
        id: 2,
        text: 'Pegawai ini mampu bekerja sama secara aktif dengan rekan lintas fungsi, bersedia membantu penyelesaian pekerjaan tim ketika dibutuhkan, dan menjaga suasana kerja yang saling menghargai meskipun berada dalam tekanan target kegiatan statistik.',
    },
    {
        id: 3,
        text: 'Pegawai ini menunjukkan kemampuan berinovasi melalui usulan perbaikan proses kerja, pemanfaatan teknologi, atau penyederhanaan alur administrasi yang membuat pelaksanaan kegiatan statistik menjadi lebih efektif, cepat, dan mudah dipantau.',
    },
    {
        id: 4,
        text: 'Pegawai ini berkomunikasi dengan jelas, santun, dan tepat sasaran ketika menyampaikan informasi pekerjaan, baik kepada atasan, rekan kerja, petugas lapangan, maupun mitra eksternal yang terlibat dalam kegiatan BPS.',
    },
    {
        id: 5,
        text: 'Pegawai ini menjaga integritas dalam menjalankan tugas, mematuhi aturan kerahasiaan data, menghindari konflik kepentingan, dan dapat dipercaya dalam mengelola informasi maupun dokumen penting milik instansi.',
    },
    {
        id: 6,
        text: 'Pegawai ini menghasilkan kinerja yang konsisten, menyelesaikan tugas sesuai tenggat waktu, memperhatikan ketelitian output, dan bertanggung jawab terhadap kualitas pekerjaan sejak tahap perencanaan hingga pelaporan.',
    },
    {
        id: 7,
        text: 'Pegawai ini memberikan pelayanan internal maupun eksternal dengan responsif, ramah, dan solutif, termasuk ketika menghadapi pertanyaan, keluhan, atau kebutuhan data dari pengguna layanan statistik.',
    },
    {
        id: 8,
        text: 'Pegawai ini memiliki kemauan kuat untuk mengembangkan diri melalui pembelajaran mandiri, pelatihan, diskusi teknis, serta terbuka terhadap masukan agar kompetensinya terus meningkat sesuai kebutuhan organisasi.',
    },
];
