// Molecule banner flash full-width yang muncul setelah penilaian satu pegawai berhasil disimpan.
import { AnimatePresence, motion } from 'framer-motion';
import { useEffect } from 'react';

export default function SaveBanner({ show, savedName, nextName, onDismiss }) {
    // Auto dismiss setelah 4 detik
    useEffect(() => {
        if (!show) return;

        const timer = window.setTimeout(() => {
            onDismiss?.();
        }, 4000);

        return () => window.clearTimeout(timer);
    }, [show, savedName]);

    return (
        <AnimatePresence>
            {show && (
                <motion.div
                    key="save-banner"
                    initial={{ y: '-100%', opacity: 0 }}
                    animate={{ y: '0%', opacity: 1 }}
                    exit={{ y: '-100%', opacity: 0 }}
                    transition={{ duration: 0.32, ease: [0.22, 1, 0.36, 1] }}
                    className="sticky top-0 z-40 w-full border-b border-emerald-500/50 bg-emerald-900/75 backdrop-blur-sm"
                >
                    <div className="mx-auto flex max-w-6xl items-start justify-between gap-3 px-4 py-3 sm:items-center sm:px-6">
                        <p className="min-w-0 text-sm font-semibold leading-6 text-emerald-100">
                            ✅ Penilaian{' '}
                            <span className="font-black text-white">{savedName}</span>{' '}
                            tersimpan!
                            {nextName && (
                                <>
                                    {' '}Sekarang menilai:{' '}
                                    <span className="font-black text-cyan-200">{nextName}</span>
                                </>
                            )}
                        </p>

                        <button
                            type="button"
                            onClick={onDismiss}
                            className="shrink-0 rounded-md p-1.5 text-emerald-300 outline-none transition-colors hover:text-white focus-visible:ring-2 focus-visible:ring-emerald-400"
                            aria-label="Tutup banner"
                        >
                            <svg className="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 1 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 0 0-1.06-1.06L10 8.94 6.28 5.22Z" />
                            </svg>
                        </button>
                    </div>
                </motion.div>
            )}
        </AnimatePresence>
    );
}
