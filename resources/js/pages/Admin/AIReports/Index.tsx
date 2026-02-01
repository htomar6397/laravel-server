import { Head } from '@inertiajs/react';
import { Brain, TrendingUp, AlertCircle, CheckCircle, BarChart3, Activity } from 'lucide-react';
import AdminLayout from '@/Layouts/AdminLayout';

interface Stats {
    total_projects: number;
    active_projects: number;
    total_data_entries: number;
    verified_entries: number;
    total_expenditures: number;
    approved_expenditures: number;
    total_budget: number;
    total_spent: number;
}

interface RecentActivity {
    data_entries_this_month: number;
    data_entries_last_month: number;
    expenditures_this_month: number;
    expenditures_last_month: number;
}

interface Insight {
    type: string;
    severity: 'info' | 'warning' | 'success' | 'error';
    title: string;
    message: string;
}

interface AIReportsIndexProps {
    stats: Stats;
    recentActivity: RecentActivity;
    insights: Insight[];
    filters: { date_from?: string; date_to?: string; project?: string; theme?: string };
}

export default function Index({ stats, recentActivity, insights }: AIReportsIndexProps) {
    const getSeverityIcon = (severity: string) => {
        switch (severity) {
            case 'success':
                return <CheckCircle className="h-6 w-6 text-green-600" />;
            case 'warning':
                return <AlertCircle className="h-6 w-6 text-yellow-600" />;
            case 'error':
                return <AlertCircle className="h-6 w-6 text-red-600" />;
            default:
                return <AlertCircle className="h-6 w-6 text-blue-600" />;
        }
    };

    const getSeverityColor = (severity: string) => {
        switch (severity) {
            case 'success':
                return 'border-green-200 bg-green-50';
            case 'warning':
                return 'border-yellow-200 bg-yellow-50';
            case 'error':
                return 'border-red-200 bg-red-50';
            default:
                return 'border-blue-200 bg-blue-50';
        }
    };

    return (
        <AdminLayout header="AI-Powered Reports">
            <Head title="AI Reports" />

            {/* Header */}
            <div className="mb-6 rounded-xl border border-gray-200 bg-gradient-to-r from-blue-600 to-purple-600 p-6 text-white shadow-sm">
                <div className="flex items-center gap-4">
                    <Brain className="h-12 w-12" />
                    <div>
                        <h2 className="text-2xl font-bold">AI Analytics Dashboard</h2>
                        <p className="text-blue-100">Intelligent insights and recommendations powered by AI</p>
                    </div>
                </div>
            </div>

            {/* Key Metrics */}
            <div className="mb-6 grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
                <div className="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <div className="mb-2 flex items-center justify-between">
                        <p className="text-sm text-gray-600">Active Projects</p>
                        <Activity className="h-5 w-5 text-blue-600" />
                    </div>
                    <p className="text-3xl font-bold text-gray-900">{stats.active_projects}</p>
                    <p className="mt-1 text-xs text-gray-500">of {stats.total_projects} total</p>
                </div>

                <div className="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <div className="mb-2 flex items-center justify-between">
                        <p className="text-sm text-gray-600">Data Verification</p>
                        <CheckCircle className="h-5 w-5 text-green-600" />
                    </div>
                    <p className="text-3xl font-bold text-gray-900">
                        {((stats.verified_entries / Math.max(stats.total_data_entries, 1)) * 100).toFixed(1)}%
                    </p>
                    <p className="mt-1 text-xs text-gray-500">{stats.verified_entries} verified</p>
                </div>

                <div className="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <div className="mb-2 flex items-center justify-between">
                        <p className="text-sm text-gray-600">Budget Utilization</p>
                        <BarChart3 className="h-5 w-5 text-purple-600" />
                    </div>
                    <p className="text-3xl font-bold text-gray-900">{((stats.total_spent / Math.max(stats.total_budget, 1)) * 100).toFixed(1)}%</p>
                    <p className="mt-1 text-xs text-gray-500">TZS {stats.total_spent.toLocaleString()}</p>
                </div>

                <div className="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <div className="mb-2 flex items-center justify-between">
                        <p className="text-sm text-gray-600">Expenditure Approval</p>
                        <TrendingUp className="h-5 w-5 text-orange-600" />
                    </div>
                    <p className="text-3xl font-bold text-gray-900">
                        {((stats.approved_expenditures / Math.max(stats.total_expenditures, 1)) * 100).toFixed(1)}%
                    </p>
                    <p className="mt-1 text-xs text-gray-500">{stats.approved_expenditures} approved</p>
                </div>
            </div>

            {/* Activity Trends */}
            <div className="mb-6 rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <h3 className="mb-4 text-lg font-semibold text-gray-900">Monthly Activity Trends</h3>
                <div className="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div>
                        <div className="mb-2 flex items-center justify-between">
                            <p className="text-sm font-medium text-gray-700">Data Entries</p>
                            <TrendingUp
                                className={`h-5 w-5 ${
                                    recentActivity.data_entries_this_month >= recentActivity.data_entries_last_month
                                        ? 'text-green-600'
                                        : 'text-red-600'
                                }`}
                            />
                        </div>
                        <div className="flex items-end gap-4">
                            <div>
                                <p className="text-xs text-gray-500">This Month</p>
                                <p className="text-2xl font-bold text-blue-600">{recentActivity.data_entries_this_month}</p>
                            </div>
                            <div>
                                <p className="text-xs text-gray-500">Last Month</p>
                                <p className="text-xl font-semibold text-gray-400">{recentActivity.data_entries_last_month}</p>
                            </div>
                        </div>
                    </div>

                    <div>
                        <div className="mb-2 flex items-center justify-between">
                            <p className="text-sm font-medium text-gray-700">Expenditures</p>
                            <TrendingUp
                                className={`h-5 w-5 ${
                                    recentActivity.expenditures_this_month >= recentActivity.expenditures_last_month
                                        ? 'text-green-600'
                                        : 'text-red-600'
                                }`}
                            />
                        </div>
                        <div className="flex items-end gap-4">
                            <div>
                                <p className="text-xs text-gray-500">This Month</p>
                                <p className="text-2xl font-bold text-purple-600">{recentActivity.expenditures_this_month}</p>
                            </div>
                            <div>
                                <p className="text-xs text-gray-500">Last Month</p>
                                <p className="text-xl font-semibold text-gray-400">{recentActivity.expenditures_last_month}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {/* AI Insights */}
            <div className="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <h3 className="mb-4 text-lg font-semibold text-gray-900">AI-Generated Insights</h3>
                <div className="space-y-4">
                    {insights.map((insight, index) => (
                        <div key={index} className={`rounded-lg border p-4 ${getSeverityColor(insight.severity)}`}>
                            <div className="flex items-start gap-3">
                                {getSeverityIcon(insight.severity)}
                                <div className="flex-1">
                                    <h4 className="font-semibold text-gray-900">{insight.title}</h4>
                                    <p className="mt-1 text-sm text-gray-700">{insight.message}</p>
                                </div>
                            </div>
                        </div>
                    ))}
                </div>
            </div>

            {/* Coming Soon Notice */}
            <div className="mt-6 rounded-xl border border-blue-200 bg-blue-50 p-6 text-center">
                <Brain className="mx-auto mb-4 h-12 w-12 text-blue-600" />
                <h3 className="mb-2 text-lg font-semibold text-gray-900">Advanced AI Features Coming Soon</h3>
                <p className="text-gray-700">
                    Predictive analytics, automated reporting, anomaly detection, and intelligent recommendations will be available in the next
                    release.
                </p>
            </div>
        </AdminLayout>
    );
}
