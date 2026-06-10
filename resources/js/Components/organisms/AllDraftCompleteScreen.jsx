// Organism layar full-page yang tampil ketika semua pegawai sudah dinilai (status draft).
// Menampilkan grid mini pegawai, tombol kirim semua, dan bisa masuk preview mode.
import { motion } from 'framer-motion';
import { router } from '@inertiajs/react';
import { useMemo, useState } from 'react';
import Button from '../atoms/Button';

const containerVariants = {
    hidden: { opacity: 0 },
    visible: {
        opacity: 1,
        transition: { staggerChildren: 0.02 },
    },
};

const itemVariants = {
    hidden: { opacity: 0, y: 12, scale: 0.96 },
    visible: {
        opacity: 1,
        y: 0,
        scale: 1,
        transition: { duration: 0.3, ease: [0.22, 1, 0.36, 1] },
    },
};

export default function AllDraftCompleteScreen({
    employees,
    kuesioner,
    isAllSubmitted = false,
    onSelectEmployee,
    onBackToPeriod,
    completedDrafts = [],
}) {
    const [isSubmitting, setIsSubmitting] = useState(false);
    const [searchQuery, setSearchQuery] = useState('');
    const [visibleCount, setVisibleCount] = useState(30);

    const handleSubmitAll = () => {
        if (isSubmitting || isAllSubmitted) return;

        setIsSubmitting(true);
        router.post(
            `/kuesioner/${kuesioner.id}/submit-all`,
            {},
            {
                preserveScroll: true,
                onError: (errors) => {
                    console.error('Submit all error:', errors);
                },
                onFinish: () => {
                    setIsSubmitting(false);
                },
            }
        );
    };

    const filteredEmployees = useMemo(() => {
        const query = searchQuery.toLowerCase().trim();
        if (!query) return employees;
        return employees.filter((employee) => {
            return (
                (employee.name && employee.name.toLowerCase().includes(query)) ||
                (employee.position && employee.position.toLowerCase().includes(query)) ||
                (employee.department && employee.department.toLowerCase().includes(query)) ||
                (employee.jabatan && employee.jabatan.toLowerCase().includes(query)) ||
                (employee.departemen && employee.departemen.toLowerCase().includes(query))
            );
        });
    }, [employees, searchQuery]);

    const slicedEmployees = useMemo(() => {
        return filteredEmployees.slice(0, visibleCount);
    }, [filteredEmployees, visibleCount]);

    return (
        <motion.div
            initial={{ opacity: 0, scale: 0.98, y: 12 }}
            animate={{ opacity: 1, scale: 1, y: 0 }}
            transition={{ duration: 0.35, ease: [0.22, 1, 0.36, 1] }}
            className="min-h-svh bg-gray-950 text-slate-200"
        >
            <main className="mx-auto w-full max-w-5xl px-4 py-6 sm:px-6 sm:py-10 lg:px-8">
                <div className="mb-6 flex justify-stretch sm:justify-end">
                    <Button variant="secondary" className="w-full sm:w-auto" onClick={onBackToPeriod}>
                        ← Kembali ke Daftar Periode
                    </Button>
                </div>

                {/* Header seksi atas */}
                <div className="mb-8 flex flex-col items-center text-center sm:mb-10">
                    {/* Ikon centang animasi spring */}
                    <motion.div
                        initial={{ scale: 0, opacity: 0 }}
                        animate={{ scale: 1, opacity: 1 }}
                        transition={{ type: 'spring', stiffness: 320, damping: 20, delay: 0.1 }}
                        className={[
                            'mb-5 flex h-20 w-20 items-center justify-center rounded-full border-2 text-4xl sm:mb-6 sm:h-24 sm:w-24 sm:text-5xl',
                            completedDrafts.length === 0
                                ? 'border-amber-500/40 bg-amber-500/10 shadow-[0_0_40px_rgba(245,158,11,0.15)]'
                                : 'border-emerald-400/40 bg-emerald-500/15 shadow-[0_0_40px_rgba(52,211,153,0.25)]'
                        ].join(' ')}
                    >
                        {completedDrafts.length === 0 ? 'ℹ️' : '✅'}
                    </motion.div>

                    <motion.h1
                        initial={{ opacity: 0, y: 10 }}
                        animate={{ opacity: 1, y: 0 }}
                        transition={{ delay: 0.2, duration: 0.35 }}
                        className="text-3xl font-black leading-tight text-slate-100 sm:text-5xl"
                    >
                        {completedDrafts.length === 0
                            ? 'Kuesioner Ditutup'
                            : isAllSubmitted
                                ? 'Semua Penilaian Terkirim!'
                                : 'Semua Penilaian Tersimpan!'}
                    </motion.h1>

                    <motion.p
                        initial={{ opacity: 0, y: 8 }}
                        animate={{ opacity: 1, y: 0 }}
                        transition={{ delay: 0.3, duration: 0.35 }}
                        className="mt-4 max-w-lg text-sm leading-6 text-slate-400 sm:text-base"
                    >
                        {completedDrafts.length === 0
                            ? 'Periode kuesioner ini telah ditutup dan Anda tidak mengisi penilaian.'
                            : isAllSubmitted
                                ? 'Seluruh penilaian pada periode ini sudah dikirim final.'
                                : 'Periksa kembali sebelum mengirim secara final. Setelah dikirim, penilaian tidak dapat diubah.'}
                    </motion.p>
                </div>

                {/* Search Input */}
                <div className="mb-6 max-w-md mx-auto relative">
                    <span className="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-500">
                        🔍
                    </span>
                    <input
                        type="text"
                        placeholder="Cari pegawai berdasarkan nama, jabatan..."
                        value={searchQuery}
                        onChange={(e) => {
                            setSearchQuery(e.target.value);
                            setVisibleCount(30);
                        }}
                        className="w-full rounded-lg border border-slate-800 bg-slate-900/60 py-2 pl-10 pr-4 text-sm font-medium text-slate-200 placeholder-slate-500 outline-none transition-all duration-200 focus:border-emerald-500/60 focus:ring-1 focus:ring-emerald-500/30"
                    />
                </div>

                {filteredEmployees.length === 0 ? (
                    <div className="mb-8 flex flex-col items-center justify-center rounded-lg border border-slate-800 bg-slate-900/30 p-10 text-center">
                        <span className="text-4xl mb-3">🔍</span>
                        <p className="text-sm font-bold text-slate-400 uppercase tracking-wide">Tidak ada pegawai yang cocok</p>
                    </div>
                ) : (
                    <>
                        {/* Grid mini pegawai — klik untuk preview mode */}
                        <motion.div
                            variants={containerVariants}
                            initial="hidden"
                            animate="visible"
                            className="mb-8 grid grid-cols-2 gap-3 sm:mb-10 sm:grid-cols-3 sm:gap-4 md:grid-cols-4 lg:grid-cols-5"
                        >
                            {slicedEmployees.map((employee) => {
                                const hasDraft = completedDrafts.includes(employee.id);

                                return (
                                    <motion.button
                                        key={employee.id}
                                        type="button"
                                        variants={itemVariants}
                                        onClick={() => onSelectEmployee?.(employee)}
                                        whileHover={{ scale: 1.025, y: -2 }}
                                        whileTap={{ scale: 0.97 }}
                                        transition={{ type: 'spring', stiffness: 380, damping: 22 }}
                                        className="group flex min-h-36 flex-col items-center justify-center gap-2 rounded-lg border border-emerald-500/20 bg-gray-900 p-3 text-center outline-none transition-all duration-200 hover:border-emerald-400/50 hover:shadow-[0_0_20px_rgba(52,211,153,0.15)] focus-visible:ring-2 focus-visible:ring-emerald-400 focus-visible:ring-offset-2 focus-visible:ring-offset-gray-950 sm:min-h-40 sm:p-4"
                                    >
                                        <span className="text-3xl sm:text-4xl">{employee.avatar}</span>

                                        <span className="line-clamp-2 text-xs font-bold leading-snug text-slate-200 sm:text-sm">
                                            {employee.name}
                                        </span>

                                        <span className={[
                                            'inline-flex items-center gap-1 rounded-full border px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide',
                                            !hasDraft
                                                ? 'border-slate-600/40 bg-slate-800/20 text-slate-400'
                                                : isAllSubmitted
                                                    ? 'border-cyan-500/30 bg-cyan-500/10 text-cyan-300'
                                                    : 'border-emerald-500/30 bg-emerald-500/10 text-emerald-300',
                                        ].join(' ')}>
                                            {!hasDraft ? 'Kosong' : isAllSubmitted ? 'Terkirim' : 'Draft'} {hasDraft && '✓'}
                                        </span>

                                        <span className="text-[10px] font-semibold text-cyan-400 opacity-100 transition-opacity sm:opacity-0 sm:group-hover:opacity-100">
                                            Pratinjau →
                                        </span>
                                    </motion.button>
                                );
                            })}
                        </motion.div>

                        {filteredEmployees.length > visibleCount && (
                            <div className="mb-8 flex justify-center">
                                <Button
                                    variant="secondary"
                                    onClick={() => setVisibleCount((prev) => prev + 30)}
                                    className="px-6 py-2.5 font-bold text-emerald-300 hover:text-emerald-100 hover:bg-emerald-950/20 border-emerald-500/20"
                                >
                                    Muat Lebih Banyak ({filteredEmployees.length - visibleCount} lagi)
                                </Button>
                            </div>
                        )}
                    </>
                )}

                {/* Tombol kirim semua */}
                <motion.div
                    initial={{ opacity: 0, y: 16 }}
                    animate={{ opacity: 1, y: 0 }}
                    transition={{ delay: 0.4, duration: 0.35 }}
                    className="flex flex-col items-center gap-3"
                >
                    <motion.button
                        type="button"
                        onClick={handleSubmitAll}
                        disabled={isSubmitting || isAllSubmitted}
                        whileHover={(isSubmitting || isAllSubmitted) ? undefined : { scale: 1.02, y: -2 }}
                        whileTap={(isSubmitting || isAllSubmitted) ? undefined : { scale: 0.98 }}
                        transition={{ type: 'spring', stiffness: 380, damping: 22 }}
                        className={[
                            'w-full max-w-sm rounded-lg border border-emerald-400/40 bg-gradient-to-r from-emerald-600 to-teal-600 px-6 py-4 text-base font-black leading-tight text-white shadow-[0_0_30px_rgba(52,211,153,0.3)] outline-none transition-all duration-200 focus-visible:ring-2 focus-visible:ring-emerald-400 focus-visible:ring-offset-2 focus-visible:ring-offset-gray-950 sm:px-8',
                            (isSubmitting || isAllSubmitted)
                                ? 'cursor-not-allowed opacity-70'
                                : 'hover:from-emerald-500 hover:to-teal-500 hover:shadow-[0_0_40px_rgba(52,211,153,0.45)]',
                        ].join(' ')}
                    >
                        {isAllSubmitted
                            ? 'Semua Penilaian Sudah Terkirim'
                            : isSubmitting
                                ? 'Mengirim Penilaian...'
                                : 'Kirim Semua Penilaian'}
                    </motion.button>

                    <p className="text-xs font-semibold text-slate-600">
                        Tindakan ini tidak dapat dibatalkan
                    </p>
                </motion.div>
            </main>
        </motion.div>
    );
}
