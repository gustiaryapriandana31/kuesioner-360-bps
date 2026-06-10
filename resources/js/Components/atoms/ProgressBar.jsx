// Atom progress bar animasi untuk kemajuan penilaian dan pengisian soal.
import { motion } from 'framer-motion';

const sizeClasses = {
    sm: 'h-2',
    md: 'h-3',
    lg: 'h-4',
};

export default function ProgressBar({ value = 0, showLabel = true, size = 'md', className = '' }) {
    const safeValue = Math.min(100, Math.max(0, Number(value) || 0));

    return (
        <div className={['w-full', className].join(' ')}>
            <div className={['overflow-hidden rounded-full bg-gray-800 ring-1 ring-white/5', sizeClasses[size]].join(' ')}>
                <motion.div
                    initial={{ width: 0 }}
                    animate={{ width: `${safeValue}%` }}
                    transition={{ duration: 0.65, ease: [0.22, 1, 0.36, 1] }}
                    className="h-full rounded-full bg-gradient-to-r from-purple-600 via-violet-500 to-cyan-400 shadow-[0_0_18px_rgba(6,182,212,0.35)]"
                />
            </div>
            {showLabel && (
                <div className="mt-2 text-right text-xs font-semibold text-slate-400">
                    {Math.round(safeValue)}%
                </div>
            )}
        </div>
    );
}
