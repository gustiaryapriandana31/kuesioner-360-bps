// Page detail Kuesioner 360 untuk memilih pegawai dan mengirim jawaban ke API.
import axios from 'axios';
import { Head, router, usePage } from '@inertiajs/react';
import { AnimatePresence, motion } from 'framer-motion';
import { useCallback, useEffect, useMemo, useState } from 'react';
import Button from '../../Components/atoms/Button';
import AllDraftCompleteScreen from '../../Components/organisms/AllDraftCompleteScreen';
import NextEmployeeModal from '../../Components/molecules/NextEmployeeModal';
import NotificationToast from '../../Components/molecules/NotificationToast';
import TransitionOverlay from '../../Components/molecules/TransitionOverlay';
import QuestionnaireTemplate from '../../Components/templates/QuestionnaireTemplate';
import SelectEmployeeTemplate from '../../Components/templates/SelectEmployeeTemplate';

export default function Show({
    kuesioner,
    employees = [],
    // completedEmployees = target_id[] yang sudah SUBMITTED
    completedEmployees: initialCompletedEmployees = [],
    // completedDrafts = target_id[] yang sudah DRAFT atau SUBMITTED (semua yang sudah dinilai)
    completedDrafts: initialCompletedDrafts = [],
    allDraftsComplete: initialAllDraftsComplete = false,
    progress: initialProgress = { completed: 0, total: 0, percentage: 0 },
    initialEmployee = null,
}) {
    const { props } = usePage();
    const csrfToken = props.csrf_token;

    // ─── State yang sudah ada ────────────────────────────────────────────────
    const [currentView, setCurrentView] = useState(initialEmployee ? 'questionnaire' : 'hero-select');
    const [selectedEmployee, setSelectedEmployee] = useState(initialEmployee);
    const [answers, setAnswers] = useState({});
    const [completedEmployees, setCompletedEmployees] = useState(initialCompletedEmployees);
    const [progress, setProgress] = useState(initialProgress);
    const [currentQuestionIndex, setCurrentQuestionIndex] = useState(0);
    const [showToast, setShowToast] = useState(false);
    const [toastMessage, setToastMessage] = useState('');
    const [nextEmployeePrompt, setNextEmployeePrompt] = useState(null);

    // ─── State baru ──────────────────────────────────────────────────────────
    // Daftar pegawai yang sudah punya draft/submitted (inisialisasi dari server)
    const [completedDrafts, setCompletedDrafts] = useState(initialCompletedDrafts);
    const [allDraftsComplete, setAllDraftsComplete] = useState(initialAllDraftsComplete || kuesioner.status === 'completed');
    // Overlay simpan antar pegawai
    const [isSaving, setIsSaving] = useState(false);
    const [savingName, setSavingName] = useState('');
    const [savingNextName, setSavingNextName] = useState('');
    // Banner flash setelah simpan berhasil
    const [saveBanner, setSaveBanner] = useState({ show: false, savedName: '', nextName: '' });
    // Preview mode: true jika pegawai yang dibuka sudah ada draft-nya
    const [isPreview, setIsPreview] = useState(false);
    // AutoAdvance: persist ke sessionStorage agar tidak reset saat pindah pegawai
    const [autoAdvance, setAutoAdvance] = useState(() => {
        try { return sessionStorage.getItem('kuesioner:autoAdvance') === 'true'; }
        catch { return false; }
    });

    const questions = kuesioner?.questions ?? [];
    const employeeProgress = useMemo(() => {
        const total = employees.length;
        const completed = completedDrafts.length;

        return {
            completed,
            total,
            percentage: total > 0 ? (completed / total) * 100 : 0,
        };
    }, [completedDrafts.length, employees.length]);
    const isAllSubmitted = (employees.length > 0 && completedEmployees.length >= employees.length) || kuesioner.status === 'completed';

    // ─── Handlers & Loaders ──────────────────────────────────────────────────
    const handleToastDismiss = useCallback(() => {
        setShowToast(false);
    }, []);

    const handleSaveBannerDismiss = useCallback(() => {
        setSaveBanner((prev) => ({ ...prev, show: false }));
    }, []);

    const handleAutoAdvanceChange = useCallback((value) => {
        setAutoAdvance(value);
        try { sessionStorage.setItem('kuesioner:autoAdvance', String(value)); }
        catch { /* silent */ }
    }, []);

    const handleBackToPeriod = () => {
        router.visit('/kuesioner');
    };

    const loadSavedAnswers = async (employee) => {
        if (!employee?.id) return false;

        const existingAnswers = answers[employee.id] ?? {};
        if (Object.keys(existingAnswers).length > 0) {
            return true;
        }

        try {
            const response = await axios.get('/api/responses/answers', {
                params: {
                    kuesioner_id: kuesioner.id,
                    target_id: employee.id,
                },
                headers: { 'X-CSRF-TOKEN': csrfToken },
            });

            if (response.data.success) {
                setAnswers((prev) => ({
                    ...prev,
                    [employee.id]: response.data.data.answers ?? {},
                }));

                return true;
            }
        } catch (error) {
            // Jika 404 (tidak ada jawaban), jangan tampilkan toast error jika kuesioner selesai/closed
            if (error.response?.status === 404 && kuesioner.status === 'completed') {
                return true;
            }
            const message = error.response?.data?.message ?? 'Gagal memuat jawaban tersimpan.';
            setToastMessage(message);
            setShowToast(true);
        }

        return false;
    };

    // ─── Sinkronkan state saat navigasi Inertia ──────────────────────────────
    useEffect(() => {
        if (initialEmployee) {
            setSelectedEmployee(initialEmployee);
            setCurrentView('questionnaire');
            setCurrentQuestionIndex(0);
            // Cek apakah pegawai ini sudah punya draft ATAU kuesioner sudah selesai → preview mode
            setIsPreview(initialCompletedDrafts.includes(initialEmployee.id) || kuesioner.status === 'completed');
            loadSavedAnswers(initialEmployee);
        } else {
            setSelectedEmployee(null);
            setCurrentView('hero-select');
            setIsPreview(false);
        }
    }, [initialEmployee?.id]);

    // Sync allDraftsComplete dari server (saat page refresh)
    useEffect(() => {
        setAllDraftsComplete(initialAllDraftsComplete || kuesioner.status === 'completed');
        setCompletedDrafts(initialCompletedDrafts);
    }, [initialAllDraftsComplete]);

    // ─── Derived ─────────────────────────────────────────────────────────────
    const currentEmployeeAnswers = useMemo(() => {
        if (!selectedEmployee) return {};
        return answers[selectedEmployee.id] ?? {};
    }, [answers, selectedEmployee]);
    const isSelectedEmployeeSubmitted = selectedEmployee
        ? completedEmployees.includes(selectedEmployee.id) || kuesioner.status === 'completed'
        : false;

    const handleSelectEmployee = async (employee) => {
        // Dari AllDraftCompleteScreen atau HeroGrid — bisa preview jika sudah draft atau kuesioner selesai
        const alreadyDraft = completedDrafts.includes(employee.id);

        if (alreadyDraft || kuesioner.status === 'completed') {
            const answersLoaded = await loadSavedAnswers(employee);
            if (!answersLoaded && alreadyDraft) return;

            // Masuk preview mode langsung tanpa navigasi URL
            setSelectedEmployee(employee);
            setCurrentView('questionnaire');
            setCurrentQuestionIndex(0);
            setIsPreview(true);
            return;
        }

        // Navigasi ke URL dinamis untuk pegawai baru
        router.visit(`/kuesioner/${kuesioner.kode}/${employee.nama_slug}`, {
            preserveState: false,
            replace: false,
        });
    };

    const handleBackToEmployee = () => {
        setNextEmployeePrompt(null);
        setSelectedEmployee(null);
        setCurrentView('hero-select');
        setCurrentQuestionIndex(0);
        setIsPreview(false);
        setSaveBanner({ show: false, savedName: '', nextName: '' });
        window.history.pushState({}, '', `/kuesioner/${kuesioner.kode}`);
    };

    const handleContinueToNextEmployee = () => {
        if (!nextEmployeePrompt) return;

        router.visit(`/kuesioner/${kuesioner.kode}/${nextEmployeePrompt.nama_slug}`, {
            preserveState: false,
            replace: false,
        });
        setNextEmployeePrompt(null);
    };

    const handleAnswerChange = (questionId, score) => {
        if (!selectedEmployee) return;

        setAnswers((prev) => ({
            ...prev,
            [selectedEmployee.id]: {
                ...(prev[selectedEmployee.id] ?? {}),
                [questionId]: score,
            },
        }));
    };

    const handlePreviewAnswerChange = async (questionId, score) => {
        if (!selectedEmployee) return false;
        if (completedEmployees.includes(selectedEmployee.id)) return false;

        try {
            await axios.patch('/api/responses/answer', {
                kuesioner_id: kuesioner.id,
                target_id: selectedEmployee.id,
                pertanyaan_id: questionId,
                nilai: score,
            }, {
                headers: { 'X-CSRF-TOKEN': csrfToken },
            });

            setAnswers((prev) => ({
                ...prev,
                [selectedEmployee.id]: {
                    ...(prev[selectedEmployee.id] ?? {}),
                    [questionId]: score,
                },
            }));

            setToastMessage('Poin soal berhasil diperbarui.');
            setShowToast(true);

            return true;
        } catch (error) {
            const message = error.response?.data?.message ?? 'Gagal memperbarui poin soal.';
            setToastMessage(message);
            setShowToast(true);
        }

        return false;
    };

    const handleSubmit = async (answerOverride = null) => {
        if (!selectedEmployee) return;

        const employeeAnswers = answerOverride ?? answers[selectedEmployee.id] ?? {};
        const isComplete = questions.every((q) => employeeAnswers[q.id]);

        if (!isComplete) {
            setToastMessage('Lengkapi seluruh soal sebelum mengirim nilai.');
            setShowToast(true);
            return;
        }

        const savedEmployee = selectedEmployee;

        // Tampilkan overlay simpan — reset nextName dulu agar tidak tampil nilai stale
        setIsSaving(true);
        setSavingName(savedEmployee.name);
        setSavingNextName(''); // dikosongkan, diisi setelah axios selesai

        try {
            // OPTIMASI: single round-trip — buat response + batch upsert jawaban + submit
            await axios.post('/api/responses/quick-submit', {
                kuesioner_id: kuesioner.id,
                target_id: savedEmployee.id,
                jawabans: questions.map((q) => ({
                    pertanyaan_id: q.id,
                    nilai: employeeAnswers[q.id],
                })),
            }, {
                headers: { 'X-CSRF-TOKEN': csrfToken },
            });

            // 4. Update state draft. Status submitted baru berubah saat "Kirim Semua Penilaian".
            const updatedCompleted = completedEmployees;
            const updatedDrafts = completedDrafts.includes(savedEmployee.id)
                ? completedDrafts
                : [...completedDrafts, savedEmployee.id];

            const nextEmployee = employees.find((e) => !updatedDrafts.includes(e.id));
            const newAllDraftsComplete = updatedDrafts.length >= employees.length;

            setSavingNextName(nextEmployee?.name ?? '');
            setCompletedEmployees(updatedCompleted);
            setCompletedDrafts(updatedDrafts);
            setAllDraftsComplete(newAllDraftsComplete);
            setProgress({
                completed: updatedDrafts.length,
                total: employees.length,
                percentage: employees.length > 0
                    ? Math.round((updatedDrafts.length / employees.length) * 100)
                    : 0,
            });

            // 5. Setelah 600ms: sembunyikan overlay, pindah pegawai via state (BUKAN router.visit)
            //    router.visit() menyebabkan remount Show.jsx → banner langsung hilang
            window.setTimeout(() => {
                setIsSaving(false);

                if (newAllDraftsComplete) {
                    // Semua selesai → tampilkan AllDraftCompleteScreen (state only)
                    setCurrentView('hero-select');
                    setSelectedEmployee(null);
                    setIsPreview(false);
                    setSaveBanner({ show: false, savedName: '', nextName: '' });
                    // Update URL tanpa server round-trip
                    window.history.pushState({}, '', `/kuesioner/${kuesioner.kode}`);
                    return;
                }

                if (nextEmployee) {
                    // Update state langsung ke pegawai berikutnya — TANPA router.visit()
                    setSelectedEmployee(nextEmployee);
                    setCurrentView('questionnaire');
                    setCurrentQuestionIndex(0);
                    setIsPreview(false);

                    // Tampilkan banner (Show.jsx tidak remount → banner hidup normal)
                    setSaveBanner({
                        show: true,
                        savedName: savedEmployee.name,
                        nextName: nextEmployee.name,
                    });

                    // Update URL di browser agar konsisten (tanpa server request)
                    window.history.pushState(
                        {},
                        '',
                        `/kuesioner/${kuesioner.kode}/${nextEmployee.nama_slug}`
                    );
                }
            }, 600);
        } catch (error) {
            setIsSaving(false);
            const message = error.response?.data?.message ?? 'Gagal mengirim nilai. Silakan coba lagi.';
            setToastMessage(message);
            setShowToast(true);
        }
    };

    const isClosedAndEmpty = kuesioner.status === 'completed' && employees.length === 0;

    // ─── Render ──────────────────────────────────────────────────────────────
    return (
        <>
            <Head title={`${kuesioner.title} - Kuesioner 360`} />

            {/* TransitionOverlay — paling atas, fixed, z-50 */}
            <TransitionOverlay
                isVisible={isSaving}
                savingName={savingName}
                nextName={savingNextName}
            />

            <div className="min-h-svh bg-gray-950">
                {isClosedAndEmpty ? (
                    <div className="flex min-h-svh flex-col items-center justify-center p-6 text-slate-200">
                        <motion.div
                            initial={{ opacity: 0, scale: 0.97, y: 12 }}
                            animate={{ opacity: 1, scale: 1, y: 0 }}
                            transition={{ duration: 0.3 }}
                            className="w-full max-w-md rounded-lg border border-amber-500/20 bg-gray-900 p-6 text-center shadow-2xl shadow-black/40 flex flex-col items-center"
                        >
                            <div className="mb-5 flex h-16 w-16 items-center justify-center rounded-full border border-amber-500/35 bg-amber-500/10 text-3xl shadow-[0_0_24px_rgba(245,158,11,0.12)]">
                                ⚠️
                            </div>
                            <h2 className="text-lg font-black text-slate-100 uppercase tracking-wide">
                                Belum Ada Data Response
                            </h2>
                            <p className="mt-4 text-sm font-bold leading-relaxed text-amber-200 uppercase">
                                BELUM ADA DATA RESPONSE YANG BISA DITAMPILKAN UNTUK KUESIONER INI
                            </p>
                            <p className="mt-2 text-xs font-semibold text-slate-500">
                                Data respons kuesioner ini belum di-import dari Excel oleh admin.
                            </p>
                            <Button
                                variant="secondary"
                                className="mt-6 w-full"
                                onClick={handleBackToPeriod}
                            >
                                ← Kembali ke Daftar Periode
                            </Button>
                        </motion.div>
                    </div>
                ) : (
                    <AnimatePresence mode="wait">
                        {currentView === 'hero-select' && allDraftsComplete && (
                            <AllDraftCompleteScreen
                                key={`all-draft-complete-${kuesioner.id}`}
                                employees={employees}
                                kuesioner={kuesioner}
                                isAllSubmitted={isAllSubmitted}
                                onSelectEmployee={handleSelectEmployee}
                                onBackToPeriod={handleBackToPeriod}
                                completedDrafts={completedDrafts}
                            />
                        )}

                        {/* Belum semua draft → pilih pegawai normal */}
                        {currentView === 'hero-select' && !allDraftsComplete && (
                            <SelectEmployeeTemplate
                                key={`hero-select-${kuesioner.id}-${progress.completed}`}
                                employees={employees}
                                completedEmployees={completedDrafts}
                                selectedPeriod={kuesioner}
                                onSelectEmployee={handleSelectEmployee}
                                onBackToPeriod={handleBackToPeriod}
                            />
                        )}

                        {/* Form kuesioner per pegawai */}
                        {currentView === 'questionnaire' && selectedEmployee && (
                            <QuestionnaireTemplate
                                key={`questionnaire-${kuesioner.id}-${selectedEmployee.id}`}
                                employee={selectedEmployee}
                                selectedPeriod={kuesioner}
                                questions={questions}
                                answers={currentEmployeeAnswers}
                                employeeProgress={employeeProgress}
                                currentQuestionIndex={currentQuestionIndex}
                                onQuestionChange={setCurrentQuestionIndex}
                                onAnswerChange={handleAnswerChange}
                                onSubmit={handleSubmit}
                                onBack={handleBackToEmployee}
                                onBackToPeriod={handleBackToPeriod}
                                isPreview={isPreview}
                                isResponseSubmitted={isSelectedEmployeeSubmitted}
                                onPreviewAnswerChange={handlePreviewAnswerChange}
                                saveBanner={saveBanner}
                                onSaveBannerDismiss={handleSaveBannerDismiss}
                                autoAdvance={autoAdvance}
                                onAutoAdvanceChange={handleAutoAdvanceChange}
                            />
                        )}
                    </AnimatePresence>
                )}

                <NotificationToast
                    show={showToast}
                    message={toastMessage}
                    onDismiss={handleToastDismiss}
                />

                <NextEmployeeModal
                    show={Boolean(nextEmployeePrompt)}
                    employee={nextEmployeePrompt}
                    remainingCount={Math.max(employees.length - completedDrafts.length, 0)}
                    onContinue={handleContinueToNextEmployee}
                    onBackToList={handleBackToEmployee}
                />
            </div>
        </>
    );
}
