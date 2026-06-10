// Organism grid kartu pegawai dengan breadcrumb dan tracker periode.
import { motion } from 'framer-motion';
import { useMemo, useState } from 'react';
import Button from '../atoms/Button';
import ProgressTracker from './ProgressTracker';

const containerVariants = {
    hidden: { opacity: 0 },
    visible: {
        opacity: 1,
        transition: { staggerChildren: 0.02 },
    },
};

const itemVariants = {
    hidden: { opacity: 0, y: 26, scale: 0.96 },
    visible: {
        opacity: 1,
        y: 0,
        scale: 1,
        transition: { duration: 0.36, ease: [0.22, 1, 0.36, 1] },
    },
};

export default function HeroGrid({
    employees,
    completedEmployees = [],
    selectedPeriod,
    onSelectEmployee,
    onBackToPeriod,
}) {
    const [searchQuery, setSearchQuery] = useState('');
    const [statusFilter, setStatusFilter] = useState('all'); // 'all', 'undone', 'done'
    const [visibleCount, setVisibleCount] = useState(30);

    const completedSet = new Set(completedEmployees);
    const overallProgress = employees.length > 0 ? (completedEmployees.length / employees.length) * 100 : 0;

    const filteredEmployees = useMemo(() => {
        return employees.filter((employee) => {
            const isCompleted = completedSet.has(employee.id);
            const matchesStatus =
                statusFilter === 'all' ||
                (statusFilter === 'done' && isCompleted) ||
                (statusFilter === 'undone' && !isCompleted);

            const query = searchQuery.toLowerCase().trim();
            const matchesSearch =
                !query ||
                (employee.name && employee.name.toLowerCase().includes(query)) ||
                (employee.position && employee.position.toLowerCase().includes(query)) ||
                (employee.department && employee.department.toLowerCase().includes(query)) ||
                (employee.jabatan && employee.jabatan.toLowerCase().includes(query)) ||
                (employee.departemen && employee.departemen.toLowerCase().includes(query));

            return matchesStatus && matchesSearch;
        });
    }, [employees, completedSet, statusFilter, searchQuery]);

    const slicedEmployees = useMemo(() => {
        return filteredEmployees.slice(0, visibleCount);
    }, [filteredEmployees, visibleCount]);

    return (
        <main className="mx-auto w-full max-w-7xl px-4 py-5 sm:px-6 sm:py-6 lg:px-8">
            <div className="mb-5 flex flex-col gap-4 sm:mb-6 lg:flex-row lg:items-center lg:justify-between">
                <div className="min-w-0">
                    <nav className="flex flex-wrap items-center gap-2 text-xs font-semibold text-slate-500 sm:text-sm">
                        <button
                            type="button"
                            onClick={onBackToPeriod}
                            className="rounded-md text-cyan-300 outline-none transition-colors hover:text-cyan-100 focus-visible:ring-2 focus-visible:ring-cyan-400"
                        >
                            Kuesioner 360
                        </button>
                        <span>›</span>
                        <span className="text-slate-300">
                            {selectedPeriod?.code} - {selectedPeriod?.month}
                        </span>
                    </nav>
                    <h1 className="mt-3 text-2xl font-black leading-tight text-slate-100 sm:mt-4 sm:text-4xl">
                        Pilih Pegawai yang Akan Dinilai
                    </h1>
                </div>

                <Button variant="secondary" className="w-full sm:w-auto" onClick={onBackToPeriod}>
                    ← Kembali ke Daftar Periode
                </Button>
            </div>

            <ProgressTracker
                employees={employees}
                completedEmployees={completedEmployees}
                overallProgress={overallProgress}
                selectedPeriod={selectedPeriod}
            />

            {/* Search and Filter Row */}
            <div className="mt-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div className="relative flex-1">
                    <span className="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-500">
                        🔍
                    </span>
                    <input
                        type="text"
                        placeholder="Cari pegawai berdasarkan nama, jabatan, atau departemen..."
                        value={searchQuery}
                        onChange={(e) => {
                            setSearchQuery(e.target.value);
                            setVisibleCount(30);
                        }}
                        className="w-full rounded-lg border border-slate-800 bg-slate-900/60 py-2.5 pl-10 pr-4 text-sm font-medium text-slate-200 placeholder-slate-500 outline-none transition-all duration-200 focus:border-cyan-500/60 focus:ring-1 focus:ring-cyan-500/30"
                    />
                </div>
                <div className="flex items-center gap-2">
                    <span className="text-xs font-bold uppercase tracking-wider text-slate-500 whitespace-nowrap">
                        Filter Status:
                    </span>
                    <select
                        value={statusFilter}
                        onChange={(e) => {
                            setStatusFilter(e.target.value);
                            setVisibleCount(30);
                        }}
                        className="rounded-lg border border-slate-800 bg-slate-900/60 px-3 py-2.5 text-sm font-semibold text-slate-200 outline-none transition-all duration-200 focus:border-cyan-500/60 focus:ring-1 focus:ring-cyan-500/30 cursor-pointer"
                    >
                        <option value="all">Semua Pegawai</option>
                        <option value="undone">Belum Dinilai</option>
                        <option value="done">Sudah Dinilai (Draft)</option>
                    </select>
                </div>
            </div>

            {filteredEmployees.length === 0 ? (
                <div className="mt-8 flex flex-col items-center justify-center rounded-lg border border-slate-800 bg-slate-900/30 p-10 text-center">
                    <span className="text-4xl mb-3">🔍</span>
                    <p className="text-sm font-bold text-slate-400 uppercase tracking-wide">Tidak ada pegawai yang cocok</p>
                    <p className="text-xs text-slate-500 mt-1">Coba periksa ejaan atau ganti filter status penilaian Anda.</p>
                </div>
            ) : (
                <>
                    <motion.div
                        variants={containerVariants}
                        initial="hidden"
                        animate="visible"
                        className="mt-5 grid grid-cols-2 gap-3 sm:mt-6 sm:grid-cols-3 sm:gap-4 md:grid-cols-4 xl:grid-cols-5"
                    >
                        {slicedEmployees.map((employee) => {
                            const isCompleted = completedSet.has(employee.id);

                            return (
                                <motion.button
                                    key={employee.id}
                                    type="button"
                                    variants={itemVariants}
                                    onClick={() => onSelectEmployee?.(employee)}
                                    whileHover={{ scale: 1.025, y: -2 }}
                                    whileTap={{ scale: 0.97 }}
                                    transition={{ type: 'spring', stiffness: 380, damping: 22 }}
                                    className={[
                                        'group flex min-h-36 flex-col items-center justify-center gap-2 rounded-lg border bg-gray-900 p-3 text-center outline-none transition-all duration-200 focus-visible:ring-2 focus-visible:ring-cyan-400 focus-visible:ring-offset-2 focus-visible:ring-offset-gray-950 sm:min-h-40 sm:p-4',
                                        isCompleted
                                            ? 'border-emerald-500/20 hover:border-emerald-400/50 hover:shadow-[0_0_20px_rgba(52,211,153,0.15)]'
                                            : 'border-purple-500/20 hover:border-cyan-400/50 hover:shadow-[0_0_20px_rgba(6,182,212,0.15)]',
                                    ].join(' ')}
                                >
                                    <span className="text-3xl sm:text-4xl">{employee.avatar}</span>

                                    <span className="line-clamp-2 text-xs font-bold leading-snug text-slate-200 sm:text-sm">
                                        {employee.name}
                                    </span>

                                    <span className={[
                                        'inline-flex items-center gap-1 rounded-full border px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide',
                                        isCompleted
                                            ? 'border-emerald-500/30 bg-emerald-500/10 text-emerald-300'
                                            : 'border-cyan-500/30 bg-cyan-500/10 text-cyan-300',
                                    ].join(' ')}>
                                        {isCompleted ? 'Draft' : 'Belum'} {isCompleted ? '✓' : ''}
                                    </span>

                                    <span className="text-[10px] font-semibold text-cyan-400 opacity-100 transition-opacity sm:opacity-0 sm:group-hover:opacity-100">
                                        {isCompleted ? 'Pratinjau →' : 'Nilai →'}
                                    </span>
                                </motion.button>
                            );
                        })}
                    </motion.div>

                    {filteredEmployees.length > visibleCount && (
                        <div className="mt-8 flex justify-center">
                            <Button
                                variant="secondary"
                                onClick={() => setVisibleCount((prev) => prev + 30)}
                                className="px-6 py-2.5 font-bold text-cyan-300 hover:text-cyan-100 hover:bg-cyan-950/20"
                            >
                                Muat Lebih Banyak ({filteredEmployees.length - visibleCount} lagi)
                            </Button>
                        </div>
                    )}
                </>
            )}
        </main>
    );
}
