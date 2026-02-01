import { Head, Link } from '@inertiajs/react';
import { ArrowLeft, User, Calendar, Monitor, MapPin, FileText } from 'lucide-react';
import AdminLayout from '@/Layouts/AdminLayout';

interface AuditLog {
    id: number;
    user?: { id: number; full_name: string; email: string; username: string };
    action: string;
    model_type: string;
    model_id?: number;
    description?: string;
    ip_address?: string;
    user_agent?: string;
    old_values?: Record<string, any>;
    new_values?: Record<string, any>;
    created_at: string;
}

interface ShowProps {
    log: AuditLog;
}

export default function Show({ log }: ShowProps) {
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
        <AdminLayout header="Audit Log Details">
            <Head title="Audit Log Details" />

            <div className="mb-6">
                <Link href="/admin/audit-logs" className="inline-flex items-center gap-2 text-gray-600 hover:text-gray-900">
                    <ArrowLeft className="h-4 w-4" />
                    Back to Audit Logs
                </Link>
            </div>

            {/* Log Overview */}
            <div className="mb-6 rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <div className="mb-4 flex items-center justify-between">
                    <h3 className="text-lg font-semibold text-gray-900">Audit Log #{log.id}</h3>
                    <span className={`inline-flex rounded-full px-3 py-1 text-sm font-semibold ${getActionBadgeColor(log.action)}`}>
                        {log.action}
                    </span>
                </div>

                <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div className="flex items-start gap-3">
                        <User className="mt-1 h-5 w-5 text-gray-400" />
                        <div>
                            <p className="text-sm font-medium text-gray-700">User</p>
                            <p className="text-sm text-gray-900">{log.user?.full_name || 'System'}</p>
                            <p className="text-xs text-gray-500">{log.user?.email || '-'}</p>
                        </div>
                    </div>

                    <div className="flex items-start gap-3">
                        <Calendar className="mt-1 h-5 w-5 text-gray-400" />
                        <div>
                            <p className="text-sm font-medium text-gray-700">Date & Time</p>
                            <p className="text-sm text-gray-900">{new Date(log.created_at).toLocaleString()}</p>
                        </div>
                    </div>

                    <div className="flex items-start gap-3">
                        <FileText className="mt-1 h-5 w-5 text-gray-400" />
                        <div>
                            <p className="text-sm font-medium text-gray-700">Model</p>
                            <p className="text-sm text-gray-900">
                                {log.model_type ? log.model_type.split('\\').pop() : '-'}
                                {log.model_id && ` #${log.model_id}`}
                            </p>
                        </div>
                    </div>

                    <div className="flex items-start gap-3">
                        <MapPin className="mt-1 h-5 w-5 text-gray-400" />
                        <div>
                            <p className="text-sm font-medium text-gray-700">IP Address</p>
                            <p className="text-sm text-gray-900">{log.ip_address || '-'}</p>
                        </div>
                    </div>
                </div>

                {log.description && (
                    <div className="mt-4 border-t border-gray-200 pt-4">
                        <p className="text-sm font-medium text-gray-700">Description</p>
                        <p className="mt-1 text-sm text-gray-900">{log.description}</p>
                    </div>
                )}
            </div>

            {/* User Agent */}
            {log.user_agent && (
                <div className="mb-6 rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <div className="flex items-start gap-3">
                        <Monitor className="mt-1 h-5 w-5 text-gray-400" />
                        <div className="flex-1">
                            <p className="text-sm font-medium text-gray-700">User Agent</p>
                            <p className="mt-1 text-sm text-gray-900">{log.user_agent}</p>
                        </div>
                    </div>
                </div>
            )}

            {/* Changes (Old vs New Values) */}
            {(log.old_values || log.new_values) && (
                <div className="grid grid-cols-1 gap-6 md:grid-cols-2">
                    {log.old_values && (
                        <div className="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                            <h3 className="mb-4 text-lg font-semibold text-gray-900">Old Values</h3>
                            <pre className="overflow-x-auto rounded-lg bg-gray-50 p-4 text-xs">{JSON.stringify(log.old_values, null, 2)}</pre>
                        </div>
                    )}

                    {log.new_values && (
                        <div className="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                            <h3 className="mb-4 text-lg font-semibold text-gray-900">New Values</h3>
                            <pre className="overflow-x-auto rounded-lg bg-gray-50 p-4 text-xs">{JSON.stringify(log.new_values, null, 2)}</pre>
                        </div>
                    )}
                </div>
            )}
        </AdminLayout>
    );
}
