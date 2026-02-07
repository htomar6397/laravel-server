import { Head, Link, router } from '@inertiajs/react';
import { Search, Eye, Filter, Calendar } from 'lucide-react';
import { useState } from 'react';
import { ErrorBoundary } from '@/components';
import { useErrorReporting } from '@/hooks/useErrorReporting';
import AdminLayout from '@/Layouts/AdminLayout';

interface AuditLog {
    id: number;
    user?: { id: number; full_name: string; email: string };
    action: string;
    entity_type: string;
    entity_id?: number;
    ip_address?: string;
    user_agent?: string;
    created_at: string;
}

interface AuditLogsIndexProps {
    logs: {
        data: AuditLog[];
        links: Array<{ url: string | null; label: string; active: boolean }>;
        meta: { current_page: number; last_page: number; per_page: number; total: number; from: number; to: number };
    };
    filters: { search?: string; action?: string; model?: string; user?: string; date_from?: string; date_to?: string };
    actions: string[];
    models: string[];
}

export default function Index({ logs, filters, actions, models }: AuditLogsIndexProps) {
    const [search, setSearch] = useState(filters.search || '');
    const [actionFilter, setActionFilter] = useState(filters.action || '');
    const [modelFilter, setModelFilter] = useState(filters.model || '');
    const [dateFrom, setDateFrom] = useState(filters.date_from || '');
    const [dateTo, setDateTo] = useState(filters.date_to || '');
    const { reportError } = useErrorReporting();

    const handleSearch = () => {
        router.get(
            '/admin/audit-logs',
            { search, action: actionFilter, model: modelFilter, date_from: dateFrom, date_to: dateTo },
            { preserveState: true },
        );
    };

    const getActionBadgeColor = (action: string) => {
        const colors: Record<string, string> = {
            created: 'bg-green-100 text-green-800',
            updated: 'bg-blue-100 text-blue-800',
            deleted: 'bg-red-100 text-red-800',
            approved: 'bg-purple-100 text-purple-800',
            rejected: 'bg-orange-100 text-orange-800',
            verified: 'bg-teal-100 text-teal-800',
        };
        return colors[action.toLowerCase()] || 'bg-gray-100 text-gray-800';
    };

    return (
        <AdminLayout header="Audit Logs">
            <Head title="Audit Logs" />

            <ErrorBoundary
                onError={(error, errorInfo) => reportError(error, { ...errorInfo, componentStack: errorInfo.componentStack || undefined }, { page: 'AuditLogs', filters })}
                fallback={
                    <div className="rounded-xl border border-red-200 bg-red-50 p-8 text-center">
                        <div className="mx-auto max-w-md">
                            <h2 className="text-lg font-semibold text-red-900">Audit Logs Error</h2>
                            <p className="mt-2 text-sm text-red-700">
                                There was a problem loading the audit logs. Please try refreshing the page.
                            </p>
                            <button
                                onClick={() => window.location.reload()}
                                className="mt-4 inline-flex items-center rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700"
                            >
                                Refresh Page
                            </button>
                        </div>
                    </div>
                }
            >

            {/* Filters */}
            <div className="mb-6 rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <h2 className="mb-4 text-lg font-semibold text-gray-900">Filter Audit Logs</h2>

                <div className="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                    <div className="lg:col-span-3">
                        <div className="relative">
                            <Search className="absolute top-1/2 left-3 h-5 w-5 -translate-y-1/2 transform text-gray-400" />
                            <input
                                type="text"
                                placeholder="Search logs..."
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                                onKeyPress={(e) => e.key === 'Enter' && handleSearch()}
                                className="w-full rounded-lg border border-gray-300 py-2 pr-4 pl-10 focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                            />
                        </div>
                    </div>

                    <select
                        value={actionFilter}
                        onChange={(e) => setActionFilter(e.target.value)}
                        className="rounded-lg border border-gray-300 px-4 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                    >
                        <option value="">All Actions</option>
                        {actions.map((action) => (
                            <option key={action} value={action}>
                                {action}
                            </option>
                        ))}
                    </select>

                    <select
                        value={modelFilter}
                        onChange={(e) => setModelFilter(e.target.value)}
                        className="rounded-lg border border-gray-300 px-4 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                    >
                        <option value="">All Models</option>
                        {models.map((model) => (
                            <option key={model} value={model}>
                                {model}
                            </option>
                        ))}
                    </select>

                    <div className="flex items-center gap-2">
                        <Calendar className="h-5 w-5 text-gray-400" />
                        <input
                            type="date"
                            value={dateFrom}
                            onChange={(e) => setDateFrom(e.target.value)}
                            className="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                            placeholder="From"
                        />
                    </div>

                    <div className="flex items-center gap-2">
                        <Calendar className="h-5 w-5 text-gray-400" />
                        <input
                            type="date"
                            value={dateTo}
                            onChange={(e) => setDateTo(e.target.value)}
                            className="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                            placeholder="To"
                        />
                    </div>

                    <button
                        onClick={handleSearch}
                        className="flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-white hover:bg-blue-700"
                    >
                        <Filter className="h-4 w-4" />
                        Apply Filters
                    </button>
                </div>
            </div>

            {/* Logs Table */}
            <div className="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                <div className="overflow-x-auto">
                    <table className="min-w-full divide-y divide-gray-200">
                        <thead className="bg-gray-50">
                            <tr>
                                <th className="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase">Date & Time</th>
                                <th className="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase">User</th>
                                <th className="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase">Action</th>
                                <th className="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase">Model</th>
                                <th className="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase">IP Address</th>
                                <th className="px-6 py-3 text-right text-xs font-medium tracking-wider text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-gray-200 bg-white">
                            {logs?.data?.map((log) => (
                                <tr key={log.id} className="hover:bg-gray-50">
                                    <td className="px-6 py-4 text-sm whitespace-nowrap text-gray-900">{new Date(log.created_at).toLocaleString()}</td>
                                    <td className="px-6 py-4">
                                        <div className="text-sm">
                                            <div className="font-medium text-gray-900">{log.user?.full_name || 'System'}</div>
                                            <div className="text-gray-500">{log.user?.email || '-'}</div>
                                        </div>
                                    </td>
                                    <td className="px-6 py-4 whitespace-nowrap">
                                        <span
                                            className={`inline-flex rounded-full px-2 py-1 text-xs font-semibold ${getActionBadgeColor(log.action)}`}
                                        >
                                            {log.action}
                                        </span>
                                    </td>
                                    <td className="px-6 py-4 text-sm whitespace-nowrap text-gray-900">
                                        {log.entity_type ? log.entity_type.split('\\').pop() : '-'}
                                        {log.entity_id && ` #${log.entity_id}`}
                                    </td>
                                    <td className="px-6 py-4 text-sm whitespace-nowrap text-gray-500">{log.ip_address || '-'}</td>
                                    <td className="px-6 py-4 text-right text-sm font-medium whitespace-nowrap">
                                        <Link href={`/admin/audit-logs/${log.id}`} className="text-blue-600 hover:text-blue-900">
                                            <Eye className="inline h-4 w-4" />
                                        </Link>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>

                {/* Pagination */}
                {logs?.data?.length > 0 && (
                    <div className="flex items-center justify-between border-t border-gray-200 bg-white px-6 py-3">
                        <div className="text-sm text-gray-700">
                            Showing <span className="font-medium">{logs.meta?.from || 0}</span> to <span className="font-medium">{logs.meta?.to || 0}</span> of{' '}
                            <span className="font-medium">{logs.meta?.total || 0}</span> results
                        </div>
                        <div className="flex gap-2">
                            {logs.links?.map((link, index) => (
                                <button
                                    key={index}
                                    onClick={() => link.url && router.get(link.url)}
                                    disabled={!link.url}
                                    className={`rounded px-3 py-1 text-sm ${
                                        link.active
                                            ? 'bg-blue-600 text-white'
                                            : link.url
                                              ? 'bg-white text-gray-700 hover:bg-gray-50'
                                              : 'bg-gray-100 text-gray-400'
                                    }`}
                                    dangerouslySetInnerHTML={{ __html: link.label }}
                                />
                            ))}
                        </div>
                    </div>
                )}
            </div>
            </ErrorBoundary>
        </AdminLayout>
    );
}
