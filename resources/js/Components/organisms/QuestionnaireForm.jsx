// Organism form kuesioner satu pegawai dengan progress soal dan kontrol navigasi.
import { motion } from 'framer-motion';
import { useEffect, useRef, useState } from 'react';
import Badge from '../atoms/Badge';
import Button from '../atoms/Button';
import ProgressBar from '../atoms/ProgressBar';
import QuestionCard from '../molecules/QuestionCard';
import SaveBanner from '../molecules/SaveBanner';

export default function QuestionnaireForm({
    employee,
    selectedPeriod,
    questions,
    answers = {},
    employeeProgress = { completed: 0, total: 0, percentage: 0 },
    currentQuestionIndex = 0,
    onQuestionChange,
    onAnswerChange,
    onSubmit,
    onBack,
    onBackToPeriod,
    // Preview mode props
    isPreview = false,
    isResponseSubmitted = false,
    onPreviewAnswerChange,
    // SaveBanner props (di-pass dari parent)
    saveBanner = { show: false, savedName: '', nextName: '' },
    onSaveBannerDismiss,
    // AutoAdvance — dikontrol dari parent (Show.jsx) agar persist antar pegawai
    autoAdvance = false,
    onAutoAdvanceChange,
}) {
    const [questionDirection, setQuestionDirection] = useState(1);
    const [editingQuestionId, setEditingQuestionId] = useState(null);
    const [isSavingPreviewAnswer, setIsSavingPreviewAnswer] = useState(false);
    const totalQuestions = questions.length;
    const currentQuestion = questions[currentQuestionIndex];
    const answeredCount = questions.filter((question) => answers[question.id]).length;
    const questionProgress = totalQuestions > 0 ? (answeredCount / totalQuestions) * 100 : 0;
    const employeeProgressCompleted = employeeProgress.completed ?? 0;
    const employeeProgressTotal = employeeProgress.total ?? 0;
    const employeeProgressPercentage = employeeProgress.percentage ?? 0;
    const selectedScore = answers[currentQuestion.id];
    const isEditingCurrentQuestion = isPreview && editingQuestionId === currentQuestion.id;
    const isFirstQuestion = currentQuestionIndex === 0;
    const isLastQuestion = currentQuestionIndex === totalQuestions - 1;
    const autoAdvanceTimer = useRef(null);

    const clearAutoAdvanceTimer = () => {
        if (autoAdvanceTimer.current) {
            window.clearTimeout(autoAdvanceTimer.current);
            autoAdvanceTimer.current = null;
        }
    };

    useEffect(() => () => {
        clearAutoAdvanceTimer();
    }, []);

    useEffect(() => {
        if (!autoAdvance) {
            clearAutoAdvanceTimer();
        }
    }, [autoAdvance]);

    // Reset questionDirection saat berganti pegawai
    useEffect(() => {
        setQuestionDirection(1);
        setEditingQuestionId(null);
    }, [employee?.id]);

    useEffect(() => {
        setEditingQuestionId(null);
    }, [currentQuestion?.id]);

    const scheduleAutoAdvance = () => {
        if (isLastQuestion) {
            return;
        }

        clearAutoAdvanceTimer();
        autoAdvanceTimer.current = window.setTimeout(() => {
            setQuestionDirection(1);
            onQuestionChange?.(currentQuestionIndex + 1);
            autoAdvanceTimer.current = null;
        }, 500);
    };

    const goPrevious = () => {
        if (!isFirstQuestion) {
            clearAutoAdvanceTimer();
            setQuestionDirection(-1);
            onQuestionChange?.(currentQuestionIndex - 1);
        }
    };

    const goNext = () => {
        if (!isLastQuestion) {
            clearAutoAdvanceTimer();
            setQuestionDirection(1);
            onQuestionChange?.(currentQuestionIndex + 1);
        }
    };

    const handleBackToEmployee = () => {
        clearAutoAdvanceTimer();
        onBack?.();
    };

    const handleBackToPeriod = () => {
        clearAutoAdvanceTimer();
        onBackToPeriod?.();
    };

    const handlePreviewScoreSelect = async (questionId, score) => {
        if (isResponseSubmitted) return;
        if (editingQuestionId !== questionId || isSavingPreviewAnswer) return;

        setIsSavingPreviewAnswer(true);
        const saved = await onPreviewAnswerChange?.(questionId, score);
        setIsSavingPreviewAnswer(false);

        if (saved !== false) {
            setEditingQuestionId(null);
        }
    };

    const handleScoreSelect = async (score) => {
        const nextAnswers = {
            ...answers,
            [currentQuestion.id]: score,
        };
        const nextAllAnswered = questions.every((question) => nextAnswers[question.id]);

        onAnswerChange?.(currentQuestion.id, score);
        // Dismiss save banner saat user mulai memilih skor
        onSaveBannerDismiss?.();

        if (nextAllAnswered) {
            clearAutoAdvanceTimer();
            onSubmit?.(nextAnswers);
            return;
        }

        if (autoAdvance) {
            scheduleAutoAdvance();
        }
    };

    const handleAutoAdvanceToggle = () => {
        if (isPreview) return;
        const nextAutoAdvance = !autoAdvance;
        onAutoAdvanceChange?.(nextAutoAdvance);

        if (!nextAutoAdvance) {
            clearAutoAdvanceTimer();
            return;
        }

        if (selectedScore) {
            scheduleAutoAdvance();
        }
    };

    return (
        <main className="mx-auto w-full max-w-6xl px-4 py-0 sm:px-6 lg:px-8">
            {/* SaveBanner di atas form, slide down dari atas */}
            <SaveBanner
                show={saveBanner.show}
                savedName={saveBanner.savedName}
                nextName={saveBanner.nextName}
                onDismiss={onSaveBannerDismiss}
            />

            <div className="px-0 py-5 sm:py-6">
                {/* Preview mode banner */}
                {isPreview && (
                    <motion.div
                        initial={{ opacity: 0, y: -8 }}
                        animate={{ opacity: 1, y: 0 }}
                        transition={{ duration: 0.25 }}
                        className={[
                            'mb-5 rounded-lg border px-4 py-3',
                            isResponseSubmitted
                                ? 'border-cyan-500/30 bg-cyan-900/35'
                                : 'border-amber-500/30 bg-amber-900/50',
                        ].join(' ')}
                    >
                        <p className={[
                            'text-sm font-semibold leading-6',
                            isResponseSubmitted ? 'text-cyan-200' : 'text-amber-200',
                        ].join(' ')}>
                            {isResponseSubmitted
                                ? 'Mode Pratinjau - Penilaian ini sudah dikirim final (submitted)'
                                : 'Mode Pratinjau - Penilaian ini sudah tersimpan sebagai draft dan masih bisa diedit per soal'}
                        </p>
                    </motion.div>
                )}

                {/* Header: breadcrumb + info pegawai */}
                <div className="mb-5 flex flex-col gap-4 sm:mb-6 lg:flex-row lg:items-start lg:justify-between">
                    <div className="min-w-0">
                        <nav className="flex flex-wrap items-center gap-2 text-xs font-semibold text-slate-500 sm:text-sm">
                            <button
                                type="button"
                                onClick={handleBackToPeriod}
                                className="rounded-md text-cyan-300 outline-none transition-colors hover:text-cyan-100 focus-visible:ring-2 focus-visible:ring-cyan-400"
                            >
                                Kuesioner 360
                            </button>
                            <span>/</span>
                            <button
                                type="button"
                                onClick={handleBackToEmployee}
                                className="rounded-md text-cyan-300 outline-none transition-colors hover:text-cyan-100 focus-visible:ring-2 focus-visible:ring-cyan-400"
                            >
                                {selectedPeriod?.code}
                            </button>
                            <span>/</span>
                            <span className="text-slate-300">{employee.name}</span>
                        </nav>

                        <div className="mt-4 flex flex-col gap-3 sm:mt-5 sm:flex-row sm:items-center sm:gap-4">
                            <div className="flex h-16 w-16 shrink-0 items-center justify-center rounded-lg border border-white/10 bg-gradient-to-br from-purple-950 via-gray-900 to-cyan-950 text-4xl sm:h-20 sm:w-20 sm:text-5xl">
                                {employee.avatar}
                            </div>
                            <div className="min-w-0">
                                <h1 className="text-2xl font-black leading-tight text-slate-100 sm:text-3xl">
                                    {employee.name}
                                </h1>
                                <p className="mt-1 text-sm font-semibold leading-6 text-purple-200">
                                    {employee.position} - {employee.department}
                                </p>
                            </div>
                        </div>
                    </div>

                    <Button variant="secondary" className="w-full sm:w-auto" onClick={handleBackToEmployee}>
                        Kembali ke Daftar Pegawai
                    </Button>
                </div>

                {/* Progress pegawai, progress soal, dan toggle auto-advance */}
                <motion.section
                    initial={{ opacity: 0, y: 16 }}
                    animate={{ opacity: 1, y: 0 }}
                    transition={{ duration: 0.32, ease: 'easeOut' }}
                    className="mb-5 rounded-lg border border-purple-500/20 bg-gray-900 p-4 shadow-xl shadow-black/25 sm:p-5"
                >
                    <div className="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                        <div className="grid flex-1 gap-4 sm:grid-cols-2">
                            <div>
                                <p className="text-xs font-bold uppercase tracking-wide text-purple-300 sm:text-sm">
                                    Progress Pegawai
                                </p>
                                <p className="mt-1 text-sm text-slate-500">
                                    {employeeProgressCompleted} dari {employeeProgressTotal} pegawai dinilai ({Math.round(employeeProgressPercentage)}%)
                                </p>
                                <ProgressBar
                                    value={employeeProgressPercentage}
                                    showLabel={false}
                                    className="mt-3"
                                />
                            </div>

                            <div>
                                <p className="text-xs font-bold uppercase tracking-wide text-purple-300 sm:text-sm">
                                    Progress Soal
                                </p>
                                <p className="mt-1 text-sm text-slate-500">
                                    {answeredCount} dari {totalQuestions} soal terjawab ({Math.round(questionProgress)}%)
                                </p>
                                <ProgressBar
                                    value={questionProgress}
                                    showLabel={false}
                                    className="mt-3"
                                />
                            </div>
                        </div>

                        {/* Toggle auto-advance — disembunyikan di preview mode */}
                        {!isPreview && (
                            <motion.button
                                type="button"
                                onClick={handleAutoAdvanceToggle}
                                whileHover={{ scale: 1.02, y: -1 }}
                                whileTap={{ scale: 0.98 }}
                                className={[
                                    'flex w-full items-center justify-between gap-3 rounded-lg border px-4 py-2.5 text-left outline-none transition-colors focus-visible:ring-2 focus-visible:ring-cyan-400 focus-visible:ring-offset-2 focus-visible:ring-offset-gray-950 xl:w-auto',
                                    autoAdvance
                                        ? 'border-emerald-400/35 bg-emerald-500/10'
                                        : 'border-slate-600/40 bg-gray-950',
                                ].join(' ')}
                                aria-pressed={autoAdvance}
                            >
                                <span className="text-sm font-bold text-slate-300">
                                    Pilih otomatis lanjut
                                </span>
                                <Badge variant={autoAdvance ? 'success' : 'default'}>
                                    {autoAdvance ? 'ON' : 'OFF'}
                                </Badge>
                            </motion.button>
                        )}

                    </div>
                </motion.section>

                {/* Kartu pertanyaan — pass isPreview ke QuestionCard */}
                {isPreview ? (
                    <div className="space-y-6">
                        {questions.map((question, index) => {
                            const score = answers[question.id];
                            const isEditingThis = editingQuestionId === question.id;

                            return (
                                <div
                                    key={question.id}
                                    className="relative flex flex-col gap-3"
                                >
                                    <QuestionCard
                                        question={question}
                                        questionNumber={index + 1}
                                        totalQuestions={totalQuestions}
                                        selectedScore={score}
                                        onScoreSelect={(val) => handlePreviewScoreSelect(question.id, val)}
                                        isPreview={!isEditingThis}
                                    />

                                    {/* Tombol edit di bawah kartu pertanyaan jika draft */}
                                    {!isResponseSubmitted && (
                                        <div className="-mt-3 flex justify-end pr-4 pb-4 sm:pr-6 sm:pb-6 relative z-10">
                                            {isEditingThis ? (
                                                <Button
                                                    variant="secondary"
                                                    size="sm"
                                                    disabled={isSavingPreviewAnswer}
                                                    onClick={() => setEditingQuestionId(null)}
                                                >
                                                    Batal Edit
                                                </Button>
                                            ) : (
                                                <Button
                                                    variant="secondary"
                                                    size="sm"
                                                    onClick={() => setEditingQuestionId(question.id)}
                                                >
                                                    Edit Poin Soal Ini
                                                </Button>
                                            )}
                                        </div>
                                    )}
                                </div>
                            );
                        })}
                    </div>
                ) : (
                    <>
                        <QuestionCard
                            question={currentQuestion}
                            questionNumber={currentQuestionIndex + 1}
                            totalQuestions={totalQuestions}
                            selectedScore={selectedScore}
                            onScoreSelect={handleScoreSelect}
                            direction={questionDirection}
                            isPreview={false}
                        />

                        {/* Navigasi bawah */}
                        <div className="mt-5 flex flex-col gap-3 sm:mt-6 sm:flex-row sm:items-center sm:justify-between">
                            <Button
                                variant="ghost"
                                className="w-full sm:w-auto"
                                disabled={isFirstQuestion || isSavingPreviewAnswer}
                                onClick={goPrevious}
                            >
                                Sebelumnya
                            </Button>

                            <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-end">
                                {!isLastQuestion && (
                                    <Button
                                        variant="secondary"
                                        className="w-full sm:w-auto"
                                        disabled={!selectedScore || isSavingPreviewAnswer}
                                        onClick={goNext}
                                    >
                                        Selanjutnya
                                    </Button>
                                )}
                            </div>
                        </div>
                    </>
                )}
            </div>
        </main>
    );
}
