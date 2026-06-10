// Molecule kartu periode kuesioner dengan status aktif, selesai, dan belum tersedia.
import { motion } from 'framer-motion';
import Badge from '../atoms/Badge';

function statusConfig(status, isCompleted) {
    if (isCompleted) {
        return {
            badge: 'SELESAI',
            badgeVariant: 'info',
            card: 'border-cyan-500/40 bg-cyan-950/15',
            code: 'text-cyan-200',
        };
    }

    if (status === 'active') {
        return {
            badge: 'AKTIF - Bulan Ini',
            badgeVariant: 'success',
            card: 'border-purple-500 bg-gray-900 shadow-[0_0_34px_rgba(124,58,237,0.32)]',
            code: 'text-cyan-200',
        };
    }

    if (status === 'completed') {
        return {
            badge: 'Selesai ✓',
            badgeVariant: 'success',
            card: 'border-emerald-500/40 bg-emerald-950/15',
            code: 'text-emerald-200',
        };
    }

    return {
        badge: 'Belum Tersedia',
        badgeVariant: 'default',
        card: 'border-slate-700/60 bg-gray-900 opacity-50 blur-[0.25px]',
        code: 'text-slate-400',
    };
}

export default function KuesionerPeriodCard({ period, isCompleted = false, progressData, onClick }) {
    const isUnavailable = period.status === 'unavailable';
    const isActive = period.status === 'active';
    const config = statusConfig(period.status, isCompleted);
    const progressPercent = Math.min(100, Math.max(0, Number(progressData?.percent) || 0));
    const completedCount = progressData?.completedCount ?? 0;
    const totalEmployees = progressData?.totalEmployees ?? 0;

    const handleClick = () => {
        if (!isUnavailable) {
            onClick?.(period);
        }
    };

    return (
        <motion.button
            type="button"
            title={isUnavailable ? 'Kuesioner belum diupload oleh admin' : period.title}
            onClick={handleClick}
            disabled={isUnavailable}
            whileHover={isUnavailable ? undefined : { scale: 1.018, y: -2 }}
            whileTap={isUnavailable ? undefined : { scale: 0.98 }}
            animate={isActive ? { boxShadow: ['0 0 18px rgba(124,58,237,0.25)', '0 0 36px rgba(6,182,212,0.34)', '0 0 18px rgba(124,58,237,0.25)'] } : undefined}
            transition={isActive ? { boxShadow: { duration: 1.8, repeat: Infinity, ease: 'easeInOut' } } : { type: 'spring', stiffness: 360, damping: 24 }}
            className={[
                'relative min-h-[218px] overflow-hidden rounded-lg border p-4 text-left outline-none transition-colors duration-200 focus-visible:ring-2 focus-visible:ring-cyan-400 focus-visible:ring-offset-2 focus-visible:ring-offset-gray-950 sm:min-h-[230px] sm:p-5',
                config.card,
                isUnavailable ? 'cursor-not-allowed' : 'cursor-pointer hover:border-cyan-400/55',
            ].join(' ')}
        >
            <div className="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-purple-600 via-violet-500 to-cyan-400" />

            {isCompleted && (
                <div className="absolute right-3 top-3 flex h-8 w-8 items-center justify-center rounded-full bg-cyan-500/20 text-lg font-black text-cyan-300 sm:right-4 sm:top-4">
                    ✓
                </div>
            )}

            {isUnavailable && (
                <div className="absolute inset-0 z-10 flex items-center justify-center bg-gray-950/45">
                    <div className="rounded-full border border-slate-500/30 bg-gray-900/95 px-4 py-3 text-2xl text-slate-300 shadow-xl shadow-black/30 sm:text-3xl">
                        🔒
                    </div>
                </div>
            )}

            <div className={isUnavailable ? 'relative blur-[0.35px]' : 'relative'}>
                <div className="flex flex-col items-start gap-3 pr-0 sm:flex-row sm:justify-between sm:gap-4 sm:pr-8">
                    <div>
                        <div className={['text-3xl font-black leading-none sm:text-4xl lg:text-5xl', config.code].join(' ')}>
                            {period.code}
                        </div>
                        <h3 className="mt-3 text-base font-black leading-snug text-slate-100 sm:mt-4 sm:text-lg">
                            {period.title}
                        </h3>
                    </div>
                    <Badge variant={config.badgeVariant} pulse={isActive}>
                        {config.badge}
                    </Badge>
                </div>

                <p className="mt-3 text-sm font-semibold text-slate-300">
                    {period.month}
                </p>
                <p className="mt-2 min-h-10 text-sm leading-6 text-slate-500">
                    {period.description}
                </p>

                {!isUnavailable && (
                    <div className="mt-6">
                        <div className="flex items-center justify-between text-xs font-bold text-slate-500">
                            <span>{isActive ? 'Progress bulan ini' : 'Progress periode'}</span>
                            <span>
                                {completedCount}/{totalEmployees} ({Math.round(progressPercent)}%)
                            </span>
                        </div>
                        <div className="mt-2 h-2 overflow-hidden rounded-full bg-gray-800">
                            <motion.div
                                initial={{ width: 0 }}
                                animate={{ width: `${progressPercent}%` }}
                                transition={{ duration: 0.5, ease: 'easeOut' }}
                                className="h-full rounded-full bg-gradient-to-r from-purple-600 to-cyan-400"
                            />
                        </div>
                    </div>
                )}

                {!isUnavailable && (
                    <div className="mt-5 flex items-center justify-between gap-4">
                        <span className="text-xs font-bold uppercase tracking-wide text-slate-500">
                            {isCompleted ? 'Semua pegawai selesai' : 'Buka periode'}
                        </span>
                        <span className="flex h-9 w-9 items-center justify-center rounded-full bg-cyan-500/15 text-lg font-black text-cyan-200">
                            →
                        </span>
                    </div>
                )}
            </div>
        </motion.button>
    );
}
