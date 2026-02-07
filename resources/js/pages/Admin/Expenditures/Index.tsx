import { Head, Link, router } from '@inertiajs/react';
import { Search, Filter, CheckCircle, XCircle, Eye, Download, DollarSign } from 'lucide-react';
import { useState } from 'react';
import AdminLayout from '@/Layouts/AdminLayout';

interface Expenditure {
    id: number;
    code: string;
    project: { id: number; name: string };
    description: string;
    amount: number;
    currency: string;
    expenditure_date: string;
    category: string;
    vendor: string;
    invoice_number: string;
    status: string;
    entered_by: { full_name: string };
    approved_by?: { full_name: string };
    created_at: string;
}

interface ExpendituresIndexProps {
    expenditures: {
        data: Expenditure[];
        links: Array<{ url: string | null; label: string; active: boolean }>;
        meta: { current_page: number; last_page: number; per_page: number; total: number; from: number; to: number };
    };
    filters: Record<string, unknown>;
    projects: Array<{ id: number; name: string }>;
    statuses: string[];
    categories: string[];
}

export default function Index({ expenditures, filters, projects, statuses, categories }: ExpendituresIndexProps) {
    const [search, setSearch] = useState(filters.search || '');
    const [statusFilter, setStatusFilter] = useState(filters.status || '');
    const [categoryFilter, setCategoryFilter] = useState(filters.category || '');
    const [projectFilter, setProjectFilter] = useState(filters.project_id || '');
    const [selectedExpenditures, setSelectedExpenditures] = useState<number[]>([]);

    const handleSearch = () => {
        router.get(
            '/admin/expenditures',
            { search, status: statusFilter, category: categoryFilter, project_id: projectFilter },
            { preserveState: true },
        );
    };

    const approveExpenditure = (expenditureId: number) => {
        const notes = prompt('Add approval notes (optional):');
        router.post(
            `/admin/expenditures/${expenditureId}/approve`,
            { approval_notes: notes },
            {
                preserveScroll: true,
            },
        );
    };

    const rejectExpenditure = (expenditureId: number) => {
        const notes = prompt('Please provide rejection reason:');
        if (notes) {
            router.post(
                `/admin/expenditures/${expenditureId}/reject`,
                { approval_notes: notes },
                {
                    preserveScroll: true,
                },
            );
        }
    };

    const bulkApprove = () => {
        if (selectedExpenditures.length === 0) {
            alert('Please select expenditures to approve');
            return;
        }

        if (confirm(`Approve ${selectedExpenditures.length} selected expenditures?`)) {
            router.post(
                '/admin/expenditures/bulk-approve',
                { ids: selectedExpenditures },
                {
                    onSuccess: () => setSelectedExpenditures([]),
                },
            );
        }
    };

    const toggleSelection = (expenditureId: number) => {
        setSelectedExpenditures((prev) => (prev.includes(expenditureId) ? prev.filter((id) => id !== expenditureId) : [...prev, expenditureId]));
    };

    const getStatusColor = (status: string) => {
        const colors: Record<string, string> = {
            APPROVED: 'bg-green-100 text-green-700',
            VERIFIED: 'bg-blue-100 text-blue-700',
            PENDING: 'bg-yellow-100 text-yellow-700',
            REJECTED: 'bg-red-100 text-red-700',
        };
        return colors[status] || 'bg-gray-100 text-gray-700';
    };

    const formatCurrency = (amount: number, currency: string) => {
        return new Intl.NumberFormat('en-TZ', {
            style: 'currency',
            currency: currency,
        }).format(amount);
    };

    // Calculate totals
    const totalPending = expenditures.data.filter((e) => e.status === 'PENDING').reduce((sum, e) => sum + Number(e.amount), 0);

    const totalApproved = expenditures.data
        .filter((e) => e.status === 'APPROVED' || e.status === 'VERIFIED')
        .reduce((sum, e) => sum + Number(e.amount), 0);

    return (
        <AdminLayout header="Expenditure Management">
            <Head title="Expenditures" />

            {/* Stats Cards */}
            <div className="mb-6 grid grid-cols-1 gap-6 md:grid-cols-4">
                <div className="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <div className="flex items-center justify-between">
                        <div>
                            <p className="text-sm text-gray-600">Total Expenditures</p>
                            <p className="mt-1 text-2xl font-bold text-gray-900">{expenditures?.meta?.total || 0}</p>
                        </div>
                        <div className="flex h-12 w-12 items-center justify-center rounded-lg bg-blue-100">
                            <DollarSign className="h-6 w-6 text-blue-600" />
                        </div>
                    </div>
                </div>

                <div className="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <div className="flex items-center justify-between">
                        <div>
                            <p className="text-sm text-gray-600">Pending Approval</p>
                            <p className="mt-1 text-2xl font-bold text-yellow-600">
                                {expenditures.data.filter((e) => e.status === 'PENDING').length}
                            </p>
                            <p className="mt-1 text-xs text-gray-500">{formatCurrency(totalPending, 'TZS')}</p>
                        </div>
                        <div className="flex h-12 w-12 items-center justify-center rounded-lg bg-yellow-100">
                            <Filter className="h-6 w-6 text-yellow-600" />
                        </div>
                    </div>
                </div>

                <div className="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <div className="flex items-center justify-between">
                        <div>
                            <p className="text-sm text-gray-600">Approved</p>
                            <p className="mt-1 text-2xl font-bold text-green-600">
                                {expenditures.data.filter((e) => e.status === 'APPROVED' || e.status === 'VERIFIED').length}
                            </p>
                            <p className="mt-1 text-xs text-gray-500">{formatCurrency(totalApproved, 'TZS')}</p>
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
                            <p className="mt-1 text-2xl font-bold text-red-600">{expenditures.data.filter((e) => e.status === 'REJECTED').length}</p>
                        </div>
                        <div className="flex h-12 w-12 items-center justify-center rounded-lg bg-red-100">
                            <XCircle className="h-6 w-6 text-red-600" />
                        </div>
                    </div>
                </div>
            </div>

            {/* Filters & Actions */}
            <div className="mb-6 rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <div className="grid grid-cols-1 gap-4 md:grid-cols-5">
                    <div className="md:col-span-2">
                        <div className="relative">
                            <Search className="absolute top-1/2 left-3 h-5 w-5 -translate-y-1/2 transform text-gray-400" />
                            <input
                                type="text"
                                placeholder="Search expenditures..."
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
                        value={categoryFilter}
                        onChange={(e) => setCategoryFilter(e.target.value)}
                        className="rounded-lg border border-gray-300 px-4 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                    >
                        <option value="">All Categories</option>
                        {categories.map((category) => (
                            <option key={category} value={category}>
                                {category}
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

                    {selectedExpenditures.length > 0 && (
                        <button
                            onClick={bulkApprove}
                            className="flex items-center gap-2 rounded-lg bg-green-600 px-4 py-2 text-white transition-colors hover:bg-green-700"
                        >
                            <CheckCircle className="h-4 w-4" />
                            Approve Selected ({selectedExpenditures.length})
                        </button>
                    )}

                    <a
                        href="/admin/expenditures/export/csv"
                        className="flex items-center gap-2 rounded-lg bg-gray-600 px-4 py-2 text-white transition-colors hover:bg-gray-700"
                    >
                        <Download className="h-4 w-4" />
                        Export CSV
                    </a>
                </div>
            </div>

            {/* Expenditures Table */}
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
                                                setSelectedExpenditures(expenditures.data.filter((e) => e.status === 'PENDING').map((e) => e.id));
                                            } else {
                                                setSelectedExpenditures([]);
                                            }
                                        }}
                                        className="h-4 w-4 rounded text-blue-600 focus:ring-blue-500"
                                    />
                                </th>
                                <th className="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase">Code</th>
                                <th className="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase">Project</th>
                                <th className="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase">Description</th>
                                <th className="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase">Amount</th>
                                <th className="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase">Category</th>
                                <th className="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase">Date</th>
                                <th className="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase">Status</th>
                                <th className="px-6 py-3 text-right text-xs font-medium tracking-wider text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-gray-200 bg-white">
                            {expenditures?.data?.map((expenditure) => (
                                <tr key={expenditure.id} className="hover:bg-gray-50">
                                    <td className="px-6 py-4">
                                        <input
                                            type="checkbox"
                                            checked={selectedExpenditures.includes(expenditure.id)}
                                            onChange={() => toggleSelection(expenditure.id)}
                                            disabled={expenditure.status !== 'PENDING'}
                                            className="h-4 w-4 rounded text-blue-600 focus:ring-blue-500"
                                        />
                                    </td>
                                    <td className="px-6 py-4 whitespace-nowrap">
                                        <div className="text-sm font-medium text-gray-900">{expenditure.code}</div>
                                    </td>
                                    <td className="px-6 py-4 whitespace-nowrap">
                                        <div className="text-sm text-gray-900">{expenditure.project.name}</div>
                                    </td>
                                    <td className="px-6 py-4">
                                        <div className="max-w-xs truncate text-sm text-gray-900">{expenditure.description}</div>
                                        {expenditure.vendor && <div className="text-xs text-gray-500">Vendor: {expenditure.vendor}</div>}
                                    </td>
                                    <td className="px-6 py-4 whitespace-nowrap">
                                        <div className="text-sm font-semibold text-gray-900">
                                            {formatCurrency(Number(expenditure.amount), expenditure.currency)}
                                        </div>
                                    </td>
                                    <td className="px-6 py-4 whitespace-nowrap">
                                        <span className="rounded-full bg-gray-100 px-2 py-1 text-xs font-medium text-gray-700">
                                            {expenditure.category}
                                        </span>
                                    </td>
                                    <td className="px-6 py-4 whitespace-nowrap">
                                        <div className="text-sm text-gray-900">{expenditure.expenditure_date}</div>
                                    </td>
                                    <td className="px-6 py-4 whitespace-nowrap">
                                        <span className={`rounded-full px-2 py-1 text-xs font-medium ${getStatusColor(expenditure.status)}`}>
                                            {expenditure.status}
                                        </span>
                                    </td>
                                    <td className="px-6 py-4 text-right text-sm font-medium whitespace-nowrap">
                                        <div className="flex items-center justify-end gap-2">
                                            <Link href={`/admin/expenditures/${expenditure.id}`} className="text-blue-600 hover:text-blue-900">
                                                <Eye className="h-5 w-5" />
                                            </Link>
                                            {expenditure.status === 'PENDING' && (
                                                <>
                                                    <button
                                                        onClick={() => approveExpenditure(expenditure.id)}
                                                        className="text-green-600 hover:text-green-900"
                                                    >
                                                        <CheckCircle className="h-5 w-5" />
                                                    </button>
                                                    <button
                                                        onClick={() => rejectExpenditure(expenditure.id)}
                                                        className="text-red-600 hover:text-red-900"
                                                    >
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
                {expenditures.links && expenditures.meta && (
                    <div className="border-t border-gray-200 bg-gray-50 px-6 py-4">
                        <div className="flex items-center justify-between">
                            <div className="text-sm text-gray-700">
                                Showing {expenditures.meta.from || 0} to {expenditures.meta.to || 0} of {expenditures.meta.total || 0} expenditures
                            </div>
                            <div className="flex gap-2">
                                {expenditures.links.map((link: { url: string | null; label: string; active: boolean }, index: number) => (
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
