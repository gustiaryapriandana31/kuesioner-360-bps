// Molecule modal lanjut penilaian ke pegawai berikutnya setelah submit berhasil.
import { AnimatePresence, motion } from 'framer-motion';
import Button from '../atoms/Button';

export default function NextEmployeeModal({
    show,
    employee,
    remainingCount = 0,
    onContinue,
    onBackToList,
}) {
    return (
        <AnimatePresence>
            {show && employee && (
                <motion.div
                    initial={{ opacity: 0 }}
                    animate={{ opacity: 1 }}
                    exit={{ opacity: 0 }}
                    transition={{ duration: 0.22, ease: 'easeOut' }}
                    className="fixed inset-0 z-50 flex items-center justify-center bg-gray-950/72 px-4 py-6 backdrop-blur-md sm:py-8"
                >
                    <motion.section
                        initial={{ opacity: 0, y: 28, scale: 0.95 }}
                        animate={{ opacity: 1, y: 0, scale: 1 }}
                        exit={{ opacity: 0, y: 18, scale: 0.96 }}
                        transition={{ duration: 0.28, ease: [0.22, 1, 0.36, 1] }}
                        className="w-full max-w-lg overflow-hidden rounded-lg border border-purple-500/30 bg-gray-900 shadow-2xl shadow-black/40"
                    >
                        <div className="bg-gradient-to-r from-purple-600/25 via-cyan-500/18 to-emerald-500/18 px-4 py-5 sm:px-6 sm:py-6">
                            <p className="text-xs font-bold uppercase tracking-wide text-purple-200 sm:text-sm">
                                Penilaian berikutnya
                            </p>
                            <h2 className="mt-2 text-xl font-black leading-tight text-slate-100 sm:text-2xl">
                                Lanjut nilai {employee.name}?
                            </h2>
                            <p className="mt-3 text-sm leading-6 text-slate-400">
                                Masih ada {remainingCount} pegawai yang belum dinilai pada periode ini. Anda bisa lanjut sekarang atau kembali ke daftar pegawai dulu.
                            </p>
                        </div>

                        <div className="p-4 sm:p-6">
                            <div className="flex items-center gap-3 rounded-lg border border-white/10 bg-gray-950 p-3 sm:gap-4 sm:p-4">
                                <div className="flex h-14 w-14 shrink-0 items-center justify-center rounded-lg border border-cyan-400/20 bg-cyan-500/10 text-3xl sm:h-16 sm:w-16 sm:text-4xl">
                                    {employee.avatar}
                                </div>
                                <div className="min-w-0">
                                    <p className="truncate text-base font-black text-slate-100 sm:text-lg">
                                        {employee.name}
                                    </p>
                                    <p className="mt-1 text-sm font-semibold text-purple-200">
                                        {employee.position}
                                    </p>
                                    <p className="mt-1 text-sm text-slate-500">
                                        {employee.department}
                                    </p>
                                </div>
                            </div>

                            <div className="mt-5 flex flex-col gap-3 sm:mt-6 sm:flex-row sm:justify-end">
                                <Button variant="secondary" className="w-full sm:w-auto" onClick={onBackToList}>
                                    Kembali ke Daftar Pegawai
                                </Button>
                                <Button variant="primary" className="w-full sm:w-auto" onClick={onContinue}>
                                    Nilai Pegawai Ini
                                </Button>
                            </div>
                        </div>
                    </motion.section>
                </motion.div>
            )}
        </AnimatePresence>
    );
}
