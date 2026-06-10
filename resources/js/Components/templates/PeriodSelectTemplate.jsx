// Template halaman pertama untuk memilih periode Kuesioner 360.
import { router, usePage } from '@inertiajs/react';
import { motion } from 'framer-motion';
import Button from '../atoms/Button';
import PeriodGrid from '../organisms/PeriodGrid';

export default function PeriodSelectTemplate({
    periods,
    completedData,
    totalEmployees,
    onSelectPeriod,
}) {
    const { props } = usePage();
    const user = props.auth?.user;
    const pegawai = user?.pegawai;

    const handleLogout = () => {
        router.post('/keluar');
    };

    return (
        <motion.div
            initial={{ opacity: 0 }}
            animate={{ opacity: 1 }}
            exit={{ opacity: 0, y: -16 }}
            transition={{ duration: 0.32, ease: 'easeOut' }}
            className="min-h-svh bg-gray-950 text-slate-200"
        >
            <header className="border-b border-white/10 bg-gray-950/95">
                <div className="mx-auto flex w-full max-w-7xl flex-col gap-4 px-4 py-4 sm:px-6 lg:flex-row lg:items-center lg:justify-between lg:px-8">
                    <div className="flex min-w-0 items-center gap-3">
                        <div className="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg border border-cyan-400/25 bg-cyan-500/10 text-sm font-black text-cyan-200 sm:h-12 sm:w-12">
                            BPS
                        </div>
                        <div className="min-w-0">
                            <p className="text-base font-black text-slate-100 sm:text-xl">
                                Dashboard Kuesioner 360
                            </p>
                            <p className="text-sm font-semibold text-slate-500">
                                BPS Kabupaten Ogan Ilir
                            </p>
                        </div>
                    </div>

                    <div className="flex w-full flex-col gap-3 sm:flex-row sm:items-center lg:w-auto">
                        <div className="min-w-0 rounded-lg border border-purple-500/20 bg-gray-900 px-4 py-3 sm:min-w-64">
                            <p className="truncate text-sm font-black text-slate-100">
                                {pegawai?.nama ?? user?.name ?? 'Pegawai'}
                            </p>
                            <p className="mt-1 text-xs font-semibold text-slate-500">
                                {pegawai?.jabatan ?? 'Dashboard pegawai'}
                            </p>
                        </div>
                        <Button variant="secondary" className="w-full sm:w-auto" onClick={handleLogout}>
                            Keluar
                        </Button>
                    </div>
                </div>
            </header>

            <section className="mx-auto w-full max-w-7xl px-4 pt-6 sm:px-6 sm:pt-8 lg:px-8">
                <div className="rounded-lg border border-purple-500/20 bg-gray-900 p-4 shadow-2xl shadow-black/25 sm:p-6">
                    <p className="text-sm font-bold uppercase tracking-wide text-purple-300">
                        Dashboard Pegawai
                    </p>
                    <h1 className="mt-2 text-2xl font-black leading-tight text-slate-100 sm:text-3xl">
                        Kuesioner yang Ditugaskan
                    </h1>
                    <p className="mt-3 max-w-3xl text-sm leading-6 text-slate-500">
                        Pilih periode penilaian yang tersedia, isi penilaian untuk pegawai yang ditugaskan, lalu pantau progres penyelesaian dari kartu periode.
                    </p>
                </div>
            </section>

            <PeriodGrid
                periods={periods}
                completedData={completedData}
                totalEmployees={totalEmployees}
                onSelectPeriod={onSelectPeriod}
            />
        </motion.div>
    );
}
