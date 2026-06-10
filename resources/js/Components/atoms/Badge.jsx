// Atom badge kecil untuk status periode, pegawai, dan informasi ringkas.
import { motion } from 'framer-motion';

const variantClasses = {
    success: 'border-emerald-400/30 bg-emerald-500/15 text-emerald-300',
    warning: 'border-amber-400/30 bg-amber-500/15 text-amber-300',
    danger: 'border-red-400/30 bg-red-500/15 text-red-300',
    default: 'border-slate-500/25 bg-slate-700/25 text-slate-300',
    info: 'border-cyan-400/30 bg-cyan-500/15 text-cyan-300',
};

const lightVariantClasses = {
    success: 'border-2 border-black bg-[#C8E6C9] text-black shadow-[1px_1px_0px_0px_#000]',
    warning: 'border-2 border-black bg-[#FFE082] text-black shadow-[1px_1px_0px_0px_#000]',
    danger: 'border-2 border-black bg-[#FFCDD2] text-black shadow-[1px_1px_0px_0px_#000]',
    default: 'border-2 border-black bg-gray-200 text-gray-700 shadow-[1px_1px_0px_0px_#000]',
    info: 'border-2 border-black bg-[#B2EBF2] text-black shadow-[1px_1px_0px_0px_#000]',
};

export default function Badge({ children, variant = 'default', pulse = false, className = '', theme = 'dark' }) {
    const isLight = theme === 'light';
    const currentVariantClasses = isLight ? lightVariantClasses[variant] : variantClasses[variant];

    return (
        <motion.span
            animate={pulse ? { opacity: [0.65, 1, 0.65], scale: [1, 1.03, 1] } : undefined}
            transition={pulse ? { duration: 1.4, repeat: Infinity, ease: 'easeInOut' } : undefined}
            className={[
                'inline-flex max-w-full items-center justify-center border px-2.5 py-1 text-center text-xs font-black uppercase leading-tight tracking-normal sm:tracking-wide',
                isLight ? 'rounded-md' : 'rounded-full',
                currentVariantClasses,
                className,
            ].join(' ')}
        >
            {children}
        </motion.span>
    );
}
