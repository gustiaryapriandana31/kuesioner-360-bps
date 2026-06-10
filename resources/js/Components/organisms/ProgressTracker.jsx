// Organism tracker progres penilaian pegawai dalam satu periode.
import ProgressBar from '../atoms/ProgressBar';

export default function ProgressTracker({
    employees,
    completedEmployees = [],
    overallProgress = 0,
    selectedPeriod,
    theme = 'dark',
}) {
    if (overallProgress >= 100) {
        return null;
    }

    const completedSet = new Set(completedEmployees);
    const completedList = employees.filter((employee) => completedSet.has(employee.id));
    const isLight = theme === 'light';

    return (
        <section
            className={`p-4 sm:p-5 transition-all ${
                isLight
                    ? 'border-4 border-black bg-white shadow-[4px_4px_0px_0px_#000]'
                    : 'rounded-lg border border-purple-500/20 bg-gray-900 shadow-xl shadow-black/25'
            }`}
        >
            <div className="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div className="min-w-0">
                    <p
                        className={`text-xs font-black uppercase tracking-wide sm:text-sm ${
                            isLight ? 'text-[#FF6B00]' : 'text-purple-300'
                        }`}
                    >
                        Progress Penilaian {selectedPeriod?.title}
                    </p>
                    <p
                        className={`mt-2 text-lg font-black leading-snug sm:text-2xl ${
                            isLight ? 'text-black' : 'text-slate-100'
                        }`}
                    >
                        {completedEmployees.length} dari {employees.length} pegawai dinilai ({Math.round(overallProgress)}%)
                    </p>
                </div>

                <div className="flex min-h-11 flex-wrap items-center gap-2">
                    {completedList.length > 0 ? (
                        completedList.map((employee) => (
                            <div
                                key={employee.id}
                                title={employee.name}
                                className={`flex h-10 w-10 items-center justify-center text-lg sm:h-11 sm:w-11 sm:text-xl transition-all ${
                                    isLight
                                        ? 'rounded-full border-2 border-black bg-[#C8E6C9] text-black shadow-[2px_2px_0px_0px_#000]'
                                        : 'rounded-full border border-emerald-400/30 bg-emerald-500/15 shadow-lg shadow-black/20'
                                }`}
                            >
                                {employee.avatar}
                            </div>
                        ))
                    ) : (
                        <span className={`text-sm font-semibold ${isLight ? 'text-gray-500' : 'text-slate-500'}`}>
                            Belum ada pegawai dinilai
                        </span>
                    )}
                </div>
            </div>

            <div className="mt-5">
                <ProgressBar value={overallProgress} size="lg" theme={theme} />
            </div>
        </section>
    );
}
