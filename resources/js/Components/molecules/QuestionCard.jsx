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
}) {
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
                    className="relative border border-purple-500/20 bg-gray-900 p-4 shadow-2xl shadow-black/30 sm:p-6 lg:p-7"
                >
                <div className="flex items-start justify-between gap-3">
                    <p className="text-xs font-bold uppercase tracking-wide text-purple-300 sm:text-sm">
                        Pertanyaan
                    </p>
                    <div className="shrink-0 rounded-full border border-cyan-400/25 bg-cyan-500/10 px-3 py-1 text-xs font-bold text-cyan-200">
                        Soal {questionNumber} dari {totalQuestions}
                    </div>
                </div>

                <div className="mt-4 flex flex-col gap-2">
                    <h3 className="text-base font-black text-slate-100 sm:text-lg">
                        {question.judul}
                    </h3>
                    <div className="max-h-[36vh] min-h-24 w-full overflow-y-auto whitespace-pre-wrap break-words pr-1 text-sm leading-7 text-slate-300 scrollbar-thin scrollbar-track-gray-900 scrollbar-thumb-purple-700 sm:min-h-28 sm:text-base sm:leading-8 lg:max-h-60">
                        {question.isi || question.text}
                    </div>
                </div>

                <div className="mt-6 border-t border-white/10 pt-5 sm:mt-8 sm:pt-6">
                    <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <p className="text-sm font-bold text-slate-300">
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
                                    className="inline-flex w-full items-center justify-center rounded-full border border-cyan-400/35 bg-cyan-500/15 px-3 py-1.5 text-sm font-black text-cyan-200 shadow-[0_0_18px_rgba(6,182,212,0.18)] sm:w-fit"
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
                                        className="inline-flex w-full items-center justify-center rounded-full border border-amber-500/35 bg-amber-500/10 px-3 py-1.5 text-sm font-bold text-amber-400 shadow-[0_0_18px_rgba(245,158,11,0.08)] sm:w-fit"
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
                                        />
                                    </div>
                                );
                            })}
                        </div>
                    </div>

                    <div className="mt-3 flex items-center justify-between gap-4 text-xs font-semibold text-slate-500 sm:mt-4">
                        <span>Sangat Kurang</span>
                        <span>Sangat Baik</span>
                    </div>
                </div>
                </motion.article>
            </AnimatePresence>
        </div>
    );
}
