import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowLeft, Save } from 'lucide-react';
import AdminLayout from '@/Layouts/AdminLayout';

interface Role {
    id: number;
    name: string;
    description?: string;
}

interface CreateProps {
    roles: Role[];
}

export default function Create({ roles }: CreateProps) {
    const { data, setData, post, processing, errors } = useForm({
        username: '',
        email: '',
        full_name: '',
        password: '',
        password_confirmation: '',
        phone: '',
        department: '',
        position: '',
        org_unit_id: '',
        is_active: true,
        roles: [] as number[],
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post('/admin/users');
    };

    const toggleRole = (roleId: number) => {
        if (data.roles.includes(roleId)) {
            setData(
                'roles',
                data.roles.filter((id) => id !== roleId),
            );
        } else {
            setData('roles', [...data.roles, roleId]);
        }
    };

    return (
        <AdminLayout header="Create New User">
            <Head title="Create User" />

            <div className="mb-6">
                <Link href="/admin/users" className="inline-flex items-center gap-2 text-gray-600 hover:text-gray-900">
                    <ArrowLeft className="h-4 w-4" />
                    Back to Users
                </Link>
            </div>

            <form onSubmit={handleSubmit} className="space-y-6">
                {/* Basic Information */}
                <div className="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <h3 className="mb-4 text-lg font-semibold text-gray-900">Basic Information</h3>

                    <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <label className="mb-2 block text-sm font-medium text-gray-700">Username *</label>
                            <input
                                type="text"
                                value={data.username}
                                onChange={(e) => setData('username', e.target.value)}
                                className="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                                required
                            />
                            {errors.username && <p className="mt-1 text-sm text-red-600">{errors.username}</p>}
                        </div>

                        <div>
                            <label className="mb-2 block text-sm font-medium text-gray-700">Email *</label>
                            <input
                                type="email"
                                value={data.email}
                                onChange={(e) => setData('email', e.target.value)}
                                className="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                                required
                            />
                            {errors.email && <p className="mt-1 text-sm text-red-600">{errors.email}</p>}
                        </div>

                        <div>
                            <label className="mb-2 block text-sm font-medium text-gray-700">Full Name *</label>
                            <input
                                type="text"
                                value={data.full_name}
                                onChange={(e) => setData('full_name', e.target.value)}
                                className="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                                required
                            />
                            {errors.full_name && <p className="mt-1 text-sm text-red-600">{errors.full_name}</p>}
                        </div>

                        <div>
                            <label className="mb-2 block text-sm font-medium text-gray-700">Phone</label>
                            <input
                                type="text"
                                value={data.phone}
                                onChange={(e) => setData('phone', e.target.value)}
                                className="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                            />
                            {errors.phone && <p className="mt-1 text-sm text-red-600">{errors.phone}</p>}
                        </div>

                        <div>
                            <label className="mb-2 block text-sm font-medium text-gray-700">Department</label>
                            <input
                                type="text"
                                value={data.department}
                                onChange={(e) => setData('department', e.target.value)}
                                className="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                            />
                            {errors.department && <p className="mt-1 text-sm text-red-600">{errors.department}</p>}
                        </div>

                        <div>
                            <label className="mb-2 block text-sm font-medium text-gray-700">Position</label>
                            <input
                                type="text"
                                value={data.position}
                                onChange={(e) => setData('position', e.target.value)}
                                className="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                            />
                            {errors.position && <p className="mt-1 text-sm text-red-600">{errors.position}</p>}
                        </div>
                    </div>
                </div>

                {/* Password */}
                <div className="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <h3 className="mb-4 text-lg font-semibold text-gray-900">Password</h3>

                    <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <label className="mb-2 block text-sm font-medium text-gray-700">Password *</label>
                            <input
                                type="password"
                                value={data.password}
                                onChange={(e) => setData('password', e.target.value)}
                                className="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                                required
                            />
                            {errors.password && <p className="mt-1 text-sm text-red-600">{errors.password}</p>}
                        </div>

                        <div>
                            <label className="mb-2 block text-sm font-medium text-gray-700">Confirm Password *</label>
                            <input
                                type="password"
                                value={data.password_confirmation}
                                onChange={(e) => setData('password_confirmation', e.target.value)}
                                className="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                                required
                            />
                        </div>
                    </div>
                </div>

                {/* Roles */}
                <div className="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <h3 className="mb-4 text-lg font-semibold text-gray-900">Roles *</h3>

                    <div className="space-y-2">
                        {roles.map((role) => (
                            <label key={role.id} className="flex items-center gap-3 rounded-lg border border-gray-200 p-3 hover:bg-gray-50">
                                <input
                                    type="checkbox"
                                    checked={data.roles.includes(role.id)}
                                    onChange={() => toggleRole(role.id)}
                                    className="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                />
                                <div>
                                    <div className="font-medium text-gray-900">{role.name}</div>
                                    {role.description && <div className="text-sm text-gray-500">{role.description}</div>}
                                </div>
                            </label>
                        ))}
                    </div>
                    {errors.roles && <p className="mt-2 text-sm text-red-600">{errors.roles}</p>}
                </div>

                {/* Status */}
                <div className="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <h3 className="mb-4 text-lg font-semibold text-gray-900">Status</h3>

                    <label className="flex items-center gap-3">
                        <input
                            type="checkbox"
                            checked={data.is_active}
                            onChange={(e) => setData('is_active', e.target.checked)}
                            className="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                        />
                        <span className="text-sm font-medium text-gray-700">Active User</span>
                    </label>
                </div>

                {/* Actions */}
                <div className="flex justify-end gap-4">
                    <Link href="/admin/users" className="rounded-lg border border-gray-300 px-6 py-2 text-gray-700 hover:bg-gray-50">
                        Cancel
                    </Link>
                    <button
                        type="submit"
                        disabled={processing}
                        className="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-6 py-2 text-white hover:bg-blue-700 disabled:opacity-50"
                    >
                        <Save className="h-4 w-4" />
                        Create User
                    </button>
                </div>
            </form>
        </AdminLayout>
    );
}
