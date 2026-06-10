// Organism tracker progres penilaian pegawai dalam satu periode.
import ProgressBar from '../atoms/ProgressBar';

export default function ProgressTracker({
    employees,
    completedEmployees = [],
    overallProgress = 0,
    selectedPeriod,
}) {
    if (overallProgress >= 100) {
        return null;
    }

    const completedSet = new Set(completedEmployees);
    const completedList = employees.filter((employee) => completedSet.has(employee.id));

    return (
        <section className="rounded-lg border border-purple-500/20 bg-gray-900 p-4 shadow-xl shadow-black/25 sm:p-5">
            <div className="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div className="min-w-0">
                    <p className="text-xs font-bold uppercase tracking-wide text-purple-300 sm:text-sm">
                        Progress Penilaian {selectedPeriod?.title}
                    </p>
                    <p className="mt-2 text-lg font-black leading-snug text-slate-100 sm:text-2xl">
                        {completedEmployees.length} dari {employees.length} pegawai dinilai ({Math.round(overallProgress)}%)
                    </p>
                </div>

                <div className="flex min-h-11 flex-wrap items-center gap-2">
                    {completedList.length > 0 ? (
                        completedList.map((employee) => (
                            <div
                                key={employee.id}
                                title={employee.name}
                                className="flex h-10 w-10 items-center justify-center rounded-full border border-emerald-400/30 bg-emerald-500/15 text-lg shadow-lg shadow-black/20 sm:h-11 sm:w-11 sm:text-xl"
                            >
                                {employee.avatar}
                            </div>
                        ))
                    ) : (
                        <span className="text-sm font-semibold text-slate-500">
                            Belum ada pegawai dinilai
                        </span>
                    )}
                </div>
            </div>

            <div className="mt-5">
                <ProgressBar value={overallProgress} size="lg" />
            </div>
        </section>
    );
}
