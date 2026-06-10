// Page login pegawai biasa untuk masuk ke dashboard Kuesioner 360.
import { Head, useForm } from '@inertiajs/react';
import { motion } from 'framer-motion';

export default function Masuk() {
    const { data, setData, post, processing, errors } = useForm({
        username: '',
        password: '',
        remember: false,
    });

    const handleSubmit = (event) => {
        event.preventDefault();
        post('/masuk');
    };

    return (
        <>
            <Head title="Masuk Pegawai" />

            <main className="flex min-h-svh items-center justify-center bg-gray-950 px-4 py-6 text-slate-200 sm:py-10">
                <motion.section
                    initial={{ opacity: 0, y: 24 }}
                    animate={{ opacity: 1, y: 0 }}
                    transition={{ duration: 0.34, ease: 'easeOut' }}
                    className="w-full max-w-md rounded-lg border border-purple-500/20 bg-gray-900 p-5 shadow-2xl shadow-black/35 sm:p-8"
                >
                    <div className="mb-6 sm:mb-8">
                        <div className="mb-4 flex h-12 w-12 items-center justify-center rounded-lg border border-cyan-400/25 bg-cyan-500/10 text-sm font-black text-cyan-200 sm:mb-5 sm:h-14 sm:w-14">
                            BPS
                        </div>
                        <p className="text-sm font-bold uppercase tracking-wide text-purple-300">
                             Kuesioner 360
                        </p>
                        <h1 className="mt-2 text-2xl font-black leading-tight text-slate-100 sm:text-3xl">
                            Masuk Pegawai
                        </h1>
                        <p className="mt-3 text-sm leading-6 text-slate-500">
                            Gunakan akun pegawai untuk membuka dashboard kuesioner yang ditugaskan kepada Anda.
                        </p>
                    </div>

                    <form onSubmit={handleSubmit} className="space-y-4 sm:space-y-5">
                        <div>
                            <label htmlFor="username" className="text-sm font-bold text-slate-300">
                                Username
                            </label>
                            <input
                                id="username"
                                type="text"
                                value={data.username}
                                onChange={(event) => setData('username', event.target.value)}
                                className="mt-2 h-12 w-full rounded-lg border border-slate-700 bg-gray-950 px-4 text-sm font-semibold text-slate-100 outline-none transition-colors placeholder:text-slate-600 focus:border-cyan-400 focus:ring-2 focus:ring-cyan-400/20"
                                placeholder="Masukkan username"
                                autoComplete="username"
                                autoFocus
                            />
                            {errors.username && (
                                <p className="mt-2 text-sm font-semibold text-red-300">
                                    {errors.username}
                                </p>
                            )}
                        </div>

                        <div>
                            <label htmlFor="password" className="text-sm font-bold text-slate-300">
                                Password
                            </label>
                            <input
                                id="password"
                                type="password"
                                value={data.password}
                                onChange={(event) => setData('password', event.target.value)}
                                className="mt-2 h-12 w-full rounded-lg border border-slate-700 bg-gray-950 px-4 text-sm font-semibold text-slate-100 outline-none transition-colors placeholder:text-slate-600 focus:border-cyan-400 focus:ring-2 focus:ring-cyan-400/20"
                                placeholder="Masukkan password"
                                autoComplete="current-password"
                            />
                            {errors.password && (
                                <p className="mt-2 text-sm font-semibold text-red-300">
                                    {errors.password}
                                </p>
                            )}
                        </div>

                        <label className="flex cursor-pointer items-center gap-3 text-sm font-semibold text-slate-400">
                            <input
                                type="checkbox"
                                checked={data.remember}
                                onChange={(event) => setData('remember', event.target.checked)}
                                className="h-4 w-4 rounded border-slate-700 bg-gray-950 text-cyan-500 focus:ring-cyan-400"
                            />
                            Ingat saya
                        </label>

                        <motion.button
                            type="submit"
                            disabled={processing}
                            whileHover={processing ? undefined : { scale: 1.01, y: -1 }}
                            whileTap={processing ? undefined : { scale: 0.98 }}
                            className="flex min-h-12 w-full items-center justify-center rounded-lg bg-gradient-to-r from-violet-600 via-purple-600 to-cyan-500 px-4 py-3 text-center text-sm font-black leading-tight text-white shadow-lg shadow-purple-950/40 outline-none transition-opacity focus-visible:ring-2 focus-visible:ring-cyan-400 focus-visible:ring-offset-2 focus-visible:ring-offset-gray-950 disabled:cursor-not-allowed disabled:opacity-60"
                        >
                            {processing ? 'Memproses...' : 'Masuk ke Dashboard'}
                        </motion.button>
                    </form>

                </motion.section>
            </main>
        </>
    );
}
