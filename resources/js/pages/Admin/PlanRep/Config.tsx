import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowLeft, Save, CheckCircle, XCircle } from 'lucide-react';
import { useState } from 'react';
import AdminLayout from '@/Layouts/AdminLayout';

interface Config {
    url: string;
    api_key: string;
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
        api_key: '',
        enabled: config.enabled ?? false,
        sync_interval: config.sync_interval || 'daily',
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post('/admin/planrep/config');
    };

    const testConnection = async () => {
        if (!data.url || !data.api_key) {
            alert('Please fill in URL and API Key to test connection');
            return;
        }

        setTestingConnection(true);
        setConnectionStatus(null);

        try {
            const response = await fetch('/admin/planrep/test-connection', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
                body: JSON.stringify({
                    url: data.url,
                    api_key: data.api_key,
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
        <AdminLayout header="PlanRep Configuration">
            <Head title="PlanRep Configuration" />

            <div className="mb-6">
                <Link href="/admin/planrep" className="inline-flex items-center gap-2 text-gray-600 hover:text-gray-900">
                    <ArrowLeft className="h-4 w-4" />
                    Back to PlanRep Dashboard
                </Link>
            </div>

            <form onSubmit={handleSubmit} className="space-y-6">
                {/* Connection Settings */}
                <div className="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <h3 className="mb-4 text-lg font-semibold text-gray-900">Connection Settings</h3>

                    <div className="space-y-4">
                        <div>
                            <label className="mb-2 block text-sm font-medium text-gray-700">PlanRep Server URL *</label>
                            <input
                                type="url"
                                value={data.url}
                                onChange={(e) => setData('url', e.target.value)}
                                className="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-green-500 focus:ring-2 focus:ring-green-500"
                                placeholder="https://planrep.example.com"
                                required
                            />
                            {errors.url && <p className="mt-1 text-sm text-red-600">{errors.url}</p>}
                        </div>

                        <div>
                            <label className="mb-2 block text-sm font-medium text-gray-700">API Key</label>
                            <input
                                type="password"
                                value={data.api_key}
                                onChange={(e) => setData('api_key', e.target.value)}
                                className="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-green-500 focus:ring-2 focus:ring-green-500"
                                placeholder="Leave empty to keep current API key"
                            />
                            {errors.api_key && <p className="mt-1 text-sm text-red-600">{errors.api_key}</p>}
                            <p className="mt-1 text-xs text-gray-500">Current: {config.api_key || 'Not configured'}</p>
                        </div>

                        <div>
                            <button
                                type="button"
                                onClick={testConnection}
                                disabled={testingConnection}
                                className="flex items-center gap-2 rounded-lg border border-green-600 bg-white px-4 py-2 text-green-600 hover:bg-green-50 disabled:opacity-50"
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
                                className="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-green-500 focus:ring-2 focus:ring-green-500"
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
                                    className="h-4 w-4 rounded border-gray-300 text-green-600 focus:ring-2 focus:ring-green-500"
                                />
                                <span className="text-sm font-medium text-gray-700">Enable PlanRep Integration</span>
                            </label>
                        </div>
                    </div>
                </div>

                {/* Actions */}
                <div className="flex items-center justify-end gap-4">
                    <Link href="/admin/planrep" className="rounded-lg border border-gray-300 bg-white px-6 py-2 text-gray-700 hover:bg-gray-50">
                        Cancel
                    </Link>
                    <button
                        type="submit"
                        disabled={processing}
                        className="flex items-center gap-2 rounded-lg bg-green-600 px-6 py-2 text-white hover:bg-green-700 disabled:opacity-50"
                    >
                        <Save className="h-4 w-4" />
                        {processing ? 'Saving...' : 'Save Configuration'}
                    </button>
                </div>
            </form>
        </AdminLayout>
    );
}
