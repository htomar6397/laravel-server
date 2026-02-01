import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowLeft, Save, CheckCircle, XCircle } from 'lucide-react';
import { useState } from 'react';
import AdminLayout from '@/Layouts/AdminLayout';

interface Config {
    url: string;
    username: string;
    enabled: boolean;
    sync_interval: string;
}

interface ConfigProps {
    config: Config;
}

export default function Config({ config }: ConfigProps) {
    const [testingConnection, setTestingConnection] = useState(false);
    const [connectionStatus, setConnectionStatus] = useState<{ success: boolean; message: string } | null>(null);

    const { data, setData, post, processing, errors } = useForm({
        url: config.url || '',
        username: config.username || '',
        password: '',
        enabled: config.enabled ?? false,
        sync_interval: config.sync_interval || 'daily',
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post('/admin/dhis2/config');
    };

    const testConnection = async () => {
        if (!data.url || !data.username || !data.password) {
            alert('Please fill in URL, username, and password to test connection');
            return;
        }

        setTestingConnection(true);
        setConnectionStatus(null);

        try {
            const response = await fetch('/admin/dhis2/test-connection', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
                body: JSON.stringify({
                    url: data.url,
                    username: data.username,
                    password: data.password,
                }),
            });

            const result = await response.json();
            setConnectionStatus(result);
        } catch (error) {
            setConnectionStatus({
                success: false,
                message: 'Connection test failed: ' + (error as Error).message,
            });
        } finally {
            setTestingConnection(false);
        }
    };

    return (
        <AdminLayout header="DHIS2 Configuration">
            <Head title="DHIS2 Configuration" />

            <div className="mb-6">
                <Link href="/admin/dhis2" className="inline-flex items-center gap-2 text-gray-600 hover:text-gray-900">
                    <ArrowLeft className="h-4 w-4" />
                    Back to DHIS2 Dashboard
                </Link>
            </div>

            <form onSubmit={handleSubmit} className="space-y-6">
                {/* Connection Settings */}
                <div className="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <h3 className="mb-4 text-lg font-semibold text-gray-900">Connection Settings</h3>

                    <div className="space-y-4">
                        <div>
                            <label className="mb-2 block text-sm font-medium text-gray-700">DHIS2 Server URL *</label>
                            <input
                                type="url"
                                value={data.url}
                                onChange={(e) => setData('url', e.target.value)}
                                className="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                                placeholder="https://dhis2.example.com"
                                required
                            />
                            {errors.url && <p className="mt-1 text-sm text-red-600">{errors.url}</p>}
                        </div>

                        <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div>
                                <label className="mb-2 block text-sm font-medium text-gray-700">Username *</label>
                                <input
                                    type="text"
                                    value={data.username}
                                    onChange={(e) => setData('username', e.target.value)}
                                    className="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                                    required
                                />
                                {errors.username && <p className="mt-1 text-sm text-red-600">{errors.username}</p>}
                            </div>

                            <div>
                                <label className="mb-2 block text-sm font-medium text-gray-700">Password</label>
                                <input
                                    type="password"
                                    value={data.password}
                                    onChange={(e) => setData('password', e.target.value)}
                                    className="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                                    placeholder="Leave empty to keep current password"
                                />
                                {errors.password && <p className="mt-1 text-sm text-red-600">{errors.password}</p>}
                            </div>
                        </div>

                        <div>
                            <button
                                type="button"
                                onClick={testConnection}
                                disabled={testingConnection}
                                className="flex items-center gap-2 rounded-lg border border-blue-600 bg-white px-4 py-2 text-blue-600 hover:bg-blue-50 disabled:opacity-50"
                            >
                                {testingConnection ? 'Testing...' : 'Test Connection'}
                            </button>
                        </div>

                        {connectionStatus && (
                            <div
                                className={`flex items-center gap-2 rounded-lg border p-4 ${
                                    connectionStatus.success ? 'border-green-200 bg-green-50 text-green-800' : 'border-red-200 bg-red-50 text-red-800'
                                }`}
                            >
                                {connectionStatus.success ? <CheckCircle className="h-5 w-5" /> : <XCircle className="h-5 w-5" />}
                                <span>{connectionStatus.message}</span>
                            </div>
                        )}
                    </div>
                </div>

                {/* Sync Settings */}
                <div className="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <h3 className="mb-4 text-lg font-semibold text-gray-900">Sync Settings</h3>

                    <div className="space-y-4">
                        <div>
                            <label className="mb-2 block text-sm font-medium text-gray-700">Sync Interval *</label>
                            <select
                                value={data.sync_interval}
                                onChange={(e) => setData('sync_interval', e.target.value)}
                                className="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                                required
                            >
                                <option value="hourly">Hourly</option>
                                <option value="daily">Daily</option>
                                <option value="weekly">Weekly</option>
                            </select>
                            {errors.sync_interval && <p className="mt-1 text-sm text-red-600">{errors.sync_interval}</p>}
                        </div>

                        <div>
                            <label className="flex items-center gap-2">
                                <input
                                    type="checkbox"
                                    checked={data.enabled}
                                    onChange={(e) => setData('enabled', e.target.checked)}
                                    className="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-2 focus:ring-blue-500"
                                />
                                <span className="text-sm font-medium text-gray-700">Enable DHIS2 Integration</span>
                            </label>
                        </div>
                    </div>
                </div>

                {/* Actions */}
                <div className="flex items-center justify-end gap-4">
                    <Link href="/admin/dhis2" className="rounded-lg border border-gray-300 bg-white px-6 py-2 text-gray-700 hover:bg-gray-50">
                        Cancel
                    </Link>
                    <button
                        type="submit"
                        disabled={processing}
                        className="flex items-center gap-2 rounded-lg bg-blue-600 px-6 py-2 text-white hover:bg-blue-700 disabled:opacity-50"
                    >
                        <Save className="h-4 w-4" />
                        {processing ? 'Saving...' : 'Save Configuration'}
                    </button>
                </div>
            </form>
        </AdminLayout>
    );
}
