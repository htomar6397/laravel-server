import { Head, Link, router } from '@inertiajs/react';
import { Search, Plus, Edit, Trash2, Palette } from 'lucide-react';
import { useState } from 'react';
import AdminLayout from '@/Layouts/AdminLayout';

interface Theme {
    id: number;
    code: string;
    name: string;
    description?: string;
    color: string;
    icon?: string;
    sort_order: number;
    is_active: boolean;
    projects_count: number;
    created_at: string;
}

interface ThemesIndexProps {
    themes: {
        data: Theme[];
        links?: Array<{ url: string | null; label: string; active: boolean }>;
        meta?: { current_page: number; last_page: number; per_page: number; total: number; from: number; to: number };
    };
    filters: { search?: string; status?: string };
}

export default function Index({ themes, filters }: ThemesIndexProps) {
    const [search, setSearch] = useState(filters.search || '');
    const [statusFilter, setStatusFilter] = useState(filters.status || '');

    const handleSearch = () => {
        router.get('/admin/themes', { search, status: statusFilter }, { preserveState: true });
    };

    const deleteTheme = (themeId: number) => {
        if (confirm('Are you sure you want to delete this theme?')) {
            router.delete(`/admin/themes/${themeId}`);
        }
    };

    return (
        <AdminLayout header="Theme Management">
            <Head title="Themes" />

            {/* Filters & Actions */}
            <div className="mb-6 rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <div className="mb-4 flex items-center justify-between">
                    <h2 className="text-lg font-semibold text-gray-900">Manage Themes</h2>
                    <Link
                        href="/admin/themes/create"
                        className="flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-white hover:bg-blue-700"
                    >
                        <Plus className="h-4 w-4" />
                        Add Theme
                    </Link>
                </div>

                <div className="grid grid-cols-1 gap-4 md:grid-cols-3">
                    <div className="md:col-span-2">
                        <div className="relative">
                            <Search className="absolute top-1/2 left-3 h-5 w-5 -translate-y-1/2 transform text-gray-400" />
                            <input
                                type="text"
                                placeholder="Search themes..."
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
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>

            {/* Themes Grid */}
            <div className="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                {themes.data.map((theme) => (
                    <div key={theme.id} className="rounded-xl border border-gray-200 bg-white p-6 shadow-sm transition-shadow hover:shadow-md">
                        <div className="mb-4 flex items-start justify-between">
                            <div className="flex h-12 w-12 items-center justify-center rounded-lg" style={{ backgroundColor: theme.color + '20' }}>
                                <Palette className="h-6 w-6" style={{ color: theme.color }} />
                            </div>
                            <div className="flex items-center gap-2">
                                <Link href={`/admin/themes/${theme.id}/edit`} className="text-blue-600 hover:text-blue-900">
                                    <Edit className="h-4 w-4" />
                                </Link>
                                <button onClick={() => deleteTheme(theme.id)} className="text-red-600 hover:text-red-900">
                                    <Trash2 className="h-4 w-4" />
                                </button>
                            </div>
                        </div>

                        <h3 className="mb-2 text-lg font-semibold text-gray-900">{theme.name}</h3>
                        <p className="mb-2 text-xs font-medium text-gray-500">Code: {theme.code}</p>
                        {theme.description && <p className="mb-4 text-sm text-gray-600">{theme.description}</p>}

                        <div className="flex items-center justify-between border-t border-gray-200 pt-4">
                            <div className="flex items-center gap-2">
                                <span className="text-xs text-gray-500">{theme.projects_count} projects</span>
                                <span className="h-1 w-1 rounded-full bg-gray-300"></span>
                                <span className={`text-xs font-medium ${theme.is_active ? 'text-green-600' : 'text-red-600'}`}>
                                    {theme.is_active ? 'Active' : 'Inactive'}
                                </span>
                            </div>
                            <div
                                className="h-6 w-6 rounded-full border-2 border-gray-200"
                                style={{ backgroundColor: theme.color }}
                                title={theme.color}
                            ></div>
                        </div>
                    </div>
                ))}
            </div>

            {/* Pagination */}
            {themes.data.length > 0 && themes?.meta && themes?.links && (
                <div className="mt-6 flex items-center justify-between rounded-xl border border-gray-200 bg-white px-6 py-3">
                    <div className="text-sm text-gray-700">
                        Showing <span className="font-medium">{themes?.meta?.from || 0}</span> to{' '}
                        <span className="font-medium">{themes?.meta?.to || 0}</span> of{' '}
                        <span className="font-medium">{themes?.meta?.total || 0}</span> results
                    </div>
                    <div className="flex gap-2">
                        {themes.links.map((link, index) => (
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

            {themes.data.length === 0 && (
                <div className="rounded-xl border border-gray-200 bg-white p-12 text-center">
                    <Palette className="mx-auto mb-4 h-12 w-12 text-gray-400" />
                    <h3 className="mb-2 text-lg font-semibold text-gray-900">No themes found</h3>
                    <p className="text-gray-600">Get started by creating your first theme.</p>
                </div>
            )}
        </AdminLayout>
    );
}
