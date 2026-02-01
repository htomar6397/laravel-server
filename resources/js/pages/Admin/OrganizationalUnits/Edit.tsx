import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowLeft, Save } from 'lucide-react';
import AdminLayout from '@/Layouts/AdminLayout';

interface Parent {
    id: number;
    name: string;
    level: string;
}

interface OrganizationalUnit {
    id: number;
    code: string;
    name: string;
    level: string;
    parent_id?: number;
    latitude?: number;
    longitude?: number;
    population?: number;
    description?: string;
    is_active: boolean;
}

interface EditProps {
    unit: OrganizationalUnit;
    parents: Parent[];
}

export default function Edit({ unit, parents }: EditProps) {
    const { data, setData, put, processing, errors } = useForm({
        code: unit.code || '',
        name: unit.name || '',
        level: unit.level || 'ward',
        parent_id: unit.parent_id?.toString() || '',
        latitude: unit.latitude?.toString() || '',
        longitude: unit.longitude?.toString() || '',
        population: unit.population?.toString() || '',
        description: unit.description || '',
        is_active: unit.is_active ?? true,
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        put(`/admin/organizational-units/${unit.id}`);
    };

    return (
        <AdminLayout header="Edit Organizational Unit">
            <Head title="Edit Organizational Unit" />

            <div className="mb-6">
                <Link href="/admin/organizational-units" className="inline-flex items-center gap-2 text-gray-600 hover:text-gray-900">
                    <ArrowLeft className="h-4 w-4" />
                    Back to Organizational Units
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

                        <div>
                            <label className="mb-2 block text-sm font-medium text-gray-700">Level *</label>
                            <select
                                value={data.level}
                                onChange={(e) => setData('level', e.target.value)}
                                className="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                                required
                            >
                                <option value="municipality">Municipality</option>
                                <option value="ward">Ward</option>
                                <option value="mtaa">Mtaa</option>
                                <option value="village">Village</option>
                            </select>
                            {errors.level && <p className="mt-1 text-sm text-red-600">{errors.level}</p>}
                        </div>

                        <div>
                            <label className="mb-2 block text-sm font-medium text-gray-700">Parent Unit</label>
                            <select
                                value={data.parent_id}
                                onChange={(e) => setData('parent_id', e.target.value)}
                                className="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                            >
                                <option value="">No Parent</option>
                                {parents.map((parent) => (
                                    <option key={parent.id} value={parent.id}>
                                        {parent.name} ({parent.level})
                                    </option>
                                ))}
                            </select>
                            {errors.parent_id && <p className="mt-1 text-sm text-red-600">{errors.parent_id}</p>}
                        </div>
                    </div>
                </div>

                {/* Geographic Information */}
                <div className="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <h3 className="mb-4 text-lg font-semibold text-gray-900">Geographic Information</h3>

                    <div className="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <div>
                            <label className="mb-2 block text-sm font-medium text-gray-700">Latitude</label>
                            <input
                                type="number"
                                step="any"
                                value={data.latitude}
                                onChange={(e) => setData('latitude', e.target.value)}
                                className="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                                placeholder="-6.792354"
                            />
                            {errors.latitude && <p className="mt-1 text-sm text-red-600">{errors.latitude}</p>}
                        </div>

                        <div>
                            <label className="mb-2 block text-sm font-medium text-gray-700">Longitude</label>
                            <input
                                type="number"
                                step="any"
                                value={data.longitude}
                                onChange={(e) => setData('longitude', e.target.value)}
                                className="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                                placeholder="39.208328"
                            />
                            {errors.longitude && <p className="mt-1 text-sm text-red-600">{errors.longitude}</p>}
                        </div>

                        <div>
                            <label className="mb-2 block text-sm font-medium text-gray-700">Population</label>
                            <input
                                type="number"
                                value={data.population}
                                onChange={(e) => setData('population', e.target.value)}
                                className="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                                placeholder="50000"
                            />
                            {errors.population && <p className="mt-1 text-sm text-red-600">{errors.population}</p>}
                        </div>
                    </div>
                </div>

                {/* Additional Information */}
                <div className="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <h3 className="mb-4 text-lg font-semibold text-gray-900">Additional Information</h3>

                    <div>
                        <label className="mb-2 block text-sm font-medium text-gray-700">Description</label>
                        <textarea
                            value={data.description}
                            onChange={(e) => setData('description', e.target.value)}
                            rows={4}
                            className="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                            placeholder="Enter description..."
                        />
                        {errors.description && <p className="mt-1 text-sm text-red-600">{errors.description}</p>}
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

                {/* Actions */}
                <div className="flex items-center justify-end gap-4">
                    <Link
                        href="/admin/organizational-units"
                        className="rounded-lg border border-gray-300 bg-white px-6 py-2 text-gray-700 hover:bg-gray-50"
                    >
                        Cancel
                    </Link>
                    <button
                        type="submit"
                        disabled={processing}
                        className="flex items-center gap-2 rounded-lg bg-blue-600 px-6 py-2 text-white hover:bg-blue-700 disabled:opacity-50"
                    >
                        <Save className="h-4 w-4" />
                        {processing ? 'Updating...' : 'Update Unit'}
                    </button>
                </div>
            </form>
        </AdminLayout>
    );
}
