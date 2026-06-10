// Molecule kartu pertanyaan kuis dengan navigasi skor 1 sampai 10.
import { AnimatePresence, motion } from 'framer-motion';
import ScoreButton from '../atoms/ScoreButton';

const questionVariants = {
    // next (direction >= 0): masuk dari bawah (+y), keluar ke atas (-y)
    // prev (direction < 0) : masuk dari atas (-y), keluar ke bawah (+y)
    enter: (direction) => ({
        y: direction >= 0 ? 44 : -44,
        opacity: 0,
    }),
    center: {
        y: 0,
        opacity: 1,
    },
    exit: (direction) => ({
        y: direction >= 0 ? -44 : 44,
        opacity: 0,
    }),
};

export default function QuestionCard({
    question,
    questionNumber,
    totalQuestions,
    selectedScore,
    onScoreSelect,
    direction = 1,
    isPreview = false,
    theme = 'dark',
}) {
    const isLight = theme === 'light';

    return (
        // overflow-hidden wajib agar soal benar-benar hilang saat keluar batas
        <div className="overflow-hidden rounded-lg">
            <AnimatePresence mode="wait" custom={direction}>
                <motion.article
                    key={question.id}
                    custom={direction}
                    variants={questionVariants}
                    initial="enter"
                    animate="center"
                    exit="exit"
                    transition={{ duration: 0.26, ease: [0.22, 1, 0.36, 1] }}
                    className={[
                        'relative p-4 shadow-2xl transition-colors duration-300 sm:p-6 lg:p-7',
                        isLight
                            ? 'border-4 border-black bg-white shadow-[6px_6px_0px_0px_#000] text-black'
                            : 'border border-purple-500/20 bg-gray-900 shadow-black/30'
                    ].join(' ')}
                >
                <div className="flex items-start justify-between gap-3">
                    <p className={[
                        'text-xs font-bold uppercase tracking-wide sm:text-sm',
                        isLight ? 'text-[#FF6B00]' : 'text-purple-300'
                    ].join(' ')}>
                        Pertanyaan
                    </p>
                    <div className={[
                        'shrink-0 text-xs font-black transition-all duration-300',
                        isLight
                            ? 'border-2 border-black bg-[#B2EBF2] px-3 py-1 text-black shadow-[2px_2px_0px_0px_#000] rounded-md'
                            : 'rounded-full border border-cyan-400/25 bg-cyan-500/10 px-3 py-1 text-cyan-200'
                    ].join(' ')}>
                        Soal {questionNumber} dari {totalQuestions}
                    </div>
                </div>

                <div className="mt-4 flex flex-col gap-2">
                    <h3 className={[
                        'text-base font-black sm:text-lg',
                        isLight ? 'text-black' : 'text-slate-100'
                    ].join(' ')}>
                        {question.judul}
                    </h3>
                    <div className={[
                        'max-h-[36vh] min-h-24 w-full overflow-y-auto whitespace-pre-wrap break-words pr-1 text-sm leading-7 sm:min-h-28 sm:text-base sm:leading-8 lg:max-h-60 scrollbar-thin',
                        isLight
                            ? 'text-gray-800 scrollbar-track-gray-100 scrollbar-thumb-black'
                            : 'text-slate-300 scrollbar-track-gray-900 scrollbar-thumb-purple-700'
                    ].join(' ')}>
                        {question.isi || question.text}
                    </div>
                </div>

                <div className={[
                    'mt-6 pt-5 sm:mt-8 sm:pt-6 border-t',
                    isLight ? 'border-black/15' : 'border-white/10'
                ].join(' ')}>
                    <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <p className={[
                            'text-sm font-bold',
                            isLight ? 'text-black' : 'text-slate-300'
                        ].join(' ')}>
                            Berikan Penilaian Anda:
                        </p>

                        <AnimatePresence mode="wait">
                            {selectedScore ? (
                                <motion.div
                                    key={selectedScore}
                                    initial={{ opacity: 0, y: 8, scale: 0.94 }}
                                    animate={{ opacity: 1, y: 0, scale: 1 }}
                                    exit={{ opacity: 0, y: -6, scale: 0.96 }}
                                    transition={{ duration: 0.22, ease: 'easeOut' }}
                                    className={[
                                        'inline-flex w-full items-center justify-center font-black sm:w-fit transition-all duration-300',
                                        isLight
                                            ? 'border-2 border-black bg-[#C8E6C9] px-3 py-1 text-xs text-black shadow-[2px_2px_0px_0px_#000] rounded-md'
                                            : 'rounded-full border border-cyan-400/35 bg-cyan-500/15 px-3 py-1.5 text-sm text-cyan-200 shadow-[0_0_18px_rgba(6,182,212,0.18)]'
                                    ].join(' ')}
                                >
                                    Poin {selectedScore} terpilih
                                </motion.div>
                            ) : (
                                isPreview && (
                                    <motion.div
                                        key="no-score"
                                        initial={{ opacity: 0, y: 8, scale: 0.94 }}
                                        animate={{ opacity: 1, y: 0, scale: 1 }}
                                        exit={{ opacity: 0, y: -6, scale: 0.96 }}
                                        transition={{ duration: 0.22, ease: 'easeOut' }}
                                        className={[
                                            'inline-flex w-full items-center justify-center font-bold sm:w-fit transition-all duration-300',
                                            isLight
                                                ? 'border-2 border-black bg-[#FFCDD2] px-3 py-1 text-xs text-black shadow-[2px_2px_0px_0px_#000] rounded-md'
                                                : 'rounded-full border border-amber-500/35 bg-amber-500/10 px-3 py-1.5 text-sm text-amber-400 shadow-[0_0_18px_rgba(245,158,11,0.08)]'
                                        ].join(' ')}
                                    >
                                        ⚠️ Pertanyaan ini tidak memiliki poin
                                    </motion.div>
                                )
                            )}
                        </AnimatePresence>
                    </div>

                    <div className="mt-5 overflow-visible py-2 sm:px-1 sm:py-3">
                        <div className="mx-auto grid max-w-sm grid-cols-5 items-center justify-items-center gap-2 sm:max-w-none sm:grid-cols-10 sm:gap-2.5 md:gap-3 lg:gap-4">
                            {Array.from({ length: 10 }, (_, index) => {
                                const value = index + 1;

                                return (
                                    <div key={value} className="flex min-w-0 justify-center">
                                        <ScoreButton
                                            value={value}
                                            isSelected={selectedScore === value}
                                            onClick={onScoreSelect}
                                            isPreview={isPreview}
                                            theme={theme}
                                        />
                                    </div>
                                );
                            })}
                        </div>
                    </div>

                    <div className={[
                        'mt-3 flex items-center justify-between gap-4 text-xs font-bold sm:mt-4',
                        isLight ? 'text-black/75 uppercase tracking-wider' : 'text-slate-500'
                    ].join(' ')}>
                        <span>Sangat Kurang</span>
                        <span>Sangat Baik</span>
                    </div>
                </div>
                </motion.article>
            </AnimatePresence>
        </div>
    );
}
