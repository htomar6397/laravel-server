import { Head, Link, router } from '@inertiajs/react';
import { Search, Plus, Edit, Trash2, MapPin, Filter } from 'lucide-react';
import { useState } from 'react';
import AdminLayout from '@/Layouts/AdminLayout';

interface OrganizationalUnit {
    id: number;
    code: string;
    name: string;
    level: string;
    parent?: { id: number; name: string; level: string };
    latitude?: number;
    longitude?: number;
    population?: number;
    description?: string;
    is_active: boolean;
    created_at: string;
}

interface OrganizationalUnitsIndexProps {
    units: {
        data: OrganizationalUnit[];
        links?: Array<{ url: string | null; label: string; active: boolean }>;
        meta?: { current_page: number; last_page: number; per_page: number; total: number; from: number; to: number };
    };
    filters: { search?: string; level?: string; parent?: string; status?: string };
    parents: Array<{ id: number; name: string; level: string }>;
}

export default function Index({ units, filters, parents }: OrganizationalUnitsIndexProps) {
    const [search, setSearch] = useState(filters.search || '');
    const [levelFilter, setLevelFilter] = useState(filters.level || '');
    const [statusFilter, setStatusFilter] = useState(filters.status || '');

    const handleSearch = () => {
        router.get('/admin/organizational-units', { search, level: levelFilter, status: statusFilter }, { preserveState: true });
    };

    const deleteUnit = (unitId: number) => {
        if (confirm('Are you sure you want to delete this organizational unit?')) {
            router.delete(`/admin/organizational-units/${unitId}`);
        }
    };

    const getLevelBadgeColor = (level: string) => {
        const colors: Record<string, string> = {
            municipality: 'bg-purple-100 text-purple-800',
            ward: 'bg-blue-100 text-blue-800',
            mtaa: 'bg-green-100 text-green-800',
            village: 'bg-yellow-100 text-yellow-800',
        };
        return colors[level] || 'bg-gray-100 text-gray-800';
    };

    return (
        <AdminLayout header="Organizational Units">
            <Head title="Organizational Units" />

            {/* Filters & Actions */}
            <div className="mb-6 rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <div className="mb-4 flex items-center justify-between">
                    <h2 className="text-lg font-semibold text-gray-900">Manage Organizational Units</h2>
                    <Link
                        href="/admin/organizational-units/create"
                        className="flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-white hover:bg-blue-700"
                    >
                        <Plus className="h-4 w-4" />
                        Add Unit
                    </Link>
                </div>

                <div className="grid grid-cols-1 gap-4 md:grid-cols-4">
                    <div className="md:col-span-2">
                        <div className="relative">
                            <Search className="absolute top-1/2 left-3 h-5 w-5 -translate-y-1/2 transform text-gray-400" />
                            <input
                                type="text"
                                placeholder="Search units..."
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                                onKeyPress={(e) => e.key === 'Enter' && handleSearch()}
                                className="w-full rounded-lg border border-gray-300 py-2 pr-4 pl-10 focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                            />
                        </div>
                    </div>

                    <select
                        value={levelFilter}
                        onChange={(e) => setLevelFilter(e.target.value)}
                        className="rounded-lg border border-gray-300 px-4 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                    >
                        <option value="">All Levels</option>
                        <option value="municipality">Municipality</option>
                        <option value="ward">Ward</option>
                        <option value="mtaa">Mtaa</option>
                        <option value="village">Village</option>
                    </select>

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

                <div className="mt-4">
                    <button onClick={handleSearch} className="flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">
                        <Filter className="h-4 w-4" />
                        Apply Filters
                    </button>
                </div>
            </div>

            {/* Units Table */}
            <div className="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                <div className="overflow-x-auto">
                    <table className="min-w-full divide-y divide-gray-200">
                        <thead className="bg-gray-50">
                            <tr>
                                <th className="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase">Code</th>
                                <th className="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase">Name</th>
                                <th className="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase">Level</th>
                                <th className="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase">Parent</th>
                                <th className="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase">Population</th>
                                <th className="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase">Status</th>
                                <th className="px-6 py-3 text-right text-xs font-medium tracking-wider text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-gray-200 bg-white">
                            {units.data.map((unit) => (
                                <tr key={unit.id} className="hover:bg-gray-50">
                                    <td className="px-6 py-4 text-sm font-medium whitespace-nowrap text-gray-900">{unit.code}</td>
                                    <td className="px-6 py-4">
                                        <div className="flex items-center gap-2">
                                            {unit.latitude && unit.longitude && <MapPin className="h-4 w-4 text-gray-400" />}
                                            <span className="text-sm font-medium text-gray-900">{unit.name}</span>
                                        </div>
                                    </td>
                                    <td className="px-6 py-4 whitespace-nowrap">
                                        <span
                                            className={`inline-flex rounded-full px-2 py-1 text-xs font-semibold ${getLevelBadgeColor(unit.level)}`}
                                        >
                                            {unit.level}
                                        </span>
                                    </td>
                                    <td className="px-6 py-4 text-sm whitespace-nowrap text-gray-500">{unit.parent?.name || '-'}</td>
                                    <td className="px-6 py-4 text-sm whitespace-nowrap text-gray-500">
                                        {unit.population ? unit.population.toLocaleString() : '-'}
                                    </td>
                                    <td className="px-6 py-4 whitespace-nowrap">
                                        <span
                                            className={`inline-flex rounded-full px-2 py-1 text-xs font-semibold ${
                                                unit.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                                            }`}
                                        >
                                            {unit.is_active ? 'Active' : 'Inactive'}
                                        </span>
                                    </td>
                                    <td className="px-6 py-4 text-right text-sm font-medium whitespace-nowrap">
                                        <div className="flex items-center justify-end gap-2">
                                            <Link href={`/admin/organizational-units/${unit.id}/edit`} className="text-blue-600 hover:text-blue-900">
                                                <Edit className="h-4 w-4" />
                                            </Link>
                                            <button onClick={() => deleteUnit(unit.id)} className="text-red-600 hover:text-red-900">
                                                <Trash2 className="h-4 w-4" />
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>

                {/* Pagination */}
                {units.data.length > 0 && units?.meta && units?.links && (
                    <div className="flex items-center justify-between border-t border-gray-200 bg-white px-6 py-3">
                        <div className="text-sm text-gray-700">
                            Showing <span className="font-medium">{units?.meta?.from || 0}</span> to{' '}
                            <span className="font-medium">{units?.meta?.to || 0}</span> of{' '}
                            <span className="font-medium">{units?.meta?.total || 0}</span> results
                        </div>
                        <div className="flex gap-2">
                            {units.links.map((link, index) => (
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
            </div>
        </AdminLayout>
    );
}
