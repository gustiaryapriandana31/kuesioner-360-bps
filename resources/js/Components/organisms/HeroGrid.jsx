import { motion } from 'framer-motion';
import { useMemo, useState } from 'react';
import Button from '../atoms/Button';
import ThemeToggle from '../atoms/ThemeToggle';
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
    theme = 'dark',
    onToggleTheme,
}) {
    const [searchQuery, setSearchQuery] = useState('');
    const [statusFilter, setStatusFilter] = useState('all'); // 'all', 'undone', 'done'
    const [visibleCount, setVisibleCount] = useState(30);
    const isLight = theme === 'light';

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
                    <nav className={`flex flex-wrap items-center gap-2 text-xs font-semibold sm:text-sm ${
                        isLight ? 'text-gray-500' : 'text-slate-500'
                    }`}>
                        <button
                            type="button"
                            onClick={onBackToPeriod}
                            className={`rounded-md outline-none transition-colors ${
                                isLight 
                                    ? 'text-[#FF6B00] hover:text-[#FF8533] focus-visible:ring-2 focus-visible:ring-black' 
                                    : 'text-cyan-300 hover:text-cyan-100 focus-visible:ring-2 focus-visible:ring-cyan-400'
                            }`}
                        >
                            Kuesioner 360
                        </button>
                        <span>›</span>
                        <span className={isLight ? 'text-gray-800 font-bold' : 'text-slate-300'}>
                            {selectedPeriod?.code} - {selectedPeriod?.month}
                        </span>
                    </nav>
                    <h1 className={`mt-3 text-2xl font-black leading-tight sm:mt-4 sm:text-4xl ${
                        isLight ? 'text-black' : 'text-slate-100'
                    }`}>
                        Pilih Pegawai yang Akan Dinilai
                    </h1>
                </div>

                <div className="flex items-center gap-3 w-full sm:w-auto justify-between sm:justify-end">
                    <ThemeToggle theme={theme} onToggle={onToggleTheme} />
                    <Button variant="secondary" className="w-full sm:w-auto" onClick={onBackToPeriod} theme={theme}>
                        ← Kembali ke Daftar Periode
                    </Button>
                </div>
            </div>

            <ProgressTracker
                employees={employees}
                completedEmployees={completedEmployees}
                overallProgress={overallProgress}
                selectedPeriod={selectedPeriod}
                theme={theme}
            />

            {/* Search and Filter Row */}
            <div className="mt-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
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
                        className={`w-full py-2.5 pl-10 pr-4 text-sm font-medium outline-none transition-all duration-200 ${
                            isLight
                                ? 'border-2 border-black bg-white text-black placeholder-gray-500 shadow-[2px_2px_0px_0px_#000] focus:border-[#FF6B00]'
                                : 'rounded-lg border border-slate-800 bg-slate-900/60 text-slate-200 placeholder-slate-500 focus:border-cyan-500/60 focus:ring-1 focus:ring-cyan-500/30'
                        }`}
                    />
                </div>
                <div className="flex items-center gap-2">
                    <span className={`text-xs font-bold uppercase tracking-wider whitespace-nowrap ${
                        isLight ? 'text-black' : 'text-slate-500'
                    }`}>
                        Filter Status:
                    </span>
                    <select
                        value={statusFilter}
                        onChange={(e) => {
                            setStatusFilter(e.target.value);
                            setVisibleCount(30);
                        }}
                        className={`px-3 py-2.5 text-sm font-semibold outline-none transition-all duration-200 cursor-pointer ${
                            isLight
                                ? 'border-2 border-black bg-white text-black shadow-[2px_2px_0px_0px_#000] focus:border-[#FF6B00]'
                                : 'rounded-lg border border-slate-800 bg-slate-900/60 text-slate-200 focus:border-cyan-500/60 focus:ring-1 focus:ring-cyan-500/30'
                        }`}
                    >
                        <option value="all">Semua Pegawai</option>
                        <option value="undone">Belum Dinilai</option>
                        <option value="done">Sudah Dinilai (Draft)</option>
                    </select>
                </div>
            </div>

            {filteredEmployees.length === 0 ? (
                <div className={`mt-8 flex flex-col items-center justify-center p-10 text-center transition-all ${
                    isLight
                        ? 'border-4 border-black bg-[#FFE082] shadow-[4px_4px_0px_0px_#000]'
                        : 'rounded-lg border border-slate-800 bg-slate-900/30'
                }`}>
                    <span className="text-4xl mb-3">🔍</span>
                    <p className={`text-sm font-black uppercase tracking-wide ${isLight ? 'text-black' : 'text-slate-400'}`}>Tidak ada pegawai yang cocok</p>
                    <p className={`text-xs mt-1 ${isLight ? 'text-gray-700 font-semibold' : 'text-slate-500'}`}>Coba periksa ejaan atau ganti filter status penilaian Anda.</p>
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
                                    whileHover={isLight ? { scale: 1.015, translate: [2, 2] } : { scale: 1.025, y: -2 }}
                                    whileTap={{ scale: 0.97 }}
                                    transition={{ type: 'spring', stiffness: 380, damping: 22 }}
                                    className={[
                                        'group flex min-h-36 flex-col items-center justify-center gap-2 p-3 text-center outline-none transition-all duration-200 sm:min-h-40 sm:p-4',
                                        isLight
                                            ? 'rounded-none border-4 border-black'
                                            : 'rounded-lg border bg-gray-900 focus-visible:ring-2 focus-visible:ring-cyan-400 focus-visible:ring-offset-2 focus-visible:ring-offset-gray-950',
                                        isLight
                                            ? isCompleted
                                                ? 'bg-[#E8F5E9] shadow-[4px_4px_0px_0px_#000] hover:shadow-[1px_1px_0px_0px_#000]'
                                                : 'bg-white shadow-[4px_4px_0px_0px_#000] hover:shadow-[1px_1px_0px_0px_#000]'
                                            : isCompleted
                                                ? 'border-emerald-500/20 hover:border-emerald-400/50 hover:shadow-[0_0_20px_rgba(52,211,153,0.15)]'
                                                : 'border-purple-500/20 hover:border-cyan-400/50 hover:shadow-[0_0_20px_rgba(6,182,212,0.15)]',
                                        isLight ? 'focus-visible:ring-2 focus-visible:ring-black focus-visible:ring-offset-2 focus-visible:ring-offset-white' : ''
                                    ].join(' ')}
                                >
                                    <span className="text-3xl sm:text-4xl">{employee.avatar}</span>

                                    <span className={`line-clamp-2 text-xs font-bold leading-snug sm:text-sm ${
                                        isLight ? 'text-black' : 'text-slate-200'
                                    }`}>
                                        {employee.name}
                                    </span>

                                    <Badge
                                        variant={isCompleted ? 'success' : 'info'}
                                        theme={theme}
                                    >
                                        {isCompleted ? 'Draft' : 'Belum'} {isCompleted ? '✓' : ''}
                                    </Badge>

                                    <span className={`text-[10px] font-black uppercase tracking-wide opacity-100 transition-opacity sm:opacity-0 sm:group-hover:opacity-100 ${
                                        isLight 
                                            ? isCompleted ? 'text-emerald-700' : 'text-[#FF6B00]'
                                            : 'text-cyan-400'
                                    }`}>
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
                                className={`px-6 py-2.5 font-bold ${
                                    isLight ? 'text-black' : 'text-cyan-300 hover:text-cyan-100 hover:bg-cyan-950/20'
                                }`}
                                theme={theme}
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
