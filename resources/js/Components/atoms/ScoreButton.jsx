// Atom tombol skor 1 sampai 10 dengan warna nilai, glow, dan ripple klik.
import { useState } from 'react';
import { motion, AnimatePresence } from 'framer-motion';

function gradientForValue(value) {
    if (value <= 3) {
        return 'from-red-600 to-orange-500';
    }

    if (value <= 6) {
        return 'from-yellow-500 to-amber-500';
    }

    return 'from-teal-500 to-emerald-500';
}

export default function ScoreButton({ value, isSelected = false, onClick, isPreview = false }) {
    const [ripples, setRipples] = useState([]);
    const gradient = gradientForValue(value);

    const handleClick = () => {
        if (isPreview) return; // Tidak bisa klik di preview mode
        const rippleId = Date.now();
        setRipples((items) => [...items, rippleId]);
        window.setTimeout(() => {
            setRipples((items) => items.filter((item) => item !== rippleId));
        }, 520);
        onClick?.(value);
    };

    // Di preview: tombol non-selected disabled+redup, selected tampil normal tapi non-clickable
    const previewNonSelected = isPreview && !isSelected;
    const previewSelected = isPreview && isSelected;

    return (
        <motion.button
            type="button"
            onClick={handleClick}
            disabled={previewNonSelected}
            whileHover={isPreview ? undefined : { scale: 1.05, y: -2 }}
            whileTap={isPreview ? undefined : { scale: 0.94 }}
            animate={isSelected ? { scale: 1.06 } : { scale: 1 }}
            transition={{ type: 'spring', stiffness: 430, damping: 22 }}
            className={[
                'relative h-11 w-11 overflow-hidden rounded-full border text-sm font-black outline-none transition-all duration-200 focus-visible:ring-2 focus-visible:ring-cyan-300 focus-visible:ring-offset-2 focus-visible:ring-offset-gray-950 sm:h-10 sm:w-10 md:h-11 md:w-11 lg:h-12 lg:w-12',
                previewNonSelected
                    ? 'cursor-not-allowed border-gray-700 bg-gray-900 text-gray-600 opacity-40'
                    : previewSelected
                        ? `pointer-events-none z-10 border-cyan-100/80 bg-gradient-to-br ${gradient} text-white shadow-[0_0_24px_rgba(45,212,191,0.55)] ring-2 ring-white/70`
                        : isSelected
                            ? `z-10 border-cyan-100/80 bg-gradient-to-br ${gradient} text-white shadow-[0_0_24px_rgba(45,212,191,0.55)] ring-2 ring-white/70`
                            : 'border border-slate-700 bg-gray-950 text-slate-400 shadow-lg shadow-black/25 ring-1 ring-white/5 hover:border-slate-500 hover:bg-gray-900 hover:text-slate-200',
            ].join(' ')}
            aria-pressed={isSelected}
            aria-label={`Nilai ${value}`}
        >
            <AnimatePresence>
                {ripples.map((ripple) => (
                    <motion.span
                        key={ripple}
                        initial={{ scale: 0, opacity: 0.45 }}
                        animate={{ scale: 2.2, opacity: 0 }}
                        exit={{ opacity: 0 }}
                        transition={{ duration: 0.5, ease: 'easeOut' }}
                        className="absolute inset-0 m-auto h-7 w-7 rounded-full bg-white"
                    />
                ))}
            </AnimatePresence>
            <span className="relative z-10">{value}</span>
        </motion.button>
    );
}
