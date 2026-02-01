import { Head, router } from '@inertiajs/react';
import { FileText, Download, Calendar, TrendingUp, DollarSign, CheckCircle } from 'lucide-react';
import { useState } from 'react';
import AdminLayout from '@/Layouts/AdminLayout';

interface Project {
    id: number;
    name: string;
    theme?: { name: string; color: string };
    organizational_unit?: { name: string };
    data_entries_count: number;
    expenditures_count: number;
}

interface Stats {
    projects_active: number;
    data_entries: number;
    expenditures: number;
    total_spent: number;
    results_achieved: number;
}

interface QuarterlyPackIndexProps {
    stats: Stats;
    projects: Project[];
    selectedQuarter: number;
    selectedYear: number;
    quarterStart: string;
    quarterEnd: string;
}

export default function Index({ stats, projects, selectedQuarter, selectedYear, quarterStart, quarterEnd }: QuarterlyPackIndexProps) {
    const [quarter, setQuarter] = useState(selectedQuarter.toString());
    const [year, setYear] = useState(selectedYear.toString());

    const handleQuarterChange = () => {
        router.get('/admin/quarterly-pack', { quarter, year }, { preserveState: true });
    };

    const handleDownload = () => {
        // TODO: Implement download logic
        alert('Quarterly pack download will be implemented');
    };

    const quarters = [
        { value: '1', label: 'Q1 (Jan - Mar)' },
        { value: '2', label: 'Q2 (Apr - Jun)' },
        { value: '3', label: 'Q3 (Jul - Sep)' },
        { value: '4', label: 'Q4 (Oct - Dec)' },
    ];

    const years = Array.from({ length: 10 }, (_, i) => new Date().getFullYear() - 5 + i);

    return (
        <AdminLayout header="Quarterly Pack">
            <Head title="Quarterly Pack" />

            {/* Header */}
            <div className="mb-6 rounded-xl border border-gray-200 bg-gradient-to-r from-purple-600 to-blue-600 p-6 text-white shadow-sm">
                <div className="flex items-center justify-between">
                    <div className="flex items-center gap-4">
                        <FileText className="h-12 w-12" />
                        <div>
                            <h2 className="text-2xl font-bold">Quarterly Performance Pack</h2>
                            <p className="text-purple-100">
                                {new Date(quarterStart).toLocaleDateString()} - {new Date(quarterEnd).toLocaleDateString()}
                            </p>
                        </div>
                    </div>
                    <button
                        onClick={handleDownload}
                        className="flex items-center gap-2 rounded-lg bg-white px-4 py-2 text-purple-600 hover:bg-purple-50"
                    >
                        <Download className="h-4 w-4" />
                        Download Report
                    </button>
                </div>
            </div>

            {/* Quarter Selector */}
            <div className="mb-6 rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <h3 className="mb-4 text-lg font-semibold text-gray-900">Select Quarter</h3>
                <div className="grid grid-cols-1 gap-4 md:grid-cols-3">
                    <select
                        value={quarter}
                        onChange={(e) => setQuarter(e.target.value)}
                        className="rounded-lg border border-gray-300 px-4 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                    >
                        {quarters.map((q) => (
                            <option key={q.value} value={q.value}>
                                {q.label}
                            </option>
                        ))}
                    </select>

                    <select
                        value={year}
                        onChange={(e) => setYear(e.target.value)}
                        className="rounded-lg border border-gray-300 px-4 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                    >
                        {years.map((y) => (
                            <option key={y} value={y}>
                                {y}
                            </option>
                        ))}
                    </select>

                    <button
                        onClick={handleQuarterChange}
                        className="flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-white hover:bg-blue-700"
                    >
                        <Calendar className="h-4 w-4" />
                        Load Quarter
                    </button>
                </div>
            </div>

            {/* Statistics */}
            <div className="mb-6 grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-5">
                <div className="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <div className="mb-2 flex items-center justify-between">
                        <p className="text-sm text-gray-600">Active Projects</p>
                        <TrendingUp className="h-5 w-5 text-blue-600" />
                    </div>
                    <p className="text-3xl font-bold text-gray-900">{stats.projects_active}</p>
                </div>

                <div className="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <div className="mb-2 flex items-center justify-between">
                        <p className="text-sm text-gray-600">Data Entries</p>
                        <FileText className="h-5 w-5 text-green-600" />
                    </div>
                    <p className="text-3xl font-bold text-gray-900">{stats.data_entries}</p>
                </div>

                <div className="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <div className="mb-2 flex items-center justify-between">
                        <p className="text-sm text-gray-600">Expenditures</p>
                        <DollarSign className="h-5 w-5 text-purple-600" />
                    </div>
                    <p className="text-3xl font-bold text-gray-900">{stats.expenditures}</p>
                </div>

                <div className="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <div className="mb-2 flex items-center justify-between">
                        <p className="text-sm text-gray-600">Total Spent</p>
                        <DollarSign className="h-5 w-5 text-orange-600" />
                    </div>
                    <p className="text-2xl font-bold text-gray-900">TZS {stats.total_spent.toLocaleString()}</p>
                </div>

                <div className="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <div className="mb-2 flex items-center justify-between">
                        <p className="text-sm text-gray-600">Results Achieved</p>
                        <CheckCircle className="h-5 w-5 text-teal-600" />
                    </div>
                    <p className="text-3xl font-bold text-gray-900">{stats.results_achieved}</p>
                </div>
            </div>

            {/* Project Summary */}
            <div className="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <h3 className="mb-4 text-lg font-semibold text-gray-900">Project Performance Summary</h3>
                <div className="overflow-x-auto">
                    <table className="min-w-full divide-y divide-gray-200">
                        <thead className="bg-gray-50">
                            <tr>
                                <th className="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase">Project Name</th>
                                <th className="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase">Theme</th>
                                <th className="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase">Unit</th>
                                <th className="px-6 py-3 text-center text-xs font-medium tracking-wider text-gray-500 uppercase">Data Entries</th>
                                <th className="px-6 py-3 text-center text-xs font-medium tracking-wider text-gray-500 uppercase">Expenditures</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-gray-200 bg-white">
                            {projects.map((project) => (
                                <tr key={project.id} className="hover:bg-gray-50">
                                    <td className="px-6 py-4 text-sm font-medium whitespace-nowrap text-gray-900">{project.name}</td>
                                    <td className="px-6 py-4 whitespace-nowrap">
                                        {project.theme && (
                                            <span
                                                className="inline-flex rounded-full px-2 py-1 text-xs font-semibold"
                                                style={{ backgroundColor: project.theme.color + '20', color: project.theme.color }}
                                            >
                                                {project.theme.name}
                                            </span>
                                        )}
                                    </td>
                                    <td className="px-6 py-4 text-sm whitespace-nowrap text-gray-500">{project.organizational_unit?.name || '-'}</td>
                                    <td className="px-6 py-4 text-center text-sm whitespace-nowrap text-gray-900">{project.data_entries_count}</td>
                                    <td className="px-6 py-4 text-center text-sm whitespace-nowrap text-gray-900">{project.expenditures_count}</td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            </div>
        </AdminLayout>
    );
}
