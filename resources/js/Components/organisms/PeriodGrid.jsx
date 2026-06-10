// Organism grid periode kuesioner 12 bulan dengan summary status.
import { motion } from 'framer-motion';
import KuesionerPeriodCard from '../molecules/KuesionerPeriodCard';

const containerVariants = {
    hidden: { opacity: 0 },
    visible: {
        opacity: 1,
        transition: {
            staggerChildren: 0.06,
        },
    },
};

const itemVariants = {
    hidden: { opacity: 0, y: 22 },
    visible: {
        opacity: 1,
        y: 0,
        transition: { duration: 0.35, ease: [0.22, 1, 0.36, 1] },
    },
};

export default function PeriodGrid({
    periods,
    completedData = {},
    totalEmployees = 5,
    onSelectPeriod,
}) {
    const completedCount = periods.filter((period) => period.status === 'completed').length;
    const activeCount = periods.filter((period) => period.status === 'active').length;
    const unavailableCount = periods.filter((period) => period.status === 'unavailable').length;
    const safeTotalEmployees = Math.max(Number(totalEmployees) || 0, 0);

    return (
        <section className="mx-auto w-full max-w-7xl px-4 py-6 sm:px-6 sm:py-8 lg:px-8">
            <motion.div
                initial={{ opacity: 0, y: 18 }}
                animate={{ opacity: 1, y: 0 }}
                transition={{ duration: 0.38, ease: 'easeOut' }}
                className="mb-6 sm:mb-8"
            >
                <h2 className="text-2xl font-black leading-tight text-slate-100 sm:text-3xl">
                    Daftar Periode Penilaian
                </h2>
                <p className="mt-3 max-w-3xl text-sm leading-6 text-slate-500 sm:text-base sm:leading-7">
                    Pilih periode yang sedang aktif atau buka periode selesai untuk melihat status penilaian Anda.
                </p>

                <div className="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-3 lg:max-w-2xl">
                    <div className="rounded-lg border border-emerald-500/25 bg-emerald-500/10 px-4 py-3 text-sm font-bold text-emerald-200">
                        {completedCount} Selesai
                    </div>
                    <div className="rounded-lg border border-cyan-500/25 bg-cyan-500/10 px-4 py-3 text-sm font-bold text-cyan-200">
                        {activeCount} Aktif
                    </div>
                    <div className="rounded-lg border border-slate-500/20 bg-slate-700/20 px-4 py-3 text-sm font-bold text-slate-400">
                        {unavailableCount} Belum Tersedia
                    </div>
                </div>
            </motion.div>

            <motion.div
                variants={containerVariants}
                initial="hidden"
                animate="visible"
                className="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4"
            >
                {periods.map((period) => {
                    const completedEmployees = Array.isArray(completedData[period.id])
                        ? [...new Set(completedData[period.id])]
                        : [];
                    const periodTotal = period.total_employees !== undefined ? Number(period.total_employees) : safeTotalEmployees;
                    const completedEmployeeCount = periodTotal > 0
                        ? Math.min(completedEmployees.length, periodTotal)
                        : 0;
                    const progressPercent = periodTotal > 0
                        ? Math.min(100, (completedEmployeeCount / periodTotal) * 100)
                        : 0;

                    return (
                        <motion.div key={period.id} variants={itemVariants}>
                            <KuesionerPeriodCard
                                period={period}
                                isCompleted={periodTotal > 0 && completedEmployeeCount >= periodTotal}
                                progressData={{
                                    completedCount: completedEmployeeCount,
                                    totalEmployees: periodTotal,
                                    percent: progressPercent,
                                }}
                                onClick={onSelectPeriod}
                            />
                        </motion.div>
                    );
                })}
            </motion.div>
        </section>
    );
}
