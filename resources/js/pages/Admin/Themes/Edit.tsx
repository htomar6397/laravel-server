import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowLeft, Save } from 'lucide-react';
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
}

interface EditProps {
    theme: Theme;
}

export default function Edit({ theme }: EditProps) {
    const { data, setData, put, processing, errors } = useForm({
        code: theme.code || '',
        name: theme.name || '',
        description: theme.description || '',
        color: theme.color || '#3B82F6',
        icon: theme.icon || '',
        sort_order: theme.sort_order || 0,
        is_active: theme.is_active ?? true,
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        put(`/admin/themes/${theme.id}`);
    };

    return (
        <AdminLayout header="Edit Theme">
            <Head title="Edit Theme" />

            <div className="mb-6">
                <Link href="/admin/themes" className="inline-flex items-center gap-2 text-gray-600 hover:text-gray-900">
                    <ArrowLeft className="h-4 w-4" />
                    Back to Themes
                </Link>
            </div>

            <form onSubmit={handleSubmit} className="space-y-6">
                {/* Basic Information */}
                <div className="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <h3 className="mb-4 text-lg font-semibold text-gray-900">Basic Information</h3>

                    <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <label className="mb-2 block text-sm font-medium text-gray-700">Code *</label>
                            <input
                                type="text"
                                value={data.code}
                                onChange={(e) => setData('code', e.target.value)}
                                className="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                                required
                            />
                            {errors.code && <p className="mt-1 text-sm text-red-600">{errors.code}</p>}
                        </div>

                        <div>
                            <label className="mb-2 block text-sm font-medium text-gray-700">Name *</label>
                            <input
                                type="text"
                                value={data.name}
                                onChange={(e) => setData('name', e.target.value)}
                                className="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                                required
                            />
                            {errors.name && <p className="mt-1 text-sm text-red-600">{errors.name}</p>}
                        </div>

                        <div className="md:col-span-2">
                            <label className="mb-2 block text-sm font-medium text-gray-700">Description</label>
                            <textarea
                                value={data.description}
                                onChange={(e) => setData('description', e.target.value)}
                                rows={3}
                                className="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                            />
                            {errors.description && <p className="mt-1 text-sm text-red-600">{errors.description}</p>}
                        </div>
                    </div>
                </div>

                {/* Visual Settings */}
                <div className="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <h3 className="mb-4 text-lg font-semibold text-gray-900">Visual Settings</h3>

                    <div className="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <div>
                            <label className="mb-2 block text-sm font-medium text-gray-700">Color *</label>
                            <div className="flex items-center gap-2">
                                <input
                                    type="color"
                                    value={data.color}
                                    onChange={(e) => setData('color', e.target.value)}
                                    className="h-10 w-20 cursor-pointer rounded border border-gray-300"
                                />
                                <input
                                    type="text"
                                    value={data.color}
                                    onChange={(e) => setData('color', e.target.value)}
                                    className="flex-1 rounded-lg border border-gray-300 px-4 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                                />
                            </div>
                            {errors.color && <p className="mt-1 text-sm text-red-600">{errors.color}</p>}
                        </div>

                        <div>
                            <label className="mb-2 block text-sm font-medium text-gray-700">Icon</label>
                            <input
                                type="text"
                                value={data.icon}
                                onChange={(e) => setData('icon', e.target.value)}
                                className="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                            />
                            {errors.icon && <p className="mt-1 text-sm text-red-600">{errors.icon}</p>}
                            <p className="mt-1 text-xs text-gray-500">Lucide icon name (optional)</p>
                        </div>

                        <div>
                            <label className="mb-2 block text-sm font-medium text-gray-700">Sort Order</label>
                            <input
                                type="number"
                                value={data.sort_order}
                                onChange={(e) => setData('sort_order', parseInt(e.target.value) || 0)}
                                className="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                                min="0"
                            />
                            {errors.sort_order && <p className="mt-1 text-sm text-red-600">{errors.sort_order}</p>}
                        </div>
                    </div>

                    <div className="mt-4">
                        <label className="flex items-center gap-2">
                            <input
                                type="checkbox"
                                checked={data.is_active}
                                onChange={(e) => setData('is_active', e.target.checked)}
                                className="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-2 focus:ring-blue-500"
                            />
                            <span className="text-sm font-medium text-gray-700">Active</span>
                        </label>
                    </div>
                </div>

                {/* Preview */}
                <div className="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <h3 className="mb-4 text-lg font-semibold text-gray-900">Preview</h3>
                    <div className="flex items-center gap-4 rounded-lg border border-gray-200 p-4">
                        <div className="flex h-12 w-12 items-center justify-center rounded-lg" style={{ backgroundColor: data.color + '20' }}>
                            <div className="h-6 w-6 rounded" style={{ backgroundColor: data.color }}></div>
                        </div>
                        <div>
                            <h4 className="font-semibold text-gray-900">{data.name}</h4>
                            <p className="text-sm text-gray-600">{data.code}</p>
                        </div>
                    </div>
                </div>

                {/* Actions */}
                <div className="flex items-center justify-end gap-4">
                    <Link href="/admin/themes" className="rounded-lg border border-gray-300 bg-white px-6 py-2 text-gray-700 hover:bg-gray-50">
                        Cancel
                    </Link>
                    <button
                        type="submit"
                        disabled={processing}
                        className="flex items-center gap-2 rounded-lg bg-blue-600 px-6 py-2 text-white hover:bg-blue-700 disabled:opacity-50"
                    >
                        <Save className="h-4 w-4" />
                        {processing ? 'Updating...' : 'Update Theme'}
                    </button>
                </div>
            </form>
        </AdminLayout>
    );
}
