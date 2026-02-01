import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowLeft, Save } from 'lucide-react';
import AdminLayout from '@/Layouts/AdminLayout';

interface Theme {
    id: number;
    name: string;
}

interface CreateProps {
    themes: Theme[];
}

export default function Create({ themes }: CreateProps) {
    const { data, setData, post, processing, errors } = useForm({
        code: '',
        name: '',
        description: '',
        theme_id: '',
        type: 'output',
        unit: 'number',
        unit_label: '',
        baseline_value: '',
        target_value: '',
        direction: 'increasing',
        frequency: 'monthly',
        data_source: '',
        calculation_method: '',
        is_active: true,
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post('/admin/indicators');
    };

    return (
        <AdminLayout header="Create Indicator">
            <Head title="Create Indicator" />

            <div className="mb-6">
                <Link href="/admin/indicators" className="inline-flex items-center gap-2 text-gray-600 hover:text-gray-900">
                    <ArrowLeft className="h-4 w-4" />
                    Back to Indicators
                </Link>
            </div>

            <form onSubmit={handleSubmit} className="space-y-6">
                {/* Basic Information */}
                <div className="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <h3 className="mb-4 text-lg font-semibold text-gray-900">Basic Information</h3>
                    <div className="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div>
                            <label className="block text-sm font-medium text-gray-700">
                                Indicator Code <span className="text-red-500">*</span>
                            </label>
                            <input
                                type="text"
                                value={data.code}
                                onChange={(e) => setData('code', e.target.value)}
                                className="mt-1 block w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                                placeholder="e.g., IND-001"
                            />
                            {errors.code && <p className="mt-1 text-sm text-red-600">{errors.code}</p>}
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-gray-700">Theme</label>
                            <select
                                value={data.theme_id}
                                onChange={(e) => setData('theme_id', e.target.value)}
                                className="mt-1 block w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                            >
                                <option value="">Select Theme</option>
                                {themes.map((theme) => (
                                    <option key={theme.id} value={theme.id}>
                                        {theme.name}
                                    </option>
                                ))}
                            </select>
                            {errors.theme_id && <p className="mt-1 text-sm text-red-600">{errors.theme_id}</p>}
                        </div>

                        <div className="md:col-span-2">
                            <label className="block text-sm font-medium text-gray-700">
                                Indicator Name <span className="text-red-500">*</span>
                            </label>
                            <input
                                type="text"
                                value={data.name}
                                onChange={(e) => setData('name', e.target.value)}
                                className="mt-1 block w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                                placeholder="Enter indicator name"
                            />
                            {errors.name && <p className="mt-1 text-sm text-red-600">{errors.name}</p>}
                        </div>

                        <div className="md:col-span-2">
                            <label className="block text-sm font-medium text-gray-700">Description</label>
                            <textarea
                                value={data.description}
                                onChange={(e) => setData('description', e.target.value)}
                                rows={3}
                                className="mt-1 block w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                                placeholder="Describe this indicator..."
                            />
                            {errors.description && <p className="mt-1 text-sm text-red-600">{errors.description}</p>}
                        </div>
                    </div>
                </div>

                {/* Measurement Configuration */}
                <div className="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <h3 className="mb-4 text-lg font-semibold text-gray-900">Measurement Configuration</h3>
                    <div className="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div>
                            <label className="block text-sm font-medium text-gray-700">
                                Type <span className="text-red-500">*</span>
                            </label>
                            <select
                                value={data.type}
                                onChange={(e) => setData('type', e.target.value)}
                                className="mt-1 block w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                            >
                                <option value="output">Output</option>
                                <option value="outcome">Outcome</option>
                                <option value="impact">Impact</option>
                                <option value="process">Process</option>
                            </select>
                            {errors.type && <p className="mt-1 text-sm text-red-600">{errors.type}</p>}
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-gray-700">
                                Unit <span className="text-red-500">*</span>
                            </label>
                            <select
                                value={data.unit}
                                onChange={(e) => setData('unit', e.target.value)}
                                className="mt-1 block w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                            >
                                <option value="number">Number</option>
                                <option value="percentage">Percentage</option>
                                <option value="currency">Currency</option>
                                <option value="text">Text</option>
                            </select>
                            {errors.unit && <p className="mt-1 text-sm text-red-600">{errors.unit}</p>}
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-gray-700">Unit Label</label>
                            <input
                                type="text"
                                value={data.unit_label}
                                onChange={(e) => setData('unit_label', e.target.value)}
                                className="mt-1 block w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                                placeholder="e.g., people, km, households"
                            />
                            {errors.unit_label && <p className="mt-1 text-sm text-red-600">{errors.unit_label}</p>}
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-gray-700">
                                Direction <span className="text-red-500">*</span>
                            </label>
                            <select
                                value={data.direction}
                                onChange={(e) => setData('direction', e.target.value)}
                                className="mt-1 block w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                            >
                                <option value="increasing">Increasing is Better</option>
                                <option value="decreasing">Decreasing is Better</option>
                                <option value="stable">Stable is Better</option>
                            </select>
                            {errors.direction && <p className="mt-1 text-sm text-red-600">{errors.direction}</p>}
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-gray-700">Baseline Value</label>
                            <input
                                type="number"
                                step="0.01"
                                value={data.baseline_value}
                                onChange={(e) => setData('baseline_value', e.target.value)}
                                className="mt-1 block w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                            />
                            {errors.baseline_value && <p className="mt-1 text-sm text-red-600">{errors.baseline_value}</p>}
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-gray-700">Target Value</label>
                            <input
                                type="number"
                                step="0.01"
                                value={data.target_value}
                                onChange={(e) => setData('target_value', e.target.value)}
                                className="mt-1 block w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                            />
                            {errors.target_value && <p className="mt-1 text-sm text-red-600">{errors.target_value}</p>}
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-gray-700">
                                Frequency <span className="text-red-500">*</span>
                            </label>
                            <select
                                value={data.frequency}
                                onChange={(e) => setData('frequency', e.target.value)}
                                className="mt-1 block w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                            >
                                <option value="daily">Daily</option>
                                <option value="weekly">Weekly</option>
                                <option value="monthly">Monthly</option>
                                <option value="quarterly">Quarterly</option>
                                <option value="annually">Annually</option>
                            </select>
                            {errors.frequency && <p className="mt-1 text-sm text-red-600">{errors.frequency}</p>}
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-gray-700">Data Source</label>
                            <input
                                type="text"
                                value={data.data_source}
                                onChange={(e) => setData('data_source', e.target.value)}
                                className="mt-1 block w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                                placeholder="e.g., Project Reports, Surveys"
                            />
                            {errors.data_source && <p className="mt-1 text-sm text-red-600">{errors.data_source}</p>}
                        </div>

                        <div className="md:col-span-2">
                            <label className="block text-sm font-medium text-gray-700">Calculation Method</label>
                            <textarea
                                value={data.calculation_method}
                                onChange={(e) => setData('calculation_method', e.target.value)}
                                rows={2}
                                className="mt-1 block w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                                placeholder="Describe how this indicator is calculated..."
                            />
                            {errors.calculation_method && <p className="mt-1 text-sm text-red-600">{errors.calculation_method}</p>}
                        </div>

                        <div className="md:col-span-2">
                            <label className="flex items-center gap-2">
                                <input
                                    type="checkbox"
                                    checked={data.is_active}
                                    onChange={(e) => setData('is_active', e.target.checked)}
                                    className="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                />
                                <span className="text-sm font-medium text-gray-700">Active</span>
                            </label>
                        </div>
                    </div>
                </div>

                {/* Actions */}
                <div className="flex justify-end gap-4">
                    <Link href="/admin/indicators" className="rounded-lg border border-gray-300 px-6 py-2 text-gray-700 hover:bg-gray-50">
                        Cancel
                    </Link>
                    <button
                        type="submit"
                        disabled={processing}
                        className="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-6 py-2 text-white hover:bg-blue-700 disabled:opacity-50"
                    >
                        <Save className="h-5 w-5" />
                        {processing ? 'Saving...' : 'Save Indicator'}
                    </button>
                </div>
            </form>
        </AdminLayout>
    );
}
