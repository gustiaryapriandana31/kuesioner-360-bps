// Template halaman pemilihan pegawai untuk periode yang dipilih.
import { motion } from 'framer-motion';
import HeroGrid from '../organisms/HeroGrid';

export default function SelectEmployeeTemplate({
    employees,
    completedEmployees,
    selectedPeriod,
    onSelectEmployee,
    onBackToPeriod,
    theme = 'dark',
    onToggleTheme,
}) {
    const isLight = theme === 'light';
    return (
        <motion.div
            initial={{ opacity: 0, scale: 0.98, y: 12 }}
            animate={{ opacity: 1, scale: 1, y: 0 }}
            exit={{ opacity: 0, scale: 0.98, y: -8 }}
            transition={{ duration: 0.3, ease: [0.22, 1, 0.36, 1] }}
            className={`min-h-svh transition-colors duration-300 ${
                isLight ? 'bg-[#FFFDF6] text-black' : 'bg-gray-950 text-slate-200'
            }`}
        >
            <HeroGrid
                employees={employees}
                completedEmployees={completedEmployees}
                selectedPeriod={selectedPeriod}
                onSelectEmployee={onSelectEmployee}
                onBackToPeriod={onBackToPeriod}
                theme={theme}
                onToggleTheme={onToggleTheme}
            />
        </motion.div>
    );
}
