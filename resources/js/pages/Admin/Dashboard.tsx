import { Head } from '@inertiajs/react';
import { Users, FolderKanban, FileText, DollarSign, Camera, Clock, AlertCircle } from 'lucide-react';
import { LineChart, Line, BarChart, Bar, PieChart, Pie, Cell, XAxis, YAxis, CartesianGrid, Tooltip, Legend, ResponsiveContainer } from 'recharts';
import AdminLayout from '@/Layouts/AdminLayout';

interface DashboardProps {
    metrics: {
        total_users: number;
        active_users: number;
        total_projects: number;
        active_projects: number;
        completed_projects: number;
        total_data_entries: number;
        pending_data_entries: number;
        total_expenditures: number;
        pending_expenditures: number;
        total_photos: number;
        total_budget: number;
        total_spent: number;
    };
    projectsByStatus: Array<{ status: string; count: number }>;
    dataEntriesByMonth: Array<{ month: string; count: number }>;
    expendituresByCategory: Array<{ category: string; total: number }>;
    fieldOfficerActivity: Array<Record<string, unknown>>;
    recentDataEntries: Array<Record<string, unknown>>;
}

export default function Dashboard({
    metrics,
    projectsByStatus,
    dataEntriesByMonth,
    expendituresByCategory,
    fieldOfficerActivity,
    recentDataEntries,
}: DashboardProps) {
    const COLORS = ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6'];

    const stats = [
        {
            name: 'Total Users',
            value: metrics.total_users,
            subtext: `${metrics.active_users} active`,
            icon: Users,
            color: 'bg-blue-500',
            trend: '+12%',
        },
        {
            name: 'Active Projects',
            value: metrics.active_projects,
            subtext: `${metrics.completed_projects} completed`,
            icon: FolderKanban,
            color: 'bg-green-500',
            trend: '+8%',
        },
        {
            name: 'Pending Reviews',
            value: metrics.pending_data_entries,
            subtext: `${metrics.total_data_entries} total entries`,
            icon: FileText,
            color: 'bg-yellow-500',
            trend: '-5%',
        },
        {
            name: 'Budget Utilization',
            value: `${((metrics.total_spent / metrics.total_budget) * 100).toFixed(1)}%`,
            subtext: `${(metrics.total_spent / 1000000).toFixed(1)}M / ${(metrics.total_budget / 1000000).toFixed(1)}M TZS`,
            icon: DollarSign,
            color: 'bg-purple-500',
            trend: '+15%',
        },
    ];

    return (
        <AdminLayout header="Dashboard">
            <Head title="Admin Dashboard" />

            {/* Stats Grid */}
            <div className="mb-8 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                {stats.map((stat) => {
                    const Icon = stat.icon;
                    return (
                        <div key={stat.name} className="rounded-xl border border-gray-200 bg-white p-6 shadow-sm transition-shadow hover:shadow-md">
                            <div className="flex items-center justify-between">
                                <div className="flex-1">
                                    <p className="text-sm font-medium text-gray-600">{stat.name}</p>
                                    <p className="mt-2 text-3xl font-bold text-gray-900">{stat.value}</p>
                                    <p className="mt-1 text-xs text-gray-500">{stat.subtext}</p>
                                </div>
                                <div className={`${stat.color} flex h-14 w-14 items-center justify-center rounded-xl`}>
                                    <Icon className="h-7 w-7 text-white" />
                                </div>
                            </div>
                            <div className="mt-4 flex items-center text-sm">
                                <span className="font-medium text-green-600">{stat.trend}</span>
                                <span className="ml-2 text-gray-500">vs last month</span>
                            </div>
                        </div>
                    );
                })}
            </div>

            {/* Charts Row */}
            <div className="mb-8 grid grid-cols-1 gap-6 lg:grid-cols-2">
                {/* Data Entries Trend */}
                <div className="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <h3 className="mb-4 text-lg font-semibold text-gray-900">Data Entries Trend</h3>
                    <ResponsiveContainer width="100%" height={300}>
                        <LineChart data={dataEntriesByMonth}>
                            <CartesianGrid strokeDasharray="3 3" />
                            <XAxis dataKey="month" />
                            <YAxis />
                            <Tooltip />
                            <Legend />
                            <Line type="monotone" dataKey="count" stroke="#3b82f6" strokeWidth={2} />
                        </LineChart>
                    </ResponsiveContainer>
                </div>

                {/* Projects by Status */}
                <div className="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <h3 className="mb-4 text-lg font-semibold text-gray-900">Projects by Status</h3>
                    <ResponsiveContainer width="100%" height={300}>
                        <PieChart>
                            <Pie
                                data={projectsByStatus}
                                cx="50%"
                                cy="50%"
                                labelLine={false}
                                label={(props: { payload?: { status: string; count: number } }) =>
                                    props.payload ? `${props.payload.status}: ${props.payload.count}` : ''
                                }
                                outerRadius={100}
                                fill="#8884d8"
                                dataKey="count"
                            >
                                {projectsByStatus.map((entry, index) => (
                                    <Cell key={`cell-${index}`} fill={COLORS[index % COLORS.length]} />
                                ))}
                            </Pie>
                            <Tooltip />
                        </PieChart>
                    </ResponsiveContainer>
                </div>
            </div>

            {/* Expenditures by Category */}
            <div className="mb-8 rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <h3 className="mb-4 text-lg font-semibold text-gray-900">Expenditures by Category</h3>
                <ResponsiveContainer width="100%" height={300}>
                    <BarChart data={expendituresByCategory}>
                        <CartesianGrid strokeDasharray="3 3" />
                        <XAxis dataKey="category" />
                        <YAxis />
                        <Tooltip />
                        <Legend />
                        <Bar dataKey="total" fill="#3b82f6" />
                    </BarChart>
                </ResponsiveContainer>
            </div>

            {/* Recent Activity */}
            <div className="mb-8 grid grid-cols-1 gap-6 lg:grid-cols-2">
                {/* Recent Data Entries */}
                <div className="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <h3 className="mb-4 text-lg font-semibold text-gray-900">Recent Data Entries</h3>
                    <div className="space-y-3">
                        {recentDataEntries.slice(0, 5).map((entry) => (
                            <div key={entry.id} className="flex items-center justify-between rounded-lg bg-gray-50 p-3">
                                <div className="flex-1">
                                    <p className="text-sm font-medium text-gray-900">{entry.project?.name}</p>
                                    <p className="text-xs text-gray-500">{entry.indicator?.name}</p>
                                </div>
                                <div className="flex items-center gap-2">
                                    <span
                                        className={`rounded-full px-2 py-1 text-xs font-medium ${
                                            entry.verification_status === 'VERIFIED'
                                                ? 'bg-green-100 text-green-700'
                                                : entry.verification_status === 'PENDING'
                                                  ? 'bg-yellow-100 text-yellow-700'
                                                  : 'bg-red-100 text-red-700'
                                        }`}
                                    >
                                        {entry.verification_status}
                                    </span>
                                </div>
                            </div>
                        ))}
                    </div>
                </div>

                {/* Top Field Officers */}
                <div className="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <h3 className="mb-4 text-lg font-semibold text-gray-900">Top Field Officers (30 Days)</h3>
                    <div className="space-y-3">
                        {fieldOfficerActivity.slice(0, 5).map((officer, index: number) => (
                            <div key={officer.id} className="flex items-center justify-between rounded-lg bg-gray-50 p-3">
                                <div className="flex items-center gap-3">
                                    <div className="flex h-10 w-10 items-center justify-center rounded-full bg-linear-to-br from-blue-500 to-blue-600 font-semibold text-white">
                                        {index + 1}
                                    </div>
                                    <div>
                                        <p className="text-sm font-medium text-gray-900">{officer.full_name}</p>
                                        <p className="text-xs text-gray-500">{officer.position || 'Field Officer'}</p>
                                    </div>
                                </div>
                                <div className="text-right">
                                    <p className="text-sm font-semibold text-gray-900">
                                        {officer.data_entries_count + officer.expenditures_count + officer.photo_captures_count}
                                    </p>
                                    <p className="text-xs text-gray-500">activities</p>
                                </div>
                            </div>
                        ))}
                    </div>
                </div>
            </div>

            {/* Quick Actions */}
            <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <a
                    href="/admin/data-entries?status=PENDING"
                    className="rounded-xl bg-linear-to-br from-yellow-500 to-yellow-600 p-6 text-white transition-all hover:shadow-lg"
                >
                    <Clock className="mb-3 h-8 w-8" />
                    <h4 className="text-lg font-semibold">Review Data Entries</h4>
                    <p className="mt-1 text-sm text-yellow-100">{metrics.pending_data_entries} pending</p>
                </a>

                <a
                    href="/admin/expenditures?status=PENDING"
                    className="rounded-xl bg-linear-to-br from-red-500 to-red-600 p-6 text-white transition-all hover:shadow-lg"
                >
                    <AlertCircle className="mb-3 h-8 w-8" />
                    <h4 className="text-lg font-semibold">Approve Expenditures</h4>
                    <p className="mt-1 text-sm text-red-100">{metrics.pending_expenditures} pending</p>
                </a>

                <a
                    href="/admin/projects/create"
                    className="rounded-xl bg-linear-to-br from-green-500 to-green-600 p-6 text-white transition-all hover:shadow-lg"
                >
                    <FolderKanban className="mb-3 h-8 w-8" />
                    <h4 className="text-lg font-semibold">New Project</h4>
                    <p className="mt-1 text-sm text-green-100">Create project</p>
                </a>

                <a
                    href="/admin/photos"
                    className="rounded-xl bg-linear-to-br from-purple-500 to-purple-600 p-6 text-white transition-all hover:shadow-lg"
                >
                    <Camera className="mb-3 h-8 w-8" />
                    <h4 className="text-lg font-semibold">Photo Gallery</h4>
                    <p className="mt-1 text-sm text-purple-100">{metrics.total_photos} photos</p>
                </a>
            </div>
        </AdminLayout>
    );
}
