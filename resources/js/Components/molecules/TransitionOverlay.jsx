// Molecule overlay visual feedback saat menyimpan penilaian dan berpindah antar pegawai.
import { AnimatePresence, motion } from 'framer-motion';

export default function TransitionOverlay({ isVisible, savingName, nextName }) {
    return (
        <AnimatePresence>
            {isVisible && (
                <motion.div
                    key="transition-overlay"
                    initial={{ opacity: 0 }}
                    animate={{ opacity: 1 }}
                    exit={{ opacity: 0 }}
                    transition={{ duration: 0.2, ease: 'easeInOut' }}
                    className="fixed inset-0 z-50 flex items-center justify-center bg-gray-950/90 backdrop-blur-sm"
                >
                    <div className="flex w-full max-w-lg flex-col items-center gap-5 px-6 text-center sm:gap-6 sm:px-8">
                        {/* Spinner */}
                        <motion.div
                            animate={{ rotate: 360 }}
                            transition={{ duration: 0.9, repeat: Infinity, ease: 'linear' }}
                            className="h-12 w-12 rounded-full border-4 border-purple-500/30 border-t-purple-400 sm:h-14 sm:w-14"
                        />

                        {/* Teks utama */}
                        <div className="flex flex-col items-center gap-2">
                            {savingName && (
                                <p className="text-base font-bold leading-7 text-slate-100 sm:text-xl">
                                    Menyimpan penilaian{' '}
                                    <span className="text-purple-300">{savingName}</span>
                                    ...
                                </p>
                            )}

                        {/* Sub teks: pegawai berikutnya (hanya ditampilkan jika ada) */}
                        {nextName && (
                            <p className="text-sm font-medium text-slate-400">
                                Selanjutnya: <span className="text-cyan-300">{nextName}</span>
                            </p>
                        )}
                        </div>
                    </div>
                </motion.div>
            )}
        </AnimatePresence>
    );
}
