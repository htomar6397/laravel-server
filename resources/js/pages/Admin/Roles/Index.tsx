import { Head, Link, router } from '@inertiajs/react';
import { Plus, Search, Edit, Trash2, Shield, Users } from 'lucide-react';
import { useState } from 'react';
import AdminLayout from '@/Layouts/AdminLayout';

interface Role {
    id: number;
    name: string;
    description: string | null;
    is_system: boolean;
    users_count: number;
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
    roles: {
        data: Role[];
        meta: PaginationMeta;
    };
    filters: {
        search?: string;
    };
}

export default function Index({ roles, filters }: IndexProps) {
    const [search, setSearch] = useState(filters.search || '');

    const handleSearch = (e: React.FormEvent) => {
        e.preventDefault();
        router.get('/admin/roles', { search }, { preserveState: true });
    };

    const { data, meta } = roles;

    return (
        <AdminLayout header="Roles & Permissions">
            <Head title="Roles & Permissions" />

            <div className="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p className="text-gray-600">
                        Showing {meta?.from || 0} to {meta?.to || 0} of {meta?.total || 0} roles
                    </p>
                </div>
                <Link
                    href="/admin/roles/create"
                    className="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-white hover:bg-blue-700"
                >
                    <Plus className="h-5 w-5" />
                    Add Role
                </Link>
            </div>

            {/* Search */}
            <div className="mb-6 rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                <form onSubmit={handleSearch} className="flex gap-2">
                    <div className="relative flex-1">
                        <Search className="absolute top-1/2 left-3 h-5 w-5 -translate-y-1/2 text-gray-400" />
                        <input
                            type="text"
                            value={search}
                            onChange={(e) => setSearch(e.target.value)}
                            placeholder="Search roles by name or description..."
                            className="w-full rounded-lg border-gray-300 pl-10 focus:border-blue-500 focus:ring-blue-500"
                        />
                    </div>
                    <button type="submit" className="rounded-lg bg-blue-600 px-6 py-2 text-white hover:bg-blue-700">
                        Search
                    </button>
                </form>
            </div>

            {/* Roles Grid */}
            <div className="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                {data?.map((role) => (
                    <div key={role.id} className="rounded-xl border border-gray-200 bg-white p-6 shadow-sm transition-shadow hover:shadow-md">
                        <div className="mb-4 flex items-start justify-between">
                            <div className="flex items-center gap-3">
                                <div className="flex h-12 w-12 items-center justify-center rounded-lg bg-blue-100">
                                    <Shield className="h-6 w-6 text-blue-600" />
                                </div>
                                <div>
                                    <h3 className="font-semibold text-gray-900">{role.name}</h3>
                                    {role.is_system && (
                                        <span className="mt-1 inline-flex rounded-full bg-purple-100 px-2 py-1 text-xs font-semibold text-purple-800">
                                            System Role
                                        </span>
                                    )}
                                </div>
                            </div>
                        </div>

                        {role.description && <p className="mb-4 line-clamp-2 text-sm text-gray-600">{role.description}</p>}

                        <div className="mb-4 flex items-center gap-2 text-sm text-gray-600">
                            <Users className="h-4 w-4" />
                            <span>
                                {role.users_count} user{role.users_count !== 1 ? 's' : ''}
                            </span>
                        </div>

                        {!role.is_system && (
                            <div className="flex gap-2 border-t border-gray-100 pt-4">
                                <Link
                                    href={`/admin/roles/${role.id}/edit`}
                                    className="inline-flex flex-1 items-center justify-center gap-2 rounded-lg border border-gray-300 px-4 py-2 text-gray-700 hover:bg-gray-50"
                                >
                                    <Edit className="h-4 w-4" />
                                    Edit
                                </Link>
                                <Link
                                    href={`/admin/roles/${role.id}`}
                                    method="delete"
                                    as="button"
                                    className="inline-flex items-center gap-2 rounded-lg border border-red-300 px-4 py-2 text-red-700 hover:bg-red-50"
                                >
                                    <Trash2 className="h-4 w-4" />
                                </Link>
                            </div>
                        )}
                    </div>
                ))}
            </div>

            {(!data || data.length === 0) && (
                <div className="rounded-xl border border-gray-200 bg-white p-12 text-center shadow-sm">
                    <Shield className="mx-auto h-12 w-12 text-gray-400" />
                    <h3 className="mt-4 text-lg font-medium text-gray-900">No roles found</h3>
                    <p className="mt-2 text-gray-500">Get started by creating a new role.</p>
                    <Link
                        href="/admin/roles/create"
                        className="mt-4 inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-white hover:bg-blue-700"
                    >
                        <Plus className="h-5 w-5" />
                        Add Role
                    </Link>
                </div>
            )}

            {/* Pagination */}
            {meta && meta.last_page > 1 && (
                <div className="mt-6 flex items-center justify-between">
                    <div className="flex gap-2">
                        {Array.from({ length: meta.last_page }, (_, i) => i + 1).map((page) => (
                            <Link
                                key={page}
                                href={`/admin/roles?page=${page}`}
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
