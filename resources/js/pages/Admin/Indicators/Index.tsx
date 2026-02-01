import { Head, Link, router } from '@inertiajs/react';
import { Plus, Search, Filter, Edit, Trash2, Eye, TrendingUp, TrendingDown, Minus } from 'lucide-react';
import { useState } from 'react';
import AdminLayout from '@/Layouts/AdminLayout';

interface Theme {
    id: number;
    name: string;
}

interface Indicator {
    id: number;
    code: string;
    name: string;
    description: string | null;
    theme?: Theme;
    type: 'output' | 'outcome' | 'impact' | 'process';
    unit: 'number' | 'percentage' | 'currency' | 'text';
    unit_label: string | null;
    baseline_value: number | null;
    target_value: number | null;
    direction: 'increasing' | 'decreasing' | 'stable';
    frequency: string;
    is_active: boolean;
}

interface PaginationMeta {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number;
    to: number;
}

interface IndexProps {
    indicators: {
        data: Indicator[];
        meta: PaginationMeta;
    };
    filters: {
        search?: string;
        theme?: string;
        type?: string;
        status?: string;
    };
    themes: Theme[];
}

export default function Index({ indicators, filters, themes }: IndexProps) {
    const [search, setSearch] = useState(filters.search || '');
    const [showFilters, setShowFilters] = useState(false);

    const handleSearch = (e: React.FormEvent) => {
        e.preventDefault();
        router.get('/admin/indicators', { ...filters, search }, { preserveState: true });
    };

    const handleFilter = (key: string, value: string) => {
        router.get('/admin/indicators', { ...filters, [key]: value }, { preserveState: true });
    };

    const clearFilters = () => {
        router.get('/admin/indicators', {}, { preserveState: true });
        setSearch('');
    };

    const getTypeColor = (type: string) => {
        const colors = {
            output: 'bg-blue-100 text-blue-800',
            outcome: 'bg-green-100 text-green-800',
            impact: 'bg-purple-100 text-purple-800',
            process: 'bg-gray-100 text-gray-800',
        };
        return colors[type as keyof typeof colors] || colors.process;
    };

    const getDirectionIcon = (direction: string) => {
        if (direction === 'increasing') return <TrendingUp className="h-4 w-4 text-green-600" />;
        if (direction === 'decreasing') return <TrendingDown className="h-4 w-4 text-red-600" />;
        return <Minus className="h-4 w-4 text-gray-600" />;
    };

    const { data, meta } = indicators;

    return (
        <AdminLayout header="Indicators">
            <Head title="Indicators" />

            <div className="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p className="text-gray-600">
                        Showing {meta?.from || 0} to {meta?.to || 0} of {meta?.total || 0} indicators
                    </p>
                </div>
                <Link
                    href="/admin/indicators/create"
                    className="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-white hover:bg-blue-700"
                >
                    <Plus className="h-5 w-5" />
                    Add Indicator
                </Link>
            </div>

            {/* Search and Filters */}
            <div className="mb-6 space-y-4 rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                <form onSubmit={handleSearch} className="flex gap-2">
                    <div className="relative flex-1">
                        <Search className="absolute top-1/2 left-3 h-5 w-5 -translate-y-1/2 text-gray-400" />
                        <input
                            type="text"
                            value={search}
                            onChange={(e) => setSearch(e.target.value)}
                            placeholder="Search indicators by code, name, or description..."
                            className="w-full rounded-lg border-gray-300 pl-10 focus:border-blue-500 focus:ring-blue-500"
                        />
                    </div>
                    <button
                        type="button"
                        onClick={() => setShowFilters(!showFilters)}
                        className="rounded-lg border border-gray-300 px-4 py-2 hover:bg-gray-50"
                    >
                        <Filter className="h-5 w-5" />
                    </button>
                    <button type="submit" className="rounded-lg bg-blue-600 px-6 py-2 text-white hover:bg-blue-700">
                        Search
                    </button>
                </form>

                {showFilters && (
                    <div className="grid grid-cols-1 gap-4 border-t pt-4 md:grid-cols-3">
                        <div>
                            <label className="mb-1 block text-sm font-medium text-gray-700">Theme</label>
                            <select
                                value={filters.theme || ''}
                                onChange={(e) => handleFilter('theme', e.target.value)}
                                className="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                            >
                                <option value="">All Themes</option>
                                {themes.map((theme) => (
                                    <option key={theme.id} value={theme.id}>
                                        {theme.name}
                                    </option>
                                ))}
                            </select>
                        </div>
                        <div>
                            <label className="mb-1 block text-sm font-medium text-gray-700">Type</label>
                            <select
                                value={filters.type || ''}
                                onChange={(e) => handleFilter('type', e.target.value)}
                                className="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                            >
                                <option value="">All Types</option>
                                <option value="output">Output</option>
                                <option value="outcome">Outcome</option>
                                <option value="impact">Impact</option>
                                <option value="process">Process</option>
                            </select>
                        </div>
                        <div>
                            <label className="mb-1 block text-sm font-medium text-gray-700">Status</label>
                            <select
                                value={filters.status || ''}
                                onChange={(e) => handleFilter('status', e.target.value)}
                                className="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                            >
                                <option value="">All Status</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                        <div className="md:col-span-3">
                            <button onClick={clearFilters} className="text-sm text-blue-600 hover:text-blue-700">
                                Clear all filters
                            </button>
                        </div>
                    </div>
                )}
            </div>

            {/* Indicators Table */}
            <div className="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                <table className="min-w-full divide-y divide-gray-200">
                    <thead className="bg-gray-50">
                        <tr>
                            <th className="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase">Code</th>
                            <th className="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase">Indicator</th>
                            <th className="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase">Type</th>
                            <th className="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase">Target</th>
                            <th className="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase">Direction</th>
                            <th className="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase">Status</th>
                            <th className="px-6 py-3 text-right text-xs font-medium tracking-wider text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-gray-200 bg-white">
                        {data?.map((indicator) => (
                            <tr key={indicator.id} className="hover:bg-gray-50">
                                <td className="px-6 py-4 whitespace-nowrap">
                                    <span className="font-mono text-sm text-gray-900">{indicator.code}</span>
                                </td>
                                <td className="px-6 py-4">
                                    <div>
                                        <div className="font-medium text-gray-900">{indicator.name}</div>
                                        {indicator.theme && <div className="text-sm text-gray-500">{indicator.theme.name}</div>}
                                    </div>
                                </td>
                                <td className="px-6 py-4 whitespace-nowrap">
                                    <span className={`inline-flex rounded-full px-2 py-1 text-xs font-semibold ${getTypeColor(indicator.type)}`}>
                                        {indicator.type}
                                    </span>
                                </td>
                                <td className="px-6 py-4 text-sm whitespace-nowrap text-gray-900">
                                    {indicator.target_value || '-'}
                                    {indicator.unit === 'percentage' && indicator.target_value ? '%' : ''}
                                </td>
                                <td className="px-6 py-4 whitespace-nowrap">{getDirectionIcon(indicator.direction)}</td>
                                <td className="px-6 py-4 whitespace-nowrap">
                                    <span
                                        className={`inline-flex rounded-full px-2 py-1 text-xs font-semibold ${
                                            indicator.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                                        }`}
                                    >
                                        {indicator.is_active ? 'Active' : 'Inactive'}
                                    </span>
                                </td>
                                <td className="px-6 py-4 text-right text-sm whitespace-nowrap">
                                    <div className="flex justify-end gap-2">
                                        <Link href={`/admin/indicators/${indicator.id}`} className="text-blue-600 hover:text-blue-700">
                                            <Eye className="h-5 w-5" />
                                        </Link>
                                        <Link href={`/admin/indicators/${indicator.id}/edit`} className="text-gray-600 hover:text-gray-700">
                                            <Edit className="h-5 w-5" />
                                        </Link>
                                        <Link
                                            href={`/admin/indicators/${indicator.id}`}
                                            method="delete"
                                            as="button"
                                            className="text-red-600 hover:text-red-700"
                                        >
                                            <Trash2 className="h-5 w-5" />
                                        </Link>
                                    </div>
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>

                {(!data || data.length === 0) && <div className="py-12 text-center text-gray-500">No indicators found.</div>}
            </div>

            {/* Pagination */}
            {meta && meta.last_page > 1 && (
                <div className="mt-6 flex items-center justify-between">
                    <div className="flex gap-2">
                        {Array.from({ length: meta.last_page }, (_, i) => i + 1).map((page) => (
                            <Link
                                key={page}
                                href={`/admin/indicators?page=${page}`}
                                className={`rounded-lg px-4 py-2 ${
                                    page === meta.current_page ? 'bg-blue-600 text-white' : 'border border-gray-300 text-gray-700 hover:bg-gray-50'
                                }`}
                            >
                                {page}
                            </Link>
                        ))}
                    </div>
                </div>
            )}
        </AdminLayout>
    );
}
