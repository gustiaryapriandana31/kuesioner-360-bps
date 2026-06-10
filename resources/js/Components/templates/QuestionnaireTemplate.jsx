// Template halaman pengisian kuesioner untuk satu pegawai.
import { motion } from 'framer-motion';
import QuestionnaireForm from '../organisms/QuestionnaireForm';

export default function QuestionnaireTemplate({
    employee,
    selectedPeriod,
    questions,
    answers,
    employeeProgress,
    currentQuestionIndex,
    onQuestionChange,
    onAnswerChange,
    onSubmit,
    onBack,
    onBackToPeriod,
    isPreview = false,
    isResponseSubmitted = false,
    onPreviewAnswerChange,
    saveBanner,
    onSaveBannerDismiss,
    autoAdvance = false,
    onAutoAdvanceChange,
}) {
    return (
        <motion.div
            initial={{ opacity: 0, scale: 0.98, y: 12 }}
            animate={{ opacity: 1, scale: 1, y: 0 }}
            exit={{ opacity: 0, scale: 0.98, y: -8 }}
            transition={{ duration: 0.3, ease: [0.22, 1, 0.36, 1] }}
            className="min-h-svh bg-gray-950 text-slate-200"
        >
            <QuestionnaireForm
                employee={employee}
                selectedPeriod={selectedPeriod}
                questions={questions}
                answers={answers}
                employeeProgress={employeeProgress}
                currentQuestionIndex={currentQuestionIndex}
                onQuestionChange={onQuestionChange}
                onAnswerChange={onAnswerChange}
                onSubmit={onSubmit}
                onBack={onBack}
                onBackToPeriod={onBackToPeriod}
                isPreview={isPreview}
                isResponseSubmitted={isResponseSubmitted}
                onPreviewAnswerChange={onPreviewAnswerChange}
                saveBanner={saveBanner}
                onSaveBannerDismiss={onSaveBannerDismiss}
                autoAdvance={autoAdvance}
                onAutoAdvanceChange={onAutoAdvanceChange}
            />
        </motion.div>
    );
}
