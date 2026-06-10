// Molecule kartu periode kuesioner dengan status aktif, selesai, dan belum tersedia.
import { motion } from 'framer-motion';
import Badge from '../atoms/Badge';

function statusConfig(status, isCompleted, isLight) {
    if (isLight) {
        if (isCompleted) {
            return {
                badge: 'SELESAI',
                badgeVariant: 'info',
                card: 'border-4 border-black bg-[#E8F5E9] shadow-[4px_4px_0px_0px_#000] hover:bg-[#C8E6C9] text-black',
                code: 'text-black',
                title: 'text-black font-black',
                desc: 'text-gray-700 font-semibold',
                progressText: 'text-gray-700 font-bold',
                arrowBg: 'bg-black/10 text-black',
                footerText: 'text-gray-800 font-bold',
            };
        }

        if (status === 'active') {
            return {
                badge: 'AKTIF - Bulan Ini',
                badgeVariant: 'success',
                card: 'border-4 border-black bg-[#FFE082] shadow-[6px_6px_0px_0px_#000] hover:bg-[#FFD54F] text-black',
                code: 'text-[#FF6B00] font-black',
                title: 'text-black font-black',
                desc: 'text-gray-800 font-bold',
                progressText: 'text-gray-800 font-bold',
                arrowBg: 'bg-black text-[#FFE082]',
                footerText: 'text-black font-black',
            };
        }

        if (status === 'completed') {
            return {
                badge: 'Selesai ✓',
                badgeVariant: 'success',
                card: 'border-4 border-black bg-[#C8E6C9] shadow-[4px_4px_0px_0px_#000] hover:bg-[#A5D6A7] text-black',
                code: 'text-black',
                title: 'text-black font-black',
                desc: 'text-gray-700 font-semibold',
                progressText: 'text-gray-700 font-bold',
                arrowBg: 'bg-black/10 text-black',
                footerText: 'text-gray-800 font-bold',
            };
        }

        return {
            badge: 'Belum Tersedia',
            badgeVariant: 'default',
            card: 'border-2 border-black/40 bg-gray-100 opacity-60 text-gray-500',
            code: 'text-gray-500 font-black',
            title: 'text-gray-500 font-black',
            desc: 'text-gray-400 font-semibold',
            progressText: 'text-gray-400 font-bold',
            arrowBg: 'bg-black/5 text-gray-400',
            footerText: 'text-gray-400 font-bold',
        };
    }

    if (isCompleted) {
        return {
            badge: 'SELESAI',
            badgeVariant: 'info',
            card: 'border-cyan-500/40 bg-cyan-950/15 text-slate-200',
            code: 'text-cyan-200',
            title: 'text-slate-100',
            desc: 'text-slate-500',
            progressText: 'text-slate-500',
            arrowBg: 'bg-cyan-500/15 text-cyan-200',
            footerText: 'text-slate-500',
        };
    }

    if (status === 'active') {
        return {
            badge: 'AKTIF - Bulan Ini',
            badgeVariant: 'success',
            card: 'border-purple-500 bg-gray-900 shadow-[0_0_34px_rgba(124,58,237,0.32)] text-slate-200',
            code: 'text-cyan-200',
            title: 'text-slate-100',
            desc: 'text-slate-500',
            progressText: 'text-slate-500',
            arrowBg: 'bg-cyan-500/15 text-cyan-200',
            footerText: 'text-slate-500',
        };
    }

    if (status === 'completed') {
        return {
            badge: 'Selesai ✓',
            badgeVariant: 'success',
            card: 'border-emerald-500/40 bg-emerald-950/15 text-slate-200',
            code: 'text-emerald-200',
            title: 'text-slate-100',
            desc: 'text-slate-500',
            progressText: 'text-slate-500',
            arrowBg: 'bg-cyan-500/15 text-cyan-200',
            footerText: 'text-slate-500',
        };
    }

    return {
        badge: 'Belum Tersedia',
        badgeVariant: 'default',
        card: 'border-slate-700/60 bg-gray-900 opacity-50 blur-[0.25px] text-slate-400',
        code: 'text-slate-400',
        title: 'text-slate-500',
        desc: 'text-slate-500',
        progressText: 'text-slate-500',
        arrowBg: 'bg-cyan-500/15 text-cyan-200',
        footerText: 'text-slate-500',
    };
}

