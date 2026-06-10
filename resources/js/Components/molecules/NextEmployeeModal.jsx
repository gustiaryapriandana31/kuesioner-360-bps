// Molecule modal lanjut penilaian ke pegawai berikutnya setelah submit berhasil.
import { AnimatePresence, motion } from 'framer-motion';
import Button from '../atoms/Button';

export default function NextEmployeeModal({
    show,
    employee,
    remainingCount = 0,
    onContinue,
    onBackToList,
    theme = 'dark',
}) {
    const isLight = theme === 'light';

    return (
        <AnimatePresence>
            {show && employee && (
                <motion.div
                    initial={{ opacity: 0 }}
                    animate={{ opacity: 1 }}
                    exit={{ opacity: 0 }}
                    transition={{ duration: 0.22, ease: 'easeOut' }}
                    className="fixed inset-0 z-50 flex items-center justify-center bg-black/60 px-4 py-6 backdrop-blur-md sm:py-8"
                >
                    <motion.section
                        initial={{ opacity: 0, y: 28, scale: 0.95 }}
                        animate={{ opacity: 1, y: 0, scale: 1 }}
                        exit={{ opacity: 0, y: 18, scale: 0.96 }}
                        transition={{ duration: 0.28, ease: [0.22, 1, 0.36, 1] }}
                        className={[
                            'w-full max-w-lg overflow-hidden transition-all duration-300',
                            isLight
                                ? 'border-4 border-black bg-white shadow-[8px_8px_0px_0px_#000] rounded-none text-black'
                                : 'rounded-lg border border-purple-500/30 bg-gray-900 shadow-2xl shadow-black/40'
                        ].join(' ')}
                    >
                        <div className={[
                            'px-4 py-5 sm:px-6 sm:py-6 transition-colors duration-300',
                            isLight
                                ? 'bg-[#FFE082] border-b-2 border-black'
                                : 'bg-gradient-to-r from-purple-600/25 via-cyan-500/18 to-emerald-500/18'
                        ].join(' ')}>
                            <p className={[
                                'text-xs font-bold uppercase tracking-wide',
                                isLight ? 'text-black/85' : 'text-purple-200'
                            ].join(' ')}>
                                Penilaian berikutnya
                            </p>
                            <h2 className={[
                                'mt-2 text-xl font-black leading-tight sm:text-2xl',
                                isLight ? 'text-black' : 'text-slate-100'
                            ].join(' ')}>
                                Lanjut nilai {employee.name}?
                            </h2>
                            <p className={[
                                'mt-3 text-sm leading-6',
                                isLight ? 'text-gray-800' : 'text-slate-400'
                            ].join(' ')}>
                                Masih ada {remainingCount} pegawai yang belum dinilai pada periode ini. Anda bisa lanjut sekarang atau kembali ke daftar pegawai dulu.
                            </p>
                        </div>

                        <div className="p-4 sm:p-6 bg-white transition-colors duration-300">
                            <div className={[
                                'flex items-center gap-3 p-3 sm:gap-4 sm:p-4 transition-all duration-300',
                                isLight
                                    ? 'border-2 border-black bg-white shadow-[3px_3px_0px_0px_#000] rounded-md'
                                    : 'rounded-lg border border-white/10 bg-gray-955/40 bg-gray-950'
                            ].join(' ')}>
                                <div className={[
                                    'flex h-14 w-14 shrink-0 items-center justify-center text-3xl sm:h-16 sm:w-16 sm:text-4xl transition-all duration-300',
                                    isLight
                                        ? 'border-2 border-black bg-white shadow-[2px_2px_0px_0px_#000] rounded-md'
                                        : 'rounded-lg border border-cyan-400/20 bg-cyan-500/10'
                                ].join(' ')}>
                                    {employee.avatar}
                                </div>
                                <div className="min-w-0">
                                    <p className={[
                                        'truncate text-base font-black sm:text-lg',
                                        isLight ? 'text-black' : 'text-slate-100'
                                    ].join(' ')}>
                                        {employee.name}
                                    </p>
                                    <p className={[
                                        'mt-1 text-sm font-black uppercase',
                                        isLight ? 'text-[#FF6B00]' : 'text-purple-200'
                                    ].join(' ')}>
                                        {employee.position}
                                    </p>
                                    <p className={[
                                        'mt-1 text-sm font-bold transition-colors duration-300',
                                        isLight ? 'text-gray-500' : 'text-slate-500'
                                    ].join(' ')}>
                                        {employee.department}
                                    </p>
                                </div>
                            </div>

                            <div className="mt-5 flex flex-col gap-3 sm:mt-6 sm:flex-row sm:justify-end">
                                <Button variant="secondary" className="w-full sm:w-auto" onClick={onBackToList} theme={theme}>
                                    Kembali ke Daftar Pegawai
                                </Button>
                                <Button variant="primary" className="w-full sm:w-auto" onClick={onContinue} theme={theme}>
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
