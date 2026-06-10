// Atom ThemeToggle untuk mempermudah perpindahan antara Light Mode (Neobrutalism) dan Dark Mode (Glassmorphism).
import { motion } from 'framer-motion';

export default function ThemeToggle({ theme, onToggle }) {
    const isLight = theme === 'light';

    return (
        <motion.button
            type="button"
            onClick={onToggle}
            whileHover={{ scale: 1.05 }}
            whileTap={{ scale: 0.95 }}
            className={[
                'flex items-center justify-center gap-2 px-3.5 py-2 font-black text-xs sm:text-sm transition-all outline-none',
                isLight
                    ? 'bg-[#FFCA28] text-black border-2 border-black shadow-[2px_2px_0px_0px_#000] hover:shadow-[1px_1px_0px_0px_#000] hover:translate-x-[1px] hover:translate-y-[1px] rounded-md'
                    : 'rounded-lg border border-white/10 bg-gray-900 text-slate-300 hover:border-cyan-400/50 hover:text-white',
            ].join(' ')}
        >
            <span>{isLight ? '🌙' : '☀️'}</span>
            <span>{isLight ? 'Dark Mode' : 'Light Mode'}</span>
        </motion.button>
    );
}
