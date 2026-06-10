import { motion } from 'framer-motion';
import { router } from '@inertiajs/react';
import { useMemo, useState } from 'react';
import Button from '../atoms/Button';
import ThemeToggle from '../atoms/ThemeToggle';
import Badge from '../atoms/Badge';

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
    theme = 'dark',
    onToggleTheme,
}) {
    const [isSubmitting, setIsSubmitting] = useState(false);
    const [searchQuery, setSearchQuery] = useState('');
    const [visibleCount, setVisibleCount] = useState(30);
    const isLight = theme === 'light';

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
            className={`min-h-svh transition-colors duration-300 ${isLight ? 'bg-[#FFFDF6] text-black' : 'bg-gray-950 text-slate-200'}`}
        >
            <main className="mx-auto w-full max-w-5xl px-4 py-6 sm:px-6 sm:py-10 lg:px-8">
                <div className="mb-6 flex flex-col sm:flex-row gap-4 items-center justify-between">
                    <ThemeToggle theme={theme} onToggle={onToggleTheme} />
                    
                    <Button variant="secondary" className="w-full sm:w-auto" onClick={onBackToPeriod} theme={theme}>
                        ← Kembali ke Daftar Periode
                    </Button>
                </div>

                {/* Header seksi atas */}
                <div className="mb-8 flex flex-col items-center text-center sm:mb-10">
                    {/* Ikon centang */}
                    <motion.div
                        initial={{ scale: 0, opacity: 0 }}
                        animate={{ scale: 1, opacity: 1 }}
                        transition={{ type: 'spring', stiffness: 320, damping: 20, delay: 0.1 }}
                        className={[
                            'mb-5 flex h-20 w-20 items-center justify-center text-4xl sm:mb-6 sm:h-24 sm:w-24 sm:text-5xl transition-all',
                            isLight
                                ? completedDrafts.length === 0
                                    ? 'border-4 border-black bg-[#FFE082] shadow-[4px_4px_0px_0px_#000] rounded-none'
                                    : 'border-4 border-black bg-[#C8E6C9] shadow-[4px_4px_0px_0px_#000] rounded-none'
                                : completedDrafts.length === 0
                                    ? 'border-2 border-amber-500/40 bg-amber-500/10 shadow-[0_0_40px_rgba(245,158,11,0.15)] rounded-full'
                                    : 'border-2 border-emerald-400/40 bg-emerald-500/15 shadow-[0_0_40px_rgba(52,211,153,0.25)] rounded-full'
                        ].join(' ')}
                    >
                        {completedDrafts.length === 0 ? 'ℹ️' : '✅'}
                    </motion.div>

                    <motion.h1
                        initial={{ opacity: 0, y: 10 }}
                        animate={{ opacity: 1, y: 0 }}
                        transition={{ delay: 0.2, duration: 0.35 }}
                        className={`text-3xl font-black leading-tight sm:text-5xl ${isLight ? 'text-black' : 'text-slate-100'}`}
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
                        className={`mt-4 max-w-lg text-sm leading-6 sm:text-base ${isLight ? 'text-gray-800 font-bold' : 'text-slate-400'}`}
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
                        className={`w-full py-2 pl-10 pr-4 text-sm font-medium outline-none transition-all duration-200 ${
                            isLight
                                ? 'border-2 border-black bg-white text-black placeholder-gray-500 shadow-[2px_2px_0px_0px_#000] focus:border-[#FF6B00]'
                                : 'rounded-lg border border-slate-800 bg-slate-900/60 text-slate-200 placeholder-slate-500 focus:border-emerald-500/60 focus:ring-1 focus:ring-emerald-500/30'
                        }`}
                    />
                </div>

                {filteredEmployees.length === 0 ? (
                    <div className={`mb-8 flex flex-col items-center justify-center p-10 text-center transition-all ${
                        isLight
                            ? 'border-4 border-black bg-[#FFE082] shadow-[4px_4px_0px_0px_#000]'
                            : 'rounded-lg border border-slate-800 bg-slate-900/30'
                    }`}>
                        <span className="text-4xl mb-3">🔍</span>
                        <p className={`text-sm font-black uppercase tracking-wide ${isLight ? 'text-black' : 'text-slate-400'}`}>Tidak ada pegawai yang cocok</p>
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
                                        whileHover={isLight ? { scale: 1.015, translate: [2, 2] } : { scale: 1.025, y: -2 }}
                                        whileTap={{ scale: 0.97 }}
                                        transition={{ type: 'spring', stiffness: 380, damping: 22 }}
                                        className={[
                                            'group flex min-h-36 flex-col items-center justify-center gap-2 p-3 text-center outline-none transition-all duration-200 sm:min-h-40 sm:p-4',
                                            isLight
                                                ? 'rounded-none border-4 border-black'
                                                : 'rounded-lg border bg-gray-900 focus-visible:ring-2 focus-visible:ring-emerald-400 focus-visible:ring-offset-2 focus-visible:ring-offset-gray-950',
                                            isLight
                                                ? !hasDraft
                                                    ? 'bg-gray-100 shadow-[4px_4px_0px_0px_#000] hover:shadow-[1px_1px_0px_0px_#000] border-black/45 opacity-70'
                                                    : isAllSubmitted
                                                        ? 'bg-[#E3F2FD] shadow-[4px_4px_0px_0px_#000] hover:shadow-[1px_1px_0px_0px_#000]'
                                                        : 'bg-[#E8F5E9] shadow-[4px_4px_0px_0px_#000] hover:shadow-[1px_1px_0px_0px_#000]'
                                                : 'border-emerald-500/20 hover:border-emerald-400/50 hover:shadow-[0_0_20px_rgba(52,211,153,0.15)]',
                                            isLight ? 'focus-visible:ring-2 focus-visible:ring-black focus-visible:ring-offset-2 focus-visible:ring-offset-white' : ''
                                        ].join(' ')}
                                    >
                                        <span className="text-3xl sm:text-4xl">{employee.avatar}</span>

                                        <span className={`line-clamp-2 text-xs font-bold leading-snug sm:text-sm ${
                                            isLight ? 'text-black' : 'text-slate-200'
                                        }`}>
                                            {employee.name}
                                        </span>

                                        <Badge
                                            variant={!hasDraft ? 'default' : isAllSubmitted ? 'info' : 'success'}
                                            theme={theme}
                                        >
                                            {!hasDraft ? 'Kosong' : isAllSubmitted ? 'Terkirim' : 'Draft'} {hasDraft && '✓'}
                                        </Badge>

                                        <span className={`text-[10px] font-black uppercase tracking-wide opacity-100 transition-opacity sm:opacity-0 sm:group-hover:opacity-100 ${
                                            isLight ? 'text-[#FF6B00]' : 'text-cyan-400'
                                        }`}>
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
                                    className={`px-6 py-2.5 font-bold ${
                                        isLight ? 'text-black' : 'text-emerald-300 hover:text-emerald-100 hover:bg-emerald-950/20'
                                    }`}
                                    theme={theme}
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
                        whileHover={(isSubmitting || isAllSubmitted) ? undefined : isLight ? { scale: 1.015, translate: [2, 2] } : { scale: 1.02, y: -2 }}
                        whileTap={(isSubmitting || isAllSubmitted) ? undefined : { scale: 0.98 }}
                        transition={{ type: 'spring', stiffness: 380, damping: 22 }}
                        className={[
                            'w-full max-w-sm px-6 py-4 text-base font-black leading-tight outline-none transition-all duration-200 sm:px-8',
                            isLight
                                ? 'rounded-none border-4 border-black text-black shadow-[6px_6px_0px_0px_#000] hover:shadow-[2px_2px_0px_0px_#000] bg-[#81C784] hover:bg-[#66BB6A]'
                                : 'rounded-lg border border-emerald-400/40 bg-gradient-to-r from-emerald-600 to-teal-600 text-white shadow-[0_0_30px_rgba(52,211,153,0.3)] focus-visible:ring-2 focus-visible:ring-emerald-400 focus-visible:ring-offset-2 focus-visible:ring-offset-gray-950',
                            isLight ? 'focus-visible:ring-2 focus-visible:ring-black focus-visible:ring-offset-2 focus-visible:ring-offset-white' : '',
                            (isSubmitting || isAllSubmitted)
                                ? 'cursor-not-allowed opacity-50'
                                : isLight ? '' : 'hover:from-emerald-500 hover:to-teal-500 hover:shadow-[0_0_40px_rgba(52,211,153,0.45)]',
                        ].join(' ')}
                    >
                        {isAllSubmitted
                            ? 'Semua Penilaian Sudah Terkirim'
                            : isSubmitting
                                ? 'Mengirim Penilaian...'
                                : 'Kirim Semua Penilaian'}
                    </motion.button>

                    <p className={`text-xs font-semibold ${isLight ? 'text-gray-600' : 'text-slate-600'}`}>
                        Tindakan ini tidak dapat dibatalkan
                    </p>
                </motion.div>
            </main>
        </motion.div>
    );
}
