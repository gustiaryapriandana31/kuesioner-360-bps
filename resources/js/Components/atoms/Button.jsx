// Atom tombol reusable dengan varian, ukuran, loading, dan animasi Framer Motion.
import { motion } from 'framer-motion';

const variantClasses = {
    primary: 'bg-gradient-to-r from-violet-600 via-purple-600 to-cyan-500 text-white shadow-lg shadow-purple-950/40 hover:shadow-purple-500/30',
    secondary: 'border border-purple-500/25 bg-gray-900 text-slate-200 hover:border-cyan-400/50 hover:bg-gray-800',
    danger: 'bg-gradient-to-r from-red-600 to-orange-500 text-white shadow-lg shadow-red-950/40 hover:shadow-red-500/25',
    ghost: 'text-slate-300 hover:bg-gray-800/80 hover:text-white',
};

const sizeClasses = {
    sm: 'min-h-9 px-3 py-2 text-sm',
    md: 'min-h-11 px-5 py-2.5 text-sm',
    lg: 'min-h-12 px-6 py-3 text-base',
};

export default function Button({
    children,
    variant = 'primary',
    size = 'md',
    type = 'button',
    disabled = false,
    loading = false,
    className = '',
    onClick,
}) {
    const isDisabled = disabled || loading;

    return (
        <motion.button
            type={type}
            onClick={onClick}
            disabled={isDisabled}
            whileHover={isDisabled ? undefined : { scale: 1.02, y: -1 }}
            whileTap={isDisabled ? undefined : { scale: 0.97 }}
            transition={{ type: 'spring', stiffness: 420, damping: 26 }}
            className={[
                'inline-flex max-w-full items-center justify-center gap-2 rounded-lg text-center font-semibold leading-tight outline-none transition-colors duration-200 focus-visible:ring-2 focus-visible:ring-cyan-400 focus-visible:ring-offset-2 focus-visible:ring-offset-gray-950',
                variantClasses[variant],
                sizeClasses[size],
                isDisabled ? 'cursor-not-allowed opacity-50' : 'cursor-pointer',
                className,
            ].join(' ')}
        >
            {loading && (
                <span className="h-4 w-4 animate-spin rounded-full border-2 border-white/40 border-t-white" />
            )}
            <span className="min-w-0">{children}</span>
        </motion.button>
    );
}
