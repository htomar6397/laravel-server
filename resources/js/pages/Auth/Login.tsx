import { Head, useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import React from 'react';

export default function Login() {
    const { data, setData, post, processing, errors } = useForm({
        email: '',
        password: '',
        remember: false,
    });

    const handleSubmit = (e: FormEvent) => {
        e.preventDefault();
        post('/login');
    };

    return (
        <>
            <Head title="Login - KMC M&E System" />

            <div className="flex min-h-screen items-center justify-center bg-gradient-to-br from-blue-600 via-purple-600 to-pink-500 px-4">
                <div className="w-full max-w-md">
                    {/* Logo/Title */}
                    <div className="mb-8 text-center">
                        <div className="mx-auto mb-4 flex h-20 w-20 items-center justify-center rounded-full bg-white shadow-xl">
                            <svg className="h-10 w-10 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path
                                    strokeLinecap="round"
                                    strokeLinejoin="round"
                                    strokeWidth={2}
                                    d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"
                                />
                            </svg>
                        </div>
                        <h1 className="mb-2 text-3xl font-bold text-white">KMC M&E System</h1>
                        <p className="text-blue-100">Admin Panel Login</p>
                    </div>

                    {/* Login Card */}
                    <div className="rounded-2xl bg-white p-8 shadow-2xl">
                        <form onSubmit={handleSubmit} className="space-y-6">
                            {/* Email */}
                            <div>
                                <label htmlFor="email" className="mb-2 block text-sm font-medium text-gray-700">
                                    Email Address
                                </label>
                                <input
                                    id="email"
                                    type="email"
                                    value={data.email}
                                    onChange={(e) => setData('email', e.target.value)}
                                    className={`w-full rounded-lg border px-4 py-3 transition focus:border-transparent focus:ring-2 focus:ring-blue-500 ${
                                        errors.email ? 'border-red-500' : 'border-gray-300'
                                    }`}
                                    placeholder="admin@kmc.gov.gh"
                                    required
                                    autoFocus
                                />
                                {errors.email && <p className="mt-2 text-sm text-red-600">{errors.email}</p>}
                            </div>

                            {/* Password */}
                            <div>
                                <label htmlFor="password" className="mb-2 block text-sm font-medium text-gray-700">
                                    Password
                                </label>
                                <input
                                    id="password"
                                    type="password"
                                    value={data.password}
                                    onChange={(e) => setData('password', e.target.value)}
                                    className={`w-full rounded-lg border px-4 py-3 transition focus:border-transparent focus:ring-2 focus:ring-blue-500 ${
                                        errors.password ? 'border-red-500' : 'border-gray-300'
                                    }`}
                                    placeholder="••••••••"
                                    required
                                />
                                {errors.password && <p className="mt-2 text-sm text-red-600">{errors.password}</p>}
                            </div>

                            {/* Remember Me */}
                            <div className="flex items-center">
                                <input
                                    id="remember"
                                    type="checkbox"
                                    checked={data.remember}
                                    onChange={(e) => setData('remember', e.target.checked)}
                                    className="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                />
                                <label htmlFor="remember" className="ml-2 block text-sm text-gray-700">
                                    Remember me
                                </label>
                            </div>

                            {/* Submit Button */}
                            <button
                                type="submit"
                                disabled={processing}
                                className="w-full rounded-lg bg-gradient-to-r from-blue-600 to-purple-600 px-4 py-3 font-medium text-white transition hover:from-blue-700 hover:to-purple-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                {processing ? (
                                    <span className="flex items-center justify-center">
                                        <svg className="mr-3 -ml-1 h-5 w-5 animate-spin text-white" fill="none" viewBox="0 0 24 24">
                                            <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4" />
                                            <path
                                                className="opacity-75"
                                                fill="currentColor"
                                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                            />
                                        </svg>
                                        Signing in...
                                    </span>
                                ) : (
                                    'Sign In'
                                )}
                            </button>
                        </form>

                        {/* Footer */}
                        <div className="mt-6 text-center">
                            <p className="text-sm text-gray-500">Kumasi Metropolitan Assembly</p>
                        </div>
                    </div>

                    {/* Development Credentials */}
                    <div className="mt-6 rounded-lg bg-white/10 p-4 text-sm text-white backdrop-blur-sm">
                        <p className="mb-2 font-semibold">📝 Demo Credentials:</p>
                        <p className="font-mono">Email: admin@kmc.gov.gh</p>
                        <p className="font-mono">Password: (check database)</p>
                    </div>
                </div>
            </div>
        </>
    );
}
