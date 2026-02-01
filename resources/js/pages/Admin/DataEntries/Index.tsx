import { Head, Link, router } from '@inertiajs/react';
import { Search, Filter, CheckCircle, XCircle, Eye, Download } from 'lucide-react';
import { useState } from 'react';
import AdminLayout from '@/Layouts/AdminLayout';

interface DataEntry {
    id: number;
    project: { id: number; name: string; code: string };
    indicator: { name: string };
    value: number;
    unit: string;
    data_date: string;
    verification_status: string;
    entered_by: { full_name: string };
    created_at: string;
}

interface DataEntriesIndexProps {
    dataEntries: {
        data: DataEntry[];
        links?: Array<{ url: string | null; label: string; active: boolean }>;
        meta?: { current_page: number; last_page: number; per_page: number; total: number; from: number; to: number };
    };
    filters: { search?: string; status?: string; project_id?: string };
    projects: Array<{ id: number; name: string; code: string }>;
    statuses: string[];
}

export default function Index({ dataEntries, filters, projects, statuses }: DataEntriesIndexProps) {
    const [search, setSearch] = useState(filters.search || '');
    const [statusFilter, setStatusFilter] = useState(filters.status || '');
    const [projectFilter, setProjectFilter] = useState(filters.project_id || '');
    const [selectedEntries, setSelectedEntries] = useState<number[]>([]);

    const handleSearch = () => {
        router.get('/admin/data-entries', { search, status: statusFilter, project_id: projectFilter }, { preserveState: true });
    };

    const verifyEntry = (entryId: number) => {
        router.post(
            `/admin/data-entries/${entryId}/verify`,
            {},
            {
                preserveScroll: true,
            },
        );
    };

    const rejectEntry = (entryId: number) => {
        const notes = prompt('Please provide rejection reason:');
        if (notes) {
            router.post(
                `/admin/data-entries/${entryId}/reject`,
                { verification_notes: notes },
                {
                    preserveScroll: true,
                },
            );
        }
    };

    const bulkVerify = () => {
        if (selectedEntries.length === 0) {
            alert('Please select entries to verify');
            return;
        }

        if (confirm(`Verify ${selectedEntries.length} selected entries?`)) {
            router.post(
                '/admin/data-entries/bulk-verify',
                { ids: selectedEntries },
                {
                    onSuccess: () => setSelectedEntries([]),
                },
            );
        }
    };

    const toggleSelection = (entryId: number) => {
        setSelectedEntries((prev) => (prev.includes(entryId) ? prev.filter((id) => id !== entryId) : [...prev, entryId]));
    };

    const getStatusColor = (status: string) => {
        const colors: Record<string, string> = {
            VERIFIED: 'bg-green-100 text-green-700',
            PENDING: 'bg-yellow-100 text-yellow-700',
            REJECTED: 'bg-red-100 text-red-700',
        };
        return colors[status] || 'bg-gray-100 text-gray-700';
    };

    return (
        <AdminLayout header="Data Entry Review">
            <Head title="Data Entries" />

            {/* Stats Cards */}
            <div className="mb-6 grid grid-cols-1 gap-6 md:grid-cols-4">
                <div className="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <div className="flex items-center justify-between">
                        <div>
                            <p className="text-sm text-gray-600">Total Entries</p>
                            <p className="mt-1 text-2xl font-bold text-gray-900">{dataEntries?.meta?.total || 0}</p>
                        </div>
                        <div className="flex h-12 w-12 items-center justify-center rounded-lg bg-blue-100">
                            <Eye className="h-6 w-6 text-blue-600" />
                        </div>
                    </div>
                </div>

                <div className="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <div className="flex items-center justify-between">
                        <div>
                            <p className="text-sm text-gray-600">Pending Review</p>
                            <p className="mt-1 text-2xl font-bold text-yellow-600">
                                {dataEntries?.data?.filter((e) => e.verification_status === 'PENDING').length || 0}
                            </p>
                        </div>
                        <div className="flex h-12 w-12 items-center justify-center rounded-lg bg-yellow-100">
                            <Filter className="h-6 w-6 text-yellow-600" />
                        </div>
                    </div>
                </div>

                <div className="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <div className="flex items-center justify-between">
                        <div>
                            <p className="text-sm text-gray-600">Verified</p>
                            <p className="mt-1 text-2xl font-bold text-green-600">
                                {dataEntries?.data?.filter((e) => e.verification_status === 'VERIFIED').length || 0}
                            </p>
                        </div>
                        <div className="flex h-12 w-12 items-center justify-center rounded-lg bg-green-100">
                            <CheckCircle className="h-6 w-6 text-green-600" />
                        </div>
                    </div>
                </div>

                <div className="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <div className="flex items-center justify-between">
                        <div>
                            <p className="text-sm text-gray-600">Rejected</p>
                            <p className="mt-1 text-2xl font-bold text-red-600">
                                {dataEntries?.data?.filter((e) => e.verification_status === 'REJECTED').length || 0}
                            </p>
                        </div>
                        <div className="flex h-12 w-12 items-center justify-center rounded-lg bg-red-100">
                            <XCircle className="h-6 w-6 text-red-600" />
                        </div>
                    </div>
                </div>
            </div>

            {/* Filters & Actions */}
            <div className="mb-6 rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <div className="grid grid-cols-1 gap-4 md:grid-cols-4">
                    <div className="md:col-span-2">
                        <div className="relative">
                            <Search className="absolute top-1/2 left-3 h-5 w-5 -translate-y-1/2 transform text-gray-400" />
                            <input
                                type="text"
                                placeholder="Search projects..."
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                                onKeyPress={(e) => e.key === 'Enter' && handleSearch()}
                                className="w-full rounded-lg border border-gray-300 py-2 pr-4 pl-10 focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                            />
                        </div>
                    </div>

                    <select
                        value={statusFilter}
                        onChange={(e) => setStatusFilter(e.target.value)}
                        className="rounded-lg border border-gray-300 px-4 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                    >
                        <option value="">All Status</option>
                        {statuses.map((status) => (
                            <option key={status} value={status}>
                                {status}
                            </option>
                        ))}
                    </select>

                    <select
                        value={projectFilter}
                        onChange={(e) => setProjectFilter(e.target.value)}
                        className="rounded-lg border border-gray-300 px-4 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                    >
                        <option value="">All Projects</option>
                        {projects.map((project) => (
                            <option key={project.id} value={project.id}>
                                {project.name}
                            </option>
                        ))}
                    </select>
                </div>

                <div className="mt-4 flex gap-3">
                    <button
                        onClick={handleSearch}
                        className="flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-white transition-colors hover:bg-blue-700"
                    >
                        <Filter className="h-4 w-4" />
                        Apply Filters
                    </button>

                    {selectedEntries.length > 0 && (
                        <button
                            onClick={bulkVerify}
                            className="flex items-center gap-2 rounded-lg bg-green-600 px-4 py-2 text-white transition-colors hover:bg-green-700"
                        >
                            <CheckCircle className="h-4 w-4" />
                            Verify Selected ({selectedEntries.length})
                        </button>
                    )}

                    <a
                        href="/admin/data-entries/export/csv"
                        className="flex items-center gap-2 rounded-lg bg-gray-600 px-4 py-2 text-white transition-colors hover:bg-gray-700"
                    >
                        <Download className="h-4 w-4" />
                        Export CSV
                    </a>
                </div>
            </div>

            {/* Data Entries Table */}
            <div className="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                <div className="overflow-x-auto">
                    <table className="w-full">
                        <thead className="border-b border-gray-200 bg-gray-50">
                            <tr>
                                <th className="px-6 py-3 text-left">
                                    <input
                                        type="checkbox"
                                        onChange={(e) => {
                                            if (e.target.checked) {
                                                setSelectedEntries(
                                                    dataEntries.data.filter((e) => e.verification_status === 'PENDING').map((e) => e.id),
                                                );
                                            } else {
                                                setSelectedEntries([]);
                                            }
                                        }}
                                        className="h-4 w-4 rounded text-blue-600 focus:ring-blue-500"
                                    />
                                </th>
                                <th className="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase">Project</th>
                                <th className="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase">Indicator</th>
                                <th className="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase">Value</th>
                                <th className="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase">Date</th>
                                <th className="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase">Entered By</th>
                                <th className="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase">Status</th>
                                <th className="px-6 py-3 text-right text-xs font-medium tracking-wider text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-gray-200 bg-white">
                            {dataEntries?.data?.map((entry) => (
                                <tr key={entry.id} className="hover:bg-gray-50">
                                    <td className="px-6 py-4">
                                        <input
                                            type="checkbox"
                                            checked={selectedEntries.includes(entry.id)}
                                            onChange={() => toggleSelection(entry.id)}
                                            disabled={entry.verification_status !== 'PENDING'}
                                            className="h-4 w-4 rounded text-blue-600 focus:ring-blue-500"
                                        />
                                    </td>
                                    <td className="px-6 py-4 whitespace-nowrap">
                                        <div className="text-sm font-medium text-gray-900">{entry.project.name}</div>
                                        <div className="text-sm text-gray-500">{entry.project.code}</div>
                                    </td>
                                    <td className="px-6 py-4">
                                        <div className="text-sm text-gray-900">{entry.indicator.name}</div>
                                    </td>
                                    <td className="px-6 py-4 whitespace-nowrap">
                                        <div className="text-sm font-semibold text-gray-900">
                                            {entry.value} {entry.unit}
                                        </div>
                                    </td>
                                    <td className="px-6 py-4 whitespace-nowrap">
                                        <div className="text-sm text-gray-900">{entry.data_date}</div>
                                    </td>
                                    <td className="px-6 py-4 whitespace-nowrap">
                                        <div className="text-sm text-gray-900">{entry.entered_by.full_name}</div>
                                    </td>
                                    <td className="px-6 py-4 whitespace-nowrap">
                                        <span className={`rounded-full px-2 py-1 text-xs font-medium ${getStatusColor(entry.verification_status)}`}>
                                            {entry.verification_status}
                                        </span>
                                    </td>
                                    <td className="px-6 py-4 text-right text-sm font-medium whitespace-nowrap">
                                        <div className="flex items-center justify-end gap-2">
                                            <Link href={`/admin/data-entries/${entry.id}`} className="text-blue-600 hover:text-blue-900">
                                                <Eye className="h-5 w-5" />
                                            </Link>
                                            {entry.verification_status === 'PENDING' && (
                                                <>
                                                    <button onClick={() => verifyEntry(entry.id)} className="text-green-600 hover:text-green-900">
                                                        <CheckCircle className="h-5 w-5" />
                                                    </button>
                                                    <button onClick={() => rejectEntry(entry.id)} className="text-red-600 hover:text-red-900">
                                                        <XCircle className="h-5 w-5" />
                                                    </button>
                                                </>
                                            )}
                                        </div>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>

                {/* Pagination */}
                {dataEntries?.links && dataEntries?.meta && (
                    <div className="border-t border-gray-200 bg-gray-50 px-6 py-4">
                        <div className="flex items-center justify-between">
                            <div className="text-sm text-gray-700">
                                Showing {dataEntries?.meta?.from || 0} to {dataEntries?.meta?.to || 0} of {dataEntries?.meta?.total || 0} entries
                            </div>
                            <div className="flex gap-2">
                                {dataEntries.links.map((link, index: number) => (
                                    <Link
                                        key={index}
                                        href={link.url || '#'}
                                        className={`rounded px-3 py-1 ${
                                            link.active ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100'
                                        } ${!link.url ? 'cursor-not-allowed opacity-50' : ''}`}
                                        dangerouslySetInnerHTML={{ __html: link.label }}
                                    />
                                ))}
                            </div>
                        </div>
                    </div>
                )}
            </div>
        </AdminLayout>
    );
}
