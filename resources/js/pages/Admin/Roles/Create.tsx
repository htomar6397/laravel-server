import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowLeft, Save } from 'lucide-react';
import AdminLayout from '@/Layouts/AdminLayout';

interface CreateProps {
    availablePermissions: Record<string, Record<string, string>>;
}

export default function Create({ availablePermissions }: CreateProps) {
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        description: '',
        permissions: [] as string[],
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post('/admin/roles');
    };

    const togglePermission = (permission: string) => {
        if (data.permissions.includes(permission)) {
            setData(
                'permissions',
                data.permissions.filter((p) => p !== permission),
            );
        } else {
            setData('permissions', [...data.permissions, permission]);
        }
    };

    const toggleCategory = (category: Record<string, string>) => {
        const categoryPermissions = Object.keys(category);
        const allSelected = categoryPermissions.every((p) => data.permissions.includes(p));

        if (allSelected) {
            setData(
                'permissions',
                data.permissions.filter((p) => !categoryPermissions.includes(p)),
            );
        } else {
            setData('permissions', [...new Set([...data.permissions, ...categoryPermissions])]);
        }
    };

    const isCategorySelected = (category: Record<string, string>) => {
        return Object.keys(category).every((p) => data.permissions.includes(p));
    };

    return (
        <AdminLayout header="Create Role">
            <Head title="Create Role" />

            <div className="mb-6">
                <Link href="/admin/roles" className="inline-flex items-center gap-2 text-gray-600 hover:text-gray-900">
                    <ArrowLeft className="h-4 w-4" />
                    Back to Roles
                </Link>
            </div>

            <form onSubmit={handleSubmit} className="space-y-6">
                {/* Basic Information */}
                <div className="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <h3 className="mb-4 text-lg font-semibold text-gray-900">Basic Information</h3>

                    <div className="space-y-4">
                        <div>
                            <label className="block text-sm font-medium text-gray-700">
                                Role Name <span className="text-red-500">*</span>
                            </label>
                            <input
                                type="text"
                                value={data.name}
                                onChange={(e) => setData('name', e.target.value)}
                                className="mt-1 block w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                                placeholder="e.g., Project Manager"
                            />
                            {errors.name && <p className="mt-1 text-sm text-red-600">{errors.name}</p>}
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-gray-700">Description</label>
                            <textarea
                                value={data.description}
                                onChange={(e) => setData('description', e.target.value)}
                                rows={3}
                                className="mt-1 block w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                                placeholder="Describe the responsibilities of this role..."
                            />
                            {errors.description && <p className="mt-1 text-sm text-red-600">{errors.description}</p>}
                        </div>
                    </div>
                </div>

                {/* Permissions */}
                <div className="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <h3 className="mb-4 text-lg font-semibold text-gray-900">Permissions ({data.permissions.length} selected)</h3>

                    <div className="space-y-6">
                        {Object.entries(availablePermissions).map(([categoryName, permissions]) => (
                            <div key={categoryName} className="border-b border-gray-100 pb-4 last:border-0">
                                <div className="mb-3 flex items-center justify-between">
                                    <h4 className="font-medium text-gray-900">{categoryName}</h4>
                                    <button
                                        type="button"
                                        onClick={() => toggleCategory(permissions)}
                                        className="text-sm text-blue-600 hover:text-blue-700"
                                    >
                                        {isCategorySelected(permissions) ? 'Deselect All' : 'Select All'}
                                    </button>
                                </div>

                                <div className="grid grid-cols-1 gap-2 md:grid-cols-2 lg:grid-cols-3">
                                    {Object.entries(permissions).map(([permission, label]) => (
                                        <label
                                            key={permission}
                                            className="flex cursor-pointer items-center gap-2 rounded-lg border border-gray-200 p-3 hover:bg-gray-50"
                                        >
                                            <input
                                                type="checkbox"
                                                checked={data.permissions.includes(permission)}
                                                onChange={() => togglePermission(permission)}
                                                className="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                            />
                                            <span className="text-sm text-gray-700">{label}</span>
                                        </label>
                                    ))}
                                </div>
                            </div>
                        ))}
                    </div>
                </div>

                {/* Actions */}
                <div className="flex justify-end gap-4">
                    <Link href="/admin/roles" className="rounded-lg border border-gray-300 px-6 py-2 text-gray-700 hover:bg-gray-50">
                        Cancel
                    </Link>
                    <button
                        type="submit"
                        disabled={processing}
                        className="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-6 py-2 text-white hover:bg-blue-700 disabled:opacity-50"
                    >
                        <Save className="h-5 w-5" />
                        {processing ? 'Saving...' : 'Save Role'}
                    </button>
                </div>
            </form>
        </AdminLayout>
    );
}
