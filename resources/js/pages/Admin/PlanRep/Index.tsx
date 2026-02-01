import { Head, Link } from '@inertiajs/react';
import { Database, Settings, RefreshCw, CheckCircle, XCircle, Clock } from 'lucide-react';
import AdminLayout from '@/Layouts/AdminLayout';

interface Config {
    url: string;
    api_key: string;
    enabled: boolean;
    last_sync?: string;
}

interface SyncStats {
    total_synced: number;
    last_sync_count: number;
    failed_syncs: number;
    pending_sync: number;
}

interface PlanRepIndexProps {
    config: Config;
    syncStats: SyncStats;
}

export default function Index({ config, syncStats }: PlanRepIndexProps) {
    const handleSync = async () => {
        if (confirm('Are you sure you want to sync data to PlanRep?')) {
            // TODO: Implement sync logic
            alert('Sync initiated');
        }
    };

    return (
        <AdminLayout header="PlanRep Integration">
            <Head title="PlanRep Integration" />

            {/* Header */}
            <div className="mb-6 rounded-xl border border-gray-200 bg-gradient-to-r from-green-600 to-teal-600 p-6 text-white shadow-sm">
                <div className="flex items-center justify-between">
                    <div className="flex items-center gap-4">
                        <Database className="h-12 w-12" />
                        <div>
                            <h2 className="text-2xl font-bold">PlanRep Integration</h2>
                            <p className="text-green-100">Planning & Reporting System Integration</p>
                        </div>
                    </div>
                    <Link
                        href="/admin/planrep/config"
                        className="flex items-center gap-2 rounded-lg bg-white px-4 py-2 text-green-600 hover:bg-green-50"
                    >
                        <Settings className="h-4 w-4" />
                        Configure
                    </Link>
                </div>
            </div>

            {/* Connection Status */}
            <div className="mb-6 rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <h3 className="mb-4 text-lg font-semibold text-gray-900">Connection Status</h3>
                <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <div className="mb-2 flex items-center gap-2">
                            {config.enabled ? (
                                <>
                                    <CheckCircle className="h-5 w-5 text-green-600" />
                                    <span className="font-medium text-green-600">Enabled</span>
                                </>
                            ) : (
                                <>
                                    <XCircle className="h-5 w-5 text-red-600" />
                                    <span className="font-medium text-red-600">Disabled</span>
                                </>
                            )}
                        </div>
                        <p className="text-sm text-gray-600">Server URL</p>
                        <p className="text-sm font-medium text-gray-900">{config.url || 'Not configured'}</p>
                    </div>

                    <div>
                        <div className="mb-2 flex items-center gap-2">
                            <Clock className="h-5 w-5 text-blue-600" />
                            <span className="font-medium text-gray-700">Last Sync</span>
                        </div>
                        <p className="text-sm text-gray-600">{config.last_sync ? new Date(config.last_sync).toLocaleString() : 'Never synced'}</p>
                    </div>
                </div>
            </div>

            {/* Sync Statistics */}
            <div className="mb-6 grid grid-cols-1 gap-6 md:grid-cols-4">
                <div className="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <div className="mb-2 flex items-center justify-between">
                        <p className="text-sm text-gray-600">Total Synced</p>
                        <Database className="h-5 w-5 text-green-600" />
                    </div>
                    <p className="text-3xl font-bold text-gray-900">{syncStats.total_synced}</p>
                </div>

                <div className="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <div className="mb-2 flex items-center justify-between">
                        <p className="text-sm text-gray-600">Last Sync Count</p>
                        <CheckCircle className="h-5 w-5 text-green-600" />
                    </div>
                    <p className="text-3xl font-bold text-gray-900">{syncStats.last_sync_count}</p>
                </div>

                <div className="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <div className="mb-2 flex items-center justify-between">
                        <p className="text-sm text-gray-600">Failed Syncs</p>
                        <XCircle className="h-5 w-5 text-red-600" />
                    </div>
                    <p className="text-3xl font-bold text-gray-900">{syncStats.failed_syncs}</p>
                </div>

                <div className="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <div className="mb-2 flex items-center justify-between">
                        <p className="text-sm text-gray-600">Pending</p>
                        <Clock className="h-5 w-5 text-orange-600" />
                    </div>
                    <p className="text-3xl font-bold text-gray-900">{syncStats.pending_sync}</p>
                </div>
            </div>

            {/* Actions */}
            <div className="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <h3 className="mb-4 text-lg font-semibold text-gray-900">Actions</h3>
                <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <button
                        onClick={handleSync}
                        disabled={!config.enabled}
                        className="flex items-center justify-center gap-2 rounded-lg bg-green-600 px-6 py-3 text-white hover:bg-green-700 disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        <RefreshCw className="h-5 w-5" />
                        Sync Now
                    </button>

                    <Link
                        href="/admin/planrep/config"
                        className="flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-6 py-3 text-gray-700 hover:bg-gray-50"
                    >
                        <Settings className="h-5 w-5" />
                        Configuration Settings
                    </Link>
                </div>
            </div>

            {/* Information */}
            <div className="mt-6 rounded-xl border border-green-200 bg-green-50 p-6">
                <h3 className="mb-2 text-lg font-semibold text-gray-900">About PlanRep Integration</h3>
                <p className="text-gray-700">
                    PlanRep (Planning & Reporting) integration enables automatic synchronization of project data, financial reports, and performance
                    indicators to the national planning and reporting system. Configure the integration settings to enable seamless data exchange with
                    PlanRep.
                </p>
            </div>
        </AdminLayout>
    );
}
