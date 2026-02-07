import { Head } from '@inertiajs/react';
import { BarChart3, FileText, TrendingUp, Download, Calendar, Filter } from 'lucide-react';
import { useState } from 'react';
import AdminLayout from '@/Layouts/AdminLayout';

interface ReportsIndexProps {
    filters: { date_from?: string; date_to?: string; type?: string };
}

export default function Index({ filters }: ReportsIndexProps) {
    const [dateFrom, setDateFrom] = useState(filters.date_from || '');
    const [dateTo, setDateTo] = useState(filters.date_to || '');

    const reportTypes = [
        {
            id: 'projects',
            name: 'Project Performance',
            description: 'Comprehensive project status and progress reports',
            icon: BarChart3,
            color: 'blue',
        },
        {
            id: 'expenditures',
            name: 'Financial Reports',
            description: 'Budget utilization and expenditure analysis',
            icon: FileText,
            color: 'green',
        },
        {
            id: 'indicators',
            name: 'Indicator Performance',
            description: 'Indicator achievements and trends',
            icon: TrendingUp,
            color: 'purple',
        },
        {
            id: 'data-entries',
            name: 'Data Entry Reports',
            description: 'Data collection and verification statistics',
            icon: Calendar,
            color: 'orange',
        },
    ];

    return (
        <AdminLayout header="Reports & Analytics">
            <Head title="Reports" />

            {/* Filters */}
            <div className="mb-6 rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <div className="grid grid-cols-1 gap-4 md:grid-cols-3">
                    <div>
                        <label className="mb-2 block text-sm font-medium text-gray-700">Date From</label>
                        <input
                            type="date"
                            value={dateFrom}
                            onChange={(e) => setDateFrom(e.target.value)}
                            className="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                        />
                    </div>

                    <div>
                        <label className="mb-2 block text-sm font-medium text-gray-700">Date To</label>
                        <input
                            type="date"
                            value={dateTo}
                            onChange={(e) => setDateTo(e.target.value)}
                            className="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                        />
                    </div>

                    <div className="flex items-end">
                        <button className="flex w-full items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">
                            <Filter className="h-4 w-4" />
                            Apply Filters
                        </button>
                    </div>
                </div>
            </div>

            {/* Report Types */}
            <div className="grid grid-cols-1 gap-6 md:grid-cols-2">
                {reportTypes.map((report) => {
                    const Icon = report.icon;
                    const bgColor = `bg-${report.color}-50`;
                    const textColor = `text-${report.color}-600`;

                    return (
                        <div key={report.id} className="rounded-xl border border-gray-200 bg-white p-6 shadow-sm transition-shadow hover:shadow-md">
                            <div className="mb-4 flex items-start justify-between">
                                <div className={`rounded-lg ${bgColor} p-3`}>
                                    <Icon className={`h-6 w-6 ${textColor}`} />
                                </div>
                            </div>

                            <h3 className="mb-2 text-lg font-semibold text-gray-900">{report.name}</h3>
                            <p className="mb-4 text-sm text-gray-600">{report.description}</p>

                            <div className="flex gap-2">
                                <button className="flex flex-1 items-center justify-center gap-2 rounded-lg border border-gray-300 px-4 py-2 text-gray-700 hover:bg-gray-50">
                                    <BarChart3 className="h-4 w-4" />
                                    View
                                </button>
                                <button className="flex flex-1 items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">
                                    <Download className="h-4 w-4" />
                                    Export
                                </button>
                            </div>
                        </div>
                    );
                })}
            </div>

            {/* Quick Stats */}
            <div className="mt-6 rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <h3 className="mb-4 text-lg font-semibold text-gray-900">Quick Statistics</h3>
                <div className="grid grid-cols-1 gap-4 md:grid-cols-4">
                    <div className="rounded-lg bg-blue-50 p-4">
                        <div className="text-sm text-blue-600">Total Projects</div>
                        <div className="mt-1 text-2xl font-bold text-blue-700">--</div>
                    </div>
                    <div className="rounded-lg bg-green-50 p-4">
                        <div className="text-sm text-green-600">Active Data Entries</div>
                        <div className="mt-1 text-2xl font-bold text-green-700">--</div>
                    </div>
                    <div className="rounded-lg bg-purple-50 p-4">
                        <div className="text-sm text-purple-600">Total Expenditures</div>
                        <div className="mt-1 text-2xl font-bold text-purple-700">--</div>
                    </div>
                    <div className="rounded-lg bg-orange-50 p-4">
                        <div className="text-sm text-orange-600">Photo Captures</div>
                        <div className="mt-1 text-2xl font-bold text-orange-700">--</div>
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}
