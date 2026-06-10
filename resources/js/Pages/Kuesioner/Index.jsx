// Page daftar periode Kuesioner 360 yang memakai data Inertia dari backend.
import { Head, router } from '@inertiajs/react';
import { AnimatePresence } from 'framer-motion';
import PeriodSelectTemplate from '../../Components/templates/PeriodSelectTemplate';

export default function Index({
    periods = [],
    completedData = {},
    totalEmployees = 0,
}) {
    const handleSelectPeriod = (period) => {
        if (period.status === 'unavailable') {
            return;
        }

        router.visit(`/kuesioner/${period.kode}`);
    };

    return (
        <>
            <Head title="Kuesioner 360" />

            <div className="min-h-svh bg-gray-950">
                <AnimatePresence mode="wait">
                    <PeriodSelectTemplate
                        key="period-select"
                        periods={periods}
                        completedData={completedData}
                        totalEmployees={totalEmployees}
                        onSelectPeriod={handleSelectPeriod}
                    />
                </AnimatePresence>
            </div>
        </>
    );
}
