import { Head, router } from '@inertiajs/react';
import { Search, Bell, Filter, Calendar } from 'lucide-react';
import { useState } from 'react';
import AdminLayout from '@/Layouts/AdminLayout';

interface Notification {
    id: number;
    user?: { id: number; full_name: string; email: string };
    title: string;
    message: string;
    type: string;
    read_at?: string;
    created_at: string;
}

interface NotificationsIndexProps {
    notifications: {
        data: Notification[];
        links: Array<{ url: string | null; label: string; active: boolean }>;
        meta: { current_page: number; last_page: number; per_page: number; total: number; from: number; to: number };
    };
    filters: { search?: string; type?: string; read_status?: string; date_from?: string; date_to?: string };
    stats: { total: number; unread: number; today: number };
    types: string[];
}

export default function Index({ notifications, filters, stats, types }: NotificationsIndexProps) {
    const [search, setSearch] = useState(filters.search || '');
    const [typeFilter, setTypeFilter] = useState(filters.type || '');
    const [readStatus, setReadStatus] = useState(filters.read_status || '');
    const [dateFrom, setDateFrom] = useState(filters.date_from || '');
    const [dateTo, setDateTo] = useState(filters.date_to || '');

    const handleSearch = () => {
        router.get(
            '/admin/notifications',
            { search, type: typeFilter, read_status: readStatus, date_from: dateFrom, date_to: dateTo },
            { preserveState: true },
        );
    };

    const getTypeBadgeColor = (type: string) => {
        const colors: Record<string, string> = {
            info: 'bg-blue-100 text-blue-800',
            success: 'bg-green-100 text-green-800',
            warning: 'bg-yellow-100 text-yellow-800',
            error: 'bg-red-100 text-red-800',
        };
        return colors[type.toLowerCase()] || 'bg-gray-100 text-gray-800';
    };

    return (
        <AdminLayout header="Notifications">
            <Head title="Notifications" />

            {/* Stats */}
            <div className="mb-6 grid grid-cols-1 gap-6 md:grid-cols-3">
                <div className="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <div className="flex items-center justify-between">
                        <div>
                            <p className="text-sm text-gray-600">Total Notifications</p>
                            <p className="text-2xl font-bold text-gray-900">{stats.total}</p>
                        </div>
                        <Bell className="h-8 w-8 text-blue-600" />
                    </div>
                </div>

                <div className="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <div className="flex items-center justify-between">
                        <div>
                            <p className="text-sm text-gray-600">Unread</p>
                            <p className="text-2xl font-bold text-orange-600">{stats.unread}</p>
                        </div>
                        <Bell className="h-8 w-8 text-orange-600" />
                    </div>
                </div>

                <div className="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <div className="flex items-center justify-between">
                        <div>
                            <p className="text-sm text-gray-600">Today</p>
                            <p className="text-2xl font-bold text-green-600">{stats.today}</p>
                        </div>
                        <Bell className="h-8 w-8 text-green-600" />
                    </div>
                </div>
            </div>

            {/* Filters */}
            <div className="mb-6 rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <h2 className="mb-4 text-lg font-semibold text-gray-900">Filter Notifications</h2>

                <div className="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                    <div className="lg:col-span-3">
                        <div className="relative">
                            <Search className="absolute top-1/2 left-3 h-5 w-5 -translate-y-1/2 transform text-gray-400" />
                            <input
                                type="text"
                                placeholder="Search notifications..."
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                                onKeyPress={(e) => e.key === 'Enter' && handleSearch()}
                                className="w-full rounded-lg border border-gray-300 py-2 pr-4 pl-10 focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                            />
                        </div>
                    </div>

                    <select
                        value={typeFilter}
                        onChange={(e) => setTypeFilter(e.target.value)}
                        className="rounded-lg border border-gray-300 px-4 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                    >
                        <option value="">All Types</option>
                        {types.map((type) => (
                            <option key={type} value={type}>
                                {type}
                            </option>
                        ))}
                    </select>

                    <select
                        value={readStatus}
                        onChange={(e) => setReadStatus(e.target.value)}
                        className="rounded-lg border border-gray-300 px-4 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                    >
                        <option value="">All Status</option>
                        <option value="read">Read</option>
                        <option value="unread">Unread</option>
                    </select>

                    <div className="flex items-center gap-2">
                        <Calendar className="h-5 w-5 text-gray-400" />
                        <input
                            type="date"
                            value={dateFrom}
                            onChange={(e) => setDateFrom(e.target.value)}
                            className="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                        />
                    </div>

                    <div className="flex items-center gap-2">
                        <Calendar className="h-5 w-5 text-gray-400" />
                        <input
                            type="date"
                            value={dateTo}
                            onChange={(e) => setDateTo(e.target.value)}
                            className="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
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

            {/* Notifications List */}
            <div className="space-y-4">
                {notifications.data.map((notification) => (
                    <div
                        key={notification.id}
                        className={`rounded-xl border p-6 shadow-sm transition-shadow hover:shadow-md ${
                            notification.read_at ? 'border-gray-200 bg-white' : 'border-blue-200 bg-blue-50'
                        }`}
                    >
                        <div className="flex items-start justify-between">
                            <div className="flex-1">
                                <div className="mb-2 flex items-center gap-2">
                                    <h3 className="text-lg font-semibold text-gray-900">{notification.title}</h3>
                                    <span
                                        className={`inline-flex rounded-full px-2 py-1 text-xs font-semibold ${getTypeBadgeColor(notification.type)}`}
                                    >
                                        {notification.type}
                                    </span>
                                    {!notification.read_at && (
                                        <span className="inline-flex rounded-full bg-blue-600 px-2 py-1 text-xs font-semibold text-white">New</span>
                                    )}
                                </div>
                                <p className="mb-3 text-sm text-gray-700">{notification.message}</p>
                                <div className="flex items-center gap-4 text-xs text-gray-500">
                                    <span>{notification.user?.full_name || 'System'}</span>
                                    <span>•</span>
                                    <span>{new Date(notification.created_at).toLocaleString()}</span>
                                    {notification.read_at && (
                                        <>
                                            <span>•</span>
                                            <span className="text-green-600">Read</span>
                                        </>
                                    )}
                                </div>
                            </div>
                        </div>
                    </div>
                ))}
            </div>

            {/* Pagination */}
            {notifications.data.length > 0 && (
                <div className="mt-6 flex items-center justify-between rounded-xl border border-gray-200 bg-white px-6 py-3">
                    <div className="text-sm text-gray-700">
                        Showing <span className="font-medium">{notifications.meta.from}</span> to{' '}
                        <span className="font-medium">{notifications.meta.to}</span> of{' '}
                        <span className="font-medium">{notifications.meta.total}</span> results
                    </div>
                    <div className="flex gap-2">
                        {notifications.links.map((link, index) => (
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
        </AdminLayout>
    );
}
