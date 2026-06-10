import { useState, useEffect } from 'react';
import { Head, router } from '@inertiajs/react';
import { AnimatePresence } from 'framer-motion';
import PeriodSelectTemplate from '../../Components/templates/PeriodSelectTemplate';

export default function Index({
    periods = [],
    completedData = {},
    totalEmployees = 0,
}) {
    const [theme, setTheme] = useState(() => {
        try {
            return localStorage.getItem('kuesioner:theme') || 'dark';
        } catch {
            return 'dark';
        }
    });

    const handleToggleTheme = () => {
        const nextTheme = theme === 'dark' ? 'light' : 'dark';
        setTheme(nextTheme);
        try {
            localStorage.setItem('kuesioner:theme', nextTheme);
        } catch {}
    };

    const handleSelectPeriod = (period) => {
        if (period.status === 'unavailable') {
            return;
        }

        router.visit(`/kuesioner/${period.kode}`);
    };

    return (
        <>
            <Head title="Kuesioner 360" />

            <div className={`min-h-svh transition-colors duration-300 ${theme === 'light' ? 'bg-[#FFFDF6]' : 'bg-gray-950'}`}>
                <AnimatePresence mode="wait">
                    <PeriodSelectTemplate
                        key="period-select"
                        periods={periods}
                        completedData={completedData}
                        totalEmployees={totalEmployees}
                        onSelectPeriod={handleSelectPeriod}
                        theme={theme}
                        onToggleTheme={handleToggleTheme}
                    />
                </AnimatePresence>
            </div>
        </>
    );
}
