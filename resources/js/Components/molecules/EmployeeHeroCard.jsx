// Molecule kartu hero pegawai untuk memilih karyawan yang akan dinilai.
import { motion } from 'framer-motion';
import Badge from '../atoms/Badge';

export default function EmployeeHeroCard({ employee, isCompleted = false, onClick }) {
    const handleClick = () => {
        if (!isCompleted) {
            onClick?.(employee);
        }
    };

    return (
        <motion.button
            type="button"
            onClick={handleClick}
            disabled={isCompleted}
            whileHover={isCompleted ? undefined : { scale: 1.02, y: -2 }}
            whileTap={isCompleted ? undefined : { scale: 0.98 }}
            transition={{ type: 'spring', stiffness: 360, damping: 24 }}
            className={[
                'group relative min-h-[220px] overflow-hidden rounded-lg border bg-gray-900 p-4 text-left shadow-xl shadow-black/25 outline-none transition-all duration-200 focus-visible:ring-2 focus-visible:ring-cyan-400 focus-visible:ring-offset-2 focus-visible:ring-offset-gray-950 sm:min-h-[260px] sm:p-5',
                isCompleted
                    ? 'cursor-not-allowed border-emerald-500/25'
                    : 'cursor-pointer border-purple-500/20 hover:border-purple-400/70 hover:shadow-[0_0_30px_rgba(124,58,237,0.28)]',
            ].join(' ')}
        >
            <div className="absolute inset-x-0 top-0 h-24 bg-gradient-to-r from-purple-600/25 via-cyan-500/20 to-emerald-500/20" />

            <div className="relative flex h-full flex-col">
                <div className="flex flex-col items-start gap-3 sm:flex-row sm:justify-between">
                    <div className="flex h-16 w-16 items-center justify-center rounded-lg border border-white/10 bg-gradient-to-br from-gray-800 via-purple-950/60 to-cyan-950/60 text-4xl shadow-inner shadow-black/30 sm:h-24 sm:w-24 sm:text-5xl">
                        {employee.avatar}
                    </div>
                    <Badge variant={isCompleted ? 'success' : 'info'}>
                        {isCompleted ? 'Sudah Dinilai' : 'Belum Dinilai'}
                    </Badge>
                </div>

                <div className="mt-5 sm:mt-6">
                    <h3 className="text-lg font-black leading-tight text-slate-100 sm:text-xl">
                        {employee.name}
                    </h3>
                    <p className="mt-2 text-sm font-semibold text-purple-200">
                        {employee.position}
                    </p>
                    <p className="mt-1 text-sm text-slate-500">
                        {employee.department}
                    </p>
                </div>

                <div className="mt-auto pt-6">
                    <span className="inline-flex items-center text-sm font-bold text-cyan-300 transition-transform duration-200 group-hover:translate-x-1">
                        Mulai penilaian →
                    </span>
                </div>
            </div>

            {isCompleted && (
                <div className="absolute inset-0 flex flex-col items-center justify-center bg-gray-950/78 backdrop-blur-[1px]">
                    <div className="flex h-16 w-16 items-center justify-center rounded-full border border-emerald-300/40 bg-emerald-500/20 text-4xl font-black text-emerald-300 sm:h-20 sm:w-20 sm:text-5xl">
                        ✓
                    </div>
                    <p className="mt-4 text-sm font-bold uppercase tracking-wide text-emerald-200">
                        Penilaian Terkirim
                    </p>
                </div>
            )}
        </motion.button>
    );
}
