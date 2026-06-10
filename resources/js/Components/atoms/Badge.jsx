// Atom badge kecil untuk status periode, pegawai, dan informasi ringkas.
import { motion } from 'framer-motion';

const variantClasses = {
    success: 'border-emerald-400/30 bg-emerald-500/15 text-emerald-300',
    warning: 'border-amber-400/30 bg-amber-500/15 text-amber-300',
    danger: 'border-red-400/30 bg-red-500/15 text-red-300',
    default: 'border-slate-500/25 bg-slate-700/25 text-slate-300',
    info: 'border-cyan-400/30 bg-cyan-500/15 text-cyan-300',
};

export default function Badge({ children, variant = 'default', pulse = false, className = '' }) {
    return (
        <motion.span
            animate={pulse ? { opacity: [0.65, 1, 0.65], scale: [1, 1.03, 1] } : undefined}
            transition={pulse ? { duration: 1.4, repeat: Infinity, ease: 'easeInOut' } : undefined}
            className={[
                'inline-flex max-w-full items-center justify-center rounded-full border px-2.5 py-1 text-center text-xs font-bold uppercase leading-tight tracking-normal sm:tracking-wide',
                variantClasses[variant],
                className,
            ].join(' ')}
        >
            {children}
        </motion.span>
    );
}
