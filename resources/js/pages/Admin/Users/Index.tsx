import { Head, Link, router } from '@inertiajs/react';
import { Search, Plus, Edit, Trash2, ToggleLeft, ToggleRight, Filter } from 'lucide-react';
import { useState } from 'react';
import AdminLayout from '@/Layouts/AdminLayout';

interface User {
    id: number;
    username: string;
    email: string;
    full_name: string;
    is_active: boolean;
    position: string;
    department: string;
    roles: Array<{ name: string }>;
    data_entries_count: number;
    expenditures_count: number;
    photo_captures_count: number;
    created_at: string;
}

interface UsersIndexProps {
    users: {
        data: User[];
        links: Array<{ url: string | null; label: string; active: boolean }>;
        meta: { current_page: number; last_page: number; per_page: number; total: number; from: number; to: number };
    };
    filters: { search?: string; role?: string; status?: string };
    roles: Array<{ id: number; name: string }>;
}

export default function Index({ users, filters, roles }: UsersIndexProps) {
    const [search, setSearch] = useState(filters.search || '');
    const [statusFilter, setStatusFilter] = useState(filters.status || '');
    const [roleFilter, setRoleFilter] = useState(filters.role || '');

    const handleSearch = () => {
        router.get('/admin/users', { search, status: statusFilter, role: roleFilter }, { preserveState: true });
    };

    const toggleUserStatus = (userId: number) => {
        router.post(
            `/admin/users/${userId}/toggle-status`,
            {},
            {
                preserveScroll: true,
            },
        );
    };

    const deleteUser = (userId: number) => {
        if (confirm('Are you sure you want to delete this user?')) {
            router.delete(`/admin/users/${userId}`);
        }
    };

    return (
        <AdminLayout header="User Management">
            <Head title="Users" />

            {/* Filters & Actions */}
            <div className="mb-6 rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <div className="grid grid-cols-1 gap-4 md:grid-cols-4">
                    <div className="md:col-span-2">
                        <div className="relative">
                            <Search className="absolute top-1/2 left-3 h-5 w-5 -translate-y-1/2 transform text-gray-400" />
                            <input
                                type="text"
                                placeholder="Search users..."
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

                    <select
                        value={roleFilter}
                        onChange={(e) => setRoleFilter(e.target.value)}
                        className="rounded-lg border border-gray-300 px-4 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                    >
                        <option value="">All Roles</option>
                        {roles.map((role) => (
                            <option key={role.id} value={role.name}>
                                {role.name}
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
                        href="/admin/users/create"
                        className="flex items-center gap-2 rounded-lg bg-green-600 px-4 py-2 text-white transition-colors hover:bg-green-700"
                    >
                        <Plus className="h-4 w-4" />
                        Add User
                    </Link>
                </div>
            </div>

            {/* Users Table */}
            <div className="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                <div className="overflow-x-auto">
                    <table className="w-full">
                        <thead className="border-b border-gray-200 bg-gray-50">
                            <tr>
                                <th className="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase">User</th>
                                <th className="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase">Role</th>
                                <th className="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase">Department</th>
                                <th className="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase">Activity</th>
                                <th className="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase">Status</th>
                                <th className="px-6 py-3 text-right text-xs font-medium tracking-wider text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-gray-200 bg-white">
                            {users?.data?.map((user) => (
                                <tr key={user.id} className="hover:bg-gray-50">
                                    <td className="px-6 py-4 whitespace-nowrap">
                                        <div className="flex items-center">
                                            <div className="flex h-10 w-10 items-center justify-center rounded-full bg-linear-to-br from-blue-500 to-blue-600 font-semibold text-white">
                                                {user.full_name.charAt(0)}
                                            </div>
                                            <div className="ml-4">
                                                <div className="text-sm font-medium text-gray-900">{user.full_name}</div>
                                                <div className="text-sm text-gray-500">{user.email}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td className="px-6 py-4 whitespace-nowrap">
                                        <span className="rounded-full bg-blue-100 px-2 py-1 text-xs font-medium text-blue-700">
                                            {user.roles[0]?.name || 'No Role'}
                                        </span>
                                    </td>
                                    <td className="px-6 py-4 whitespace-nowrap">
                                        <div className="text-sm text-gray-900">{user.department || '-'}</div>
                                        <div className="text-sm text-gray-500">{user.position || '-'}</div>
                                    </td>
                                    <td className="px-6 py-4 whitespace-nowrap">
                                        <div className="text-sm text-gray-900">
                                            {user.data_entries_count + user.expenditures_count + user.photo_captures_count} activities
                                        </div>
                                    </td>
                                    <td className="px-6 py-4 whitespace-nowrap">
                                        <span
                                            className={`rounded-full px-2 py-1 text-xs font-medium ${
                                                user.is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'
                                            }`}
                                        >
                                            {user.is_active ? 'Active' : 'Inactive'}
                                        </span>
                                    </td>
                                    <td className="px-6 py-4 text-right text-sm font-medium whitespace-nowrap">
                                        <div className="flex items-center justify-end gap-2">
                                            <Link href={`/admin/users/${user.id}/edit`} className="text-blue-600 hover:text-blue-900">
                                                <Edit className="h-5 w-5" />
                                            </Link>
                                            <button onClick={() => toggleUserStatus(user.id)} className="text-yellow-600 hover:text-yellow-900">
                                                {user.is_active ? <ToggleRight className="h-5 w-5" /> : <ToggleLeft className="h-5 w-5" />}
                                            </button>
                                            <button onClick={() => deleteUser(user.id)} className="text-red-600 hover:text-red-900">
                                                <Trash2 className="h-5 w-5" />
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>

                {/* Pagination */}
                {users.links && users.meta && (
                    <div className="border-t border-gray-200 bg-gray-50 px-6 py-4">
                        <div className="flex items-center justify-between">
                            <div className="text-sm text-gray-700">
                                Showing {users.meta.from || 0} to {users.meta.to || 0} of {users.meta.total || 0} users
                            </div>
                            <div className="flex gap-2">
                                {users.links.map((link, index: number) => (
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
