import { Head, Link } from '@inertiajs/react';
import { ArrowLeft, TrendingUp, TrendingDown, Minus, BarChart, Target } from 'lucide-react';
import AdminLayout from '@/Layouts/AdminLayout';

interface Theme {
    id: number;
    name: string;
}

interface Project {
    id: number;
    name: string;
    code: string;
}

interface Indicator {
    id: number;
    code: string;
    name: string;
    description: string | null;
    theme?: Theme;
    type: string;
    unit: string;
    unit_label: string | null;
    baseline_value: number | null;
    target_value: number | null;
    direction: string;
    frequency: string;
    data_source: string | null;
    calculation_method: string | null;
    is_active: boolean;
}

interface Performance {
    project: Project;
    target: number | null;
    actual: number | null;
    achievement: number | null;
    status: string;
}

interface ShowProps {
    indicator: Indicator;
    performance: Performance[];
}

export default function Show({ indicator, performance }: ShowProps) {
    const getDirectionIcon = (direction: string) => {
        if (direction === 'increasing') return <TrendingUp className="h-5 w-5 text-green-600" />;
        if (direction === 'decreasing') return <TrendingDown className="h-5 w-5 text-red-600" />;
        return <Minus className="h-5 w-5 text-gray-600" />;
    };

    const getStatusColor = (status: string) => {
        const colors = {
            'on-track': 'bg-green-100 text-green-800',
            'at-risk': 'bg-yellow-100 text-yellow-800',
            'off-track': 'bg-red-100 text-red-800',
        };
        return colors[status as keyof typeof colors] || 'bg-gray-100 text-gray-800';
    };

    return (
        <AdminLayout header={indicator.name}>
            <Head title={indicator.name} />

            <div className="mb-6 flex items-center justify-between">
                <Link href="/admin/indicators" className="inline-flex items-center gap-2 text-gray-600 hover:text-gray-900">
                    <ArrowLeft className="h-4 w-4" />
                    Back to Indicators
                </Link>
                <Link href={`/admin/indicators/${indicator.id}/edit`} className="rounded-lg bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">
                    Edit Indicator
                </Link>
            </div>

            <div className="grid grid-cols-1 gap-6 lg:grid-cols-3">
                {/* Details */}
                <div className="space-y-6 lg:col-span-2">
                    {/* Basic Information */}
                    <div className="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                        <h3 className="mb-4 text-lg font-semibold text-gray-900">Indicator Details</h3>

                        <div className="space-y-4">
                            <div className="grid grid-cols-2 gap-4">
                                <div>
                                    <p className="text-sm text-gray-600">Code</p>
                                    <p className="font-mono font-medium text-gray-900">{indicator.code}</p>
                                </div>
                                <div>
                                    <p className="text-sm text-gray-600">Type</p>
                                    <p className="font-medium text-gray-900 capitalize">{indicator.type}</p>
                                </div>
                            </div>

                            {indicator.description && (
                                <div>
                                    <p className="text-sm text-gray-600">Description</p>
                                    <p className="text-gray-900">{indicator.description}</p>
                                </div>
                            )}

                            {indicator.theme && (
                                <div>
                                    <p className="text-sm text-gray-600">Theme</p>
                                    <p className="text-gray-900">{indicator.theme.name}</p>
                                </div>
                            )}

                            <div className="grid grid-cols-2 gap-4">
                                <div>
                                    <p className="text-sm text-gray-600">Unit</p>
                                    <p className="text-gray-900">{indicator.unit_label || indicator.unit}</p>
                                </div>
                                <div>
                                    <p className="text-sm text-gray-600">Frequency</p>
                                    <p className="text-gray-900 capitalize">{indicator.frequency}</p>
                                </div>
                            </div>

                            {indicator.data_source && (
                                <div>
                                    <p className="text-sm text-gray-600">Data Source</p>
                                    <p className="text-gray-900">{indicator.data_source}</p>
                                </div>
                            )}

                            {indicator.calculation_method && (
                                <div>
                                    <p className="text-sm text-gray-600">Calculation Method</p>
                                    <p className="text-gray-900">{indicator.calculation_method}</p>
                                </div>
                            )}
                        </div>
                    </div>

                    {/* Performance by Project */}
                    <div className="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                        <h3 className="mb-4 text-lg font-semibold text-gray-900">Performance by Project</h3>

                        {performance && performance.length > 0 ? (
                            <div className="space-y-4">
                                {performance.map((perf, index) => (
                                    <div key={index} className="border-b border-gray-100 pb-4 last:border-0">
                                        <div className="flex items-start justify-between">
                                            <div>
                                                <Link
                                                    href={`/admin/projects/${perf.project.id}`}
                                                    className="font-medium text-blue-600 hover:text-blue-700"
                                                >
                                                    {perf.project.name}
                                                </Link>
                                                <p className="text-sm text-gray-500">{perf.project.code}</p>
                                            </div>
                                            <span
                                                className={`inline-flex rounded-full px-2 py-1 text-xs font-semibold ${getStatusColor(perf.status)}`}
                                            >
                                                {perf.status}
                                            </span>
                                        </div>
                                        <div className="mt-2 grid grid-cols-3 gap-4 text-sm">
                                            <div>
                                                <p className="text-gray-600">Target</p>
                                                <p className="font-medium text-gray-900">{perf.target || '-'}</p>
                                            </div>
                                            <div>
                                                <p className="text-gray-600">Actual</p>
                                                <p className="font-medium text-gray-900">{perf.actual || '-'}</p>
                                            </div>
                                            <div>
                                                <p className="text-gray-600">Achievement</p>
                                                <p className="font-medium text-gray-900">{perf.achievement ? `${perf.achievement}%` : '-'}</p>
                                            </div>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        ) : (
                            <p className="py-8 text-center text-gray-500">No performance data available</p>
                        )}
                    </div>
                </div>

                {/* Sidebar */}
                <div className="space-y-6">
                    {/* Target & Baseline */}
                    <div className="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                        <h3 className="mb-4 flex items-center gap-2 text-lg font-semibold text-gray-900">
                            <Target className="h-5 w-5" />
                            Target & Baseline
                        </h3>
                        <div className="space-y-4">
                            <div>
                                <p className="text-sm text-gray-600">Baseline Value</p>
                                <p className="text-2xl font-bold text-gray-900">{indicator.baseline_value || '-'}</p>
                            </div>
                            <div>
                                <p className="text-sm text-gray-600">Target Value</p>
                                <p className="text-2xl font-bold text-blue-600">{indicator.target_value || '-'}</p>
                            </div>
                            <div className="flex items-center gap-2 border-t pt-2">
                                {getDirectionIcon(indicator.direction)}
                                <span className="text-sm text-gray-700 capitalize">{indicator.direction} is better</span>
                            </div>
                        </div>
                    </div>

                    {/* Status */}
                    <div className="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                        <h3 className="mb-4 flex items-center gap-2 text-lg font-semibold text-gray-900">
                            <BarChart className="h-5 w-5" />
                            Status
                        </h3>
                        <div className="space-y-4">
                            <div>
                                <p className="text-sm text-gray-600">Current Status</p>
                                <span
                                    className={`mt-1 inline-flex rounded-full px-3 py-1 text-sm font-semibold ${
                                        indicator.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                                    }`}
                                >
                                    {indicator.is_active ? 'Active' : 'Inactive'}
                                </span>
                            </div>
                            <div>
                                <p className="text-sm text-gray-600">Projects Tracking</p>
                                <p className="text-2xl font-bold text-gray-900">{performance?.length || 0}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}
