import { Head, Link, router } from '@inertiajs/react';
import { Search, Plus, Edit, Eye, Trash2, Filter } from 'lucide-react';
import { useState } from 'react';
import AdminLayout from '@/Layouts/AdminLayout';

interface Project {
    id: number;
    code: string;
    name: string;
    status: string;
    budget: number;
    currency: string;
    completion_percentage: number;
    theme?: { name: string };
    start_date: string;
    end_date: string;
    data_entries_count: number;
    expenditures_count: number;
    photo_captures_count: number;
}

interface ProjectsIndexProps {
    projects: {
        data: Project[];
        links: Array<{ url: string | null; label: string; active: boolean }>;
        meta: { current_page: number; last_page: number; per_page: number; total: number; from: number; to: number };
    };
    filters: { search?: string; status?: string; theme?: string };
    themes: Array<{ id: number; name: string }>;
    statuses: string[];
}

export default function Index({ projects, filters, themes, statuses }: ProjectsIndexProps) {
    const [search, setSearch] = useState(filters.search || '');
    const [statusFilter, setStatusFilter] = useState(filters.status || '');
    const [themeFilter, setThemeFilter] = useState(filters.theme || '');

    const handleSearch = () => {
        router.get('/admin/projects', { search, status: statusFilter, theme: themeFilter }, { preserveState: true });
    };

    const deleteProject = (projectId: number) => {
        if (confirm('Are you sure you want to delete this project?')) {
            router.delete(`/admin/projects/${projectId}`);
        }
    };

    const getStatusColor = (status: string) => {
        const colors: Record<string, string> = {
            PLANNING: 'bg-gray-100 text-gray-700',
            ACTIVE: 'bg-green-100 text-green-700',
            SUSPENDED: 'bg-yellow-100 text-yellow-700',
            COMPLETED: 'bg-blue-100 text-blue-700',
            CANCELLED: 'bg-red-100 text-red-700',
        };
        return colors[status] || 'bg-gray-100 text-gray-700';
    };

    return (
        <AdminLayout header="Project Management">
            <Head title="Projects" />

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
                        value={themeFilter}
                        onChange={(e) => setThemeFilter(e.target.value)}
                        className="rounded-lg border border-gray-300 px-4 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                    >
                        <option value="">All Themes</option>
                        {themes.map((theme) => (
                            <option key={theme.id} value={theme.id}>
                                {theme.name}
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

                    <Link
                        href="/admin/projects/create"
                        className="flex items-center gap-2 rounded-lg bg-green-600 px-4 py-2 text-white transition-colors hover:bg-green-700"
                    >
                        <Plus className="h-4 w-4" />
                        New Project
                    </Link>
                </div>
            </div>

            {/* Projects Grid */}
            <div className="grid grid-cols-1 gap-6 lg:grid-cols-2 xl:grid-cols-3">
                {projects?.data?.map((project) => (
                    <div key={project.id} className="rounded-xl border border-gray-200 bg-white p-6 shadow-sm transition-all hover:shadow-md">
                        <div className="mb-4 flex items-start justify-between">
                            <div className="flex-1">
                                <div className="mb-2 flex items-center gap-2">
                                    <span className="text-xs font-medium text-gray-500">{project.code}</span>
                                    <span className={`rounded-full px-2 py-1 text-xs font-medium ${getStatusColor(project.status)}`}>
                                        {project.status}
                                    </span>
                                </div>
                                <h3 className="line-clamp-2 text-lg font-semibold text-gray-900">{project.name}</h3>
                                {project.theme && <p className="mt-1 text-sm text-gray-500">{project.theme.name}</p>}
                            </div>
                        </div>

                        {/* Budget & Progress */}
                        <div className="mb-4">
                            <div className="mb-2 flex items-center justify-between text-sm">
                                <span className="text-gray-600">Progress</span>
                                <span className="font-semibold text-gray-900">{project.completion_percentage}%</span>
                            </div>
                            <div className="h-2 w-full rounded-full bg-gray-200">
                                <div className="h-2 rounded-full bg-blue-600 transition-all" style={{ width: `${project.completion_percentage}%` }} />
                            </div>

                            <div className="mt-3 flex items-center justify-between">
                                <span className="text-xs text-gray-500">Budget</span>
                                <span className="text-sm font-semibold text-gray-900">
                                    {(project.budget / 1000000).toFixed(1)}M {project.currency}
                                </span>
                            </div>
                        </div>

                        {/* Activity Stats */}
                        <div className="grid grid-cols-3 gap-3 border-t border-gray-200 py-3">
                            <div className="text-center">
                                <p className="text-lg font-bold text-gray-900">{project.data_entries_count}</p>
                                <p className="text-xs text-gray-500">Data Entries</p>
                            </div>
                            <div className="text-center">
                                <p className="text-lg font-bold text-gray-900">{project.expenditures_count}</p>
                                <p className="text-xs text-gray-500">Expenditures</p>
                            </div>
                            <div className="text-center">
                                <p className="text-lg font-bold text-gray-900">{project.photo_captures_count}</p>
                                <p className="text-xs text-gray-500">Photos</p>
                            </div>
                        </div>

                        {/* Actions */}
                        <div className="mt-4 flex items-center gap-2 border-t border-gray-200 pt-4">
                            <Link
                                href={`/admin/projects/${project.id}`}
                                className="flex flex-1 items-center justify-center gap-2 rounded-lg bg-blue-50 px-3 py-2 text-center text-sm font-medium text-blue-600 transition-colors hover:bg-blue-100"
                            >
                                <Eye className="h-4 w-4" />
                                View
                            </Link>
                            <Link
                                href={`/admin/projects/${project.id}/edit`}
                                className="flex flex-1 items-center justify-center gap-2 rounded-lg bg-gray-50 px-3 py-2 text-center text-sm font-medium text-gray-600 transition-colors hover:bg-gray-100"
                            >
                                <Edit className="h-4 w-4" />
                                Edit
                            </Link>
                            <button
                                onClick={() => deleteProject(project.id)}
                                className="flex items-center justify-center rounded-lg bg-red-50 px-3 py-2 text-red-600 transition-colors hover:bg-red-100"
                            >
                                <Trash2 className="h-4 w-4" />
                            </button>
                        </div>
                    </div>
                ))}
            </div>

            {/* Pagination */}
            {projects.links && projects.meta && (
                <div className="mt-6 rounded-xl border border-gray-200 bg-white px-6 py-4 shadow-sm">
                    <div className="flex items-center justify-between">
                        <div className="text-sm text-gray-700">
                            Showing {projects.meta.from || 0} to {projects.meta.to || 0} of {projects.meta.total} projects
                        </div>
                        <div className="flex gap-2">
                            {projects.links.map((link, index: number) => (
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
        </AdminLayout>
    );
}
