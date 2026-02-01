import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowLeft, Save } from 'lucide-react';
import AdminLayout from '@/Layouts/AdminLayout';

interface Theme {
    id: number;
    name: string;
}

interface OrganizationalUnit {
    id: number;
    name: string;
}

interface User {
    id: number;
    full_name: string;
}

interface CreateProps {
    themes: Theme[];
    organizationalUnits: OrganizationalUnit[];
    users: User[];
}

export default function Create({ themes, organizationalUnits }: CreateProps) {
    const { data, setData, post, processing, errors } = useForm({
        code: '',
        name: '',
        description: '',
        theme_id: '',
        org_unit_id: '',
        sector: '',
        donor: '',
        budget: '',
        currency: 'TZS',
        start_date: '',
        end_date: '',
        status: 'PLANNING',
        completion_percentage: 0,
        latitude: '',
        longitude: '',
        location_description: '',
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post('/admin/projects');
    };

    const statuses = ['PLANNING', 'ACTIVE', 'SUSPENDED', 'COMPLETED', 'CANCELLED'];

    return (
        <AdminLayout header="Create New Project">
            <Head title="Create Project" />

            <div className="mb-6">
                <Link href="/admin/projects" className="inline-flex items-center gap-2 text-gray-600 hover:text-gray-900">
                    <ArrowLeft className="h-4 w-4" />
                    Back to Projects
                </Link>
            </div>

            <form onSubmit={handleSubmit} className="space-y-6">
                {/* Basic Information */}
                <div className="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <h3 className="mb-4 text-lg font-semibold text-gray-900">Basic Information</h3>

                    <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <label className="mb-2 block text-sm font-medium text-gray-700">Project Code *</label>
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
                            <label className="mb-2 block text-sm font-medium text-gray-700">Project Name *</label>
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

                        <div>
                            <label className="mb-2 block text-sm font-medium text-gray-700">Theme</label>
                            <select
                                value={data.theme_id}
                                onChange={(e) => setData('theme_id', e.target.value)}
                                className="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                            >
                                <option value="">Select theme...</option>
                                {themes.map((theme) => (
                                    <option key={theme.id} value={theme.id}>
                                        {theme.name}
                                    </option>
                                ))}
                            </select>
                            {errors.theme_id && <p className="mt-1 text-sm text-red-600">{errors.theme_id}</p>}
                        </div>

                        <div>
                            <label className="mb-2 block text-sm font-medium text-gray-700">Organizational Unit</label>
                            <select
                                value={data.org_unit_id}
                                onChange={(e) => setData('org_unit_id', e.target.value)}
                                className="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                            >
                                <option value="">Select unit...</option>
                                {organizationalUnits.map((unit) => (
                                    <option key={unit.id} value={unit.id}>
                                        {unit.name}
                                    </option>
                                ))}
                            </select>
                            {errors.org_unit_id && <p className="mt-1 text-sm text-red-600">{errors.org_unit_id}</p>}
                        </div>

                        <div>
                            <label className="mb-2 block text-sm font-medium text-gray-700">Sector</label>
                            <input
                                type="text"
                                value={data.sector}
                                onChange={(e) => setData('sector', e.target.value)}
                                className="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                            />
                            {errors.sector && <p className="mt-1 text-sm text-red-600">{errors.sector}</p>}
                        </div>

                        <div>
                            <label className="mb-2 block text-sm font-medium text-gray-700">Donor</label>
                            <input
                                type="text"
                                value={data.donor}
                                onChange={(e) => setData('donor', e.target.value)}
                                className="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                            />
                            {errors.donor && <p className="mt-1 text-sm text-red-600">{errors.donor}</p>}
                        </div>
                    </div>
                </div>

                {/* Budget & Timeline */}
                <div className="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <h3 className="mb-4 text-lg font-semibold text-gray-900">Budget & Timeline</h3>

                    <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <label className="mb-2 block text-sm font-medium text-gray-700">Budget</label>
                            <input
                                type="number"
                                step="0.01"
                                value={data.budget}
                                onChange={(e) => setData('budget', e.target.value)}
                                className="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                            />
                            {errors.budget && <p className="mt-1 text-sm text-red-600">{errors.budget}</p>}
                        </div>

                        <div>
                            <label className="mb-2 block text-sm font-medium text-gray-700">Currency</label>
                            <select
                                value={data.currency}
                                onChange={(e) => setData('currency', e.target.value)}
                                className="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                            >
                                <option value="TZS">TZS</option>
                                <option value="USD">USD</option>
                                <option value="EUR">EUR</option>
                            </select>
                            {errors.currency && <p className="mt-1 text-sm text-red-600">{errors.currency}</p>}
                        </div>

                        <div>
                            <label className="mb-2 block text-sm font-medium text-gray-700">Start Date</label>
                            <input
                                type="date"
                                value={data.start_date}
                                onChange={(e) => setData('start_date', e.target.value)}
                                className="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                            />
                            {errors.start_date && <p className="mt-1 text-sm text-red-600">{errors.start_date}</p>}
                        </div>

                        <div>
                            <label className="mb-2 block text-sm font-medium text-gray-700">End Date</label>
                            <input
                                type="date"
                                value={data.end_date}
                                onChange={(e) => setData('end_date', e.target.value)}
                                className="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                            />
                            {errors.end_date && <p className="mt-1 text-sm text-red-600">{errors.end_date}</p>}
                        </div>

                        <div>
                            <label className="mb-2 block text-sm font-medium text-gray-700">Status *</label>
                            <select
                                value={data.status}
                                onChange={(e) => setData('status', e.target.value)}
                                className="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                                required
                            >
                                {statuses.map((status) => (
                                    <option key={status} value={status}>
                                        {status}
                                    </option>
                                ))}
                            </select>
                            {errors.status && <p className="mt-1 text-sm text-red-600">{errors.status}</p>}
                        </div>

                        <div>
                            <label className="mb-2 block text-sm font-medium text-gray-700">Completion (%)</label>
                            <input
                                type="number"
                                min="0"
                                max="100"
                                value={data.completion_percentage}
                                onChange={(e) => setData('completion_percentage', parseInt(e.target.value))}
                                className="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                            />
                            {errors.completion_percentage && <p className="mt-1 text-sm text-red-600">{errors.completion_percentage}</p>}
                        </div>
                    </div>
                </div>

                {/* Location */}
                <div className="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <h3 className="mb-4 text-lg font-semibold text-gray-900">Location</h3>

                    <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <label className="mb-2 block text-sm font-medium text-gray-700">Latitude</label>
                            <input
                                type="number"
                                step="any"
                                value={data.latitude}
                                onChange={(e) => setData('latitude', e.target.value)}
                                className="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
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
                            />
                            {errors.longitude && <p className="mt-1 text-sm text-red-600">{errors.longitude}</p>}
                        </div>

                        <div className="md:col-span-2">
                            <label className="mb-2 block text-sm font-medium text-gray-700">Location Description</label>
                            <textarea
                                value={data.location_description}
                                onChange={(e) => setData('location_description', e.target.value)}
                                rows={2}
                                className="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                            />
                            {errors.location_description && <p className="mt-1 text-sm text-red-600">{errors.location_description}</p>}
                        </div>
                    </div>
                </div>

                {/* Actions */}
                <div className="flex justify-end gap-4">
                    <Link href="/admin/projects" className="rounded-lg border border-gray-300 px-6 py-2 text-gray-700 hover:bg-gray-50">
                        Cancel
                    </Link>
                    <button
                        type="submit"
                        disabled={processing}
                        className="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-6 py-2 text-white hover:bg-blue-700 disabled:opacity-50"
                    >
                        <Save className="h-4 w-4" />
                        Create Project
                    </button>
                </div>
            </form>
        </AdminLayout>
    );
}
