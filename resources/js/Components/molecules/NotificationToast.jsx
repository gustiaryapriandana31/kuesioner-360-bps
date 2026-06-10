// Molecule toast notifikasi sukses yang muncul dari atas tengah layar.
import { useEffect } from 'react';
import { AnimatePresence, motion } from 'framer-motion';

export default function NotificationToast({ show, message, onDismiss }) {
    useEffect(() => {
        if (!show) {
            return undefined;
        }

        const timer = window.setTimeout(() => {
            onDismiss?.();
        }, 3000);

        return () => window.clearTimeout(timer);
    }, [show, onDismiss]);

    return (
        <AnimatePresence>
            {show && (
                <motion.div
                    initial={{ y: -80, opacity: 0, scale: 0.96 }}
                    animate={{ y: 0, opacity: 1, scale: 1 }}
                    exit={{ y: -80, opacity: 0, scale: 0.96 }}
                    transition={{ duration: 0.28, ease: [0.22, 1, 0.36, 1] }}
                    className="fixed left-1/2 top-4 z-50 w-[calc(100%-1.5rem)] max-w-md -translate-x-1/2 rounded-lg border border-emerald-400/30 bg-gray-900/95 p-3 shadow-2xl shadow-black/35 backdrop-blur sm:top-5 sm:w-[calc(100%-2rem)] sm:p-4"
                >
                    <div className="flex items-center gap-3">
                        <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-emerald-500/20 text-xl font-black text-emerald-300 sm:h-11 sm:w-11 sm:text-2xl">
                            ✓
                        </div>
                        <div>
                            <p className="text-sm font-black text-slate-100">
                                Berhasil
                            </p>
                            <p className="mt-1 text-sm leading-5 text-slate-400">
                                {message}
                            </p>
                        </div>
                    </div>
                </motion.div>
            )}
        </AnimatePresence>
    );
}