export default function KuesionerPeriodCard({ period, isCompleted = false, progressData, onClick, theme = 'dark' }) {
    const isUnavailable = period.status === 'unavailable';
    const isActive = period.status === 'active';
    const isLight = theme === 'light';
    const config = statusConfig(period.status, isCompleted, isLight);
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
            animate={(isActive && !isLight) ? { boxShadow: ['0 0 18px rgba(124,58,237,0.25)', '0 0 36px rgba(6,182,212,0.34)', '0 0 18px rgba(124,58,237,0.25)'] } : undefined}
            transition={isActive ? { boxShadow: { duration: 1.8, repeat: Infinity, ease: 'easeInOut' } } : { type: 'spring', stiffness: 360, damping: 24 }}
            className={[
                'relative min-h-[218px] overflow-hidden p-4 text-left outline-none transition-all duration-200 sm:min-h-[230px] sm:p-5',
                isLight 
                    ? 'rounded-none' 
                    : 'rounded-lg border focus-visible:ring-cyan-400 focus-visible:ring-offset-2 focus-visible:ring-offset-gray-950',
                isLight
                    ? 'focus-visible:ring-2 focus-visible:ring-black focus-visible:ring-offset-2 focus-visible:ring-offset-white'
                    : '',
                config.card,
                isUnavailable ? 'cursor-not-allowed' : 'cursor-pointer hover:border-black',
            ].join(' ')}
        >
            {!isLight && (
                <div className="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-purple-600 via-violet-500 to-cyan-400" />
            )}

            {isCompleted && (
                <div className={`absolute right-3 top-3 flex h-8 w-8 items-center justify-center rounded-full text-lg font-black sm:right-4 sm:top-4 ${
                    isLight ? 'bg-black text-white border-2 border-black' : 'bg-cyan-500/20 text-cyan-300'
                }`}>
                    ✓
                </div>
            )}

            {isUnavailable && (
                <div className={`absolute inset-0 z-10 flex items-center justify-center ${isLight ? 'bg-white/45' : 'bg-gray-950/45'}`}>
                    <div className={`rounded-full border px-4 py-3 text-2xl shadow-xl sm:text-3xl ${
                        isLight ? 'border-black bg-white shadow-black/25' : 'border-slate-500/30 bg-gray-900/95 shadow-black/30'
                    }`}>
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
                        <h3 className={`mt-3 text-base font-black leading-snug sm:mt-4 sm:text-lg ${config.title}`}>
                            {period.title}
                        </h3>
                    </div>
                    <Badge variant={config.badgeVariant} pulse={isActive && !isLight} theme={theme}>
                        {config.badge}
                    </Badge>
                </div>

                <p className={`mt-3 text-sm font-bold ${isLight ? 'text-gray-800' : 'text-slate-300'}`}>
                    {period.month}
                </p>
                <p className={`mt-2 min-h-10 text-sm leading-6 ${config.desc}`}>
                    {period.description}
                </p>

                {!isUnavailable && (
                    <div className="mt-6">
                        <div className={`flex items-center justify-between text-xs font-bold ${config.progressText}`}>
                            <span>{isActive ? 'Progress bulan ini' : 'Progress periode'}</span>
                            <span>
                                {completedCount}/{totalEmployees} ({Math.round(progressPercent)}%)
                            </span>
                        </div>
                        <div className={`mt-2 h-2.5 overflow-hidden rounded-full ${isLight ? 'bg-white border-2 border-black' : 'bg-gray-800'}`}>
                            <motion.div
                                initial={{ width: 0 }}
                                animate={{ width: `${progressPercent}%` }}
                                transition={{ duration: 0.5, ease: 'easeOut' }}
                                className={`h-full rounded-full ${isLight ? 'bg-[#FF6B00]' : 'bg-gradient-to-r from-purple-600 to-cyan-400'}`}
                            />
                        </div>
                    </div>
                )}

                {!isUnavailable && (
                    <div className="mt-5 flex items-center justify-between gap-4">
                        <span className={`text-xs font-black uppercase tracking-wide ${config.footerText}`}>
                            {isCompleted ? 'Semua pegawai selesai' : 'Buka periode'}
                        </span>
                        <span className={`flex h-9 w-9 items-center justify-center rounded-full text-lg font-black ${
                            isLight ? 'bg-black text-[#FFFDF6] border-2 border-black shadow-[2px_2px_0px_0px_#000]' : 'bg-cyan-500/15 text-cyan-200'
                        }`}>
                            →
                        </span>
                    </div>
                )}
            </div>
        </motion.button>
    );
}
