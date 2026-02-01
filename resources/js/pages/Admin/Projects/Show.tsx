import { Head, Link } from '@inertiajs/react';
import { ArrowLeft, Edit, MapPin, Calendar, DollarSign, TrendingUp, FileText, Image, Database } from 'lucide-react';
import AdminLayout from '@/Layouts/AdminLayout';

interface Theme {
    name: string;
}

interface OrganizationalUnit {
    name: string;
}

interface User {
    full_name: string;
}

interface Indicator {
    name: string;
}

interface DataEntry {
    id: number;
    value: number;
    data_date: string;
    indicator: Indicator;
    entered_by: User;
}

interface Expenditure {
    id: number;
    amount: number;
    description: string;
    expenditure_date: string;
    entered_by: User;
}

interface PhotoCapture {
    id: number;
    title: string;
    thumbnail_path: string;
    captured_at: string;
    captured_by: User;
}

interface Project {
    id: number;
    code: string;
    name: string;
    description: string | null;
    theme?: Theme;
    organizational_unit?: OrganizationalUnit;
    creator?: User;
    sector: string | null;
    donor: string | null;
    budget: number | null;
    currency: string;
    start_date: string | null;
    end_date: string | null;
    status: string;
    completion_percentage: number;
    latitude: number | null;
    longitude: number | null;
    location_description: string | null;
    data_entries: DataEntry[];
    expenditures: Expenditure[];
    photo_captures: PhotoCapture[];
}

interface Stats {
    total_data_entries: number;
    pending_verifications: number;
    total_expenditures: number;
    total_spent: number;
    pending_expenditures: number;
    total_photos: number;
    budget_utilized: number;
}

interface ShowProps {
    project: Project;
    stats: Stats;
}

export default function Show({ project, stats }: ShowProps) {
    const getStatusColor = (status: string) => {
        const colors: Record<string, string> = {
            PLANNING: 'bg-gray-100 text-gray-700',
            ACTIVE: 'bg-green-100 text-green-700',
            SUSPENDED: 'bg-yellow-100 text-yellow-700',
            COMPLETED: 'bg-blue-100 text-blue-700',
            CANCELLED: 'bg-red-100 text-red-700',
        };
        return colors[status] || 'bg-gray-100 text-gray-700';
    };

    return (
        <AdminLayout header={project.name}>
            <Head title={project.name} />

            <div className="mb-6 flex items-center justify-between">
                <Link href="/admin/projects" className="inline-flex items-center gap-2 text-gray-600 hover:text-gray-900">
                    <ArrowLeft className="h-4 w-4" />
                    Back to Projects
                </Link>
                <Link
                    href={`/admin/projects/${project.id}/edit`}
                    className="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-white hover:bg-blue-700"
                >
                    <Edit className="h-4 w-4" />
                    Edit Project
                </Link>
            </div>

            {/* Project Header */}
            <div className="mb-6 rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <div className="mb-4 flex items-start justify-between">
                    <div>
                        <div className="mb-2 flex items-center gap-3">
                            <h1 className="text-2xl font-bold text-gray-900">{project.name}</h1>
                            <span className={`rounded-full px-3 py-1 text-xs font-medium ${getStatusColor(project.status)}`}>{project.status}</span>
                        </div>
                        <p className="text-sm text-gray-600">Code: {project.code}</p>
                    </div>
                </div>

                {project.description && <p className="text-gray-700">{project.description}</p>}

                <div className="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
                    {project.theme && (
                        <div>
                            <p className="text-sm text-gray-600">Theme</p>
                            <p className="font-medium text-gray-900">{project.theme.name}</p>
                        </div>
                    )}
                    {project.organizational_unit && (
                        <div>
                            <p className="text-sm text-gray-600">Organizational Unit</p>
                            <p className="font-medium text-gray-900">{project.organizational_unit.name}</p>
                        </div>
                    )}
                    {project.sector && (
                        <div>
                            <p className="text-sm text-gray-600">Sector</p>
                            <p className="font-medium text-gray-900">{project.sector}</p>
                        </div>
                    )}
                    {project.donor && (
                        <div>
                            <p className="text-sm text-gray-600">Donor</p>
                            <p className="font-medium text-gray-900">{project.donor}</p>
                        </div>
                    )}
                </div>
            </div>

            {/* Statistics */}
            <div className="mb-6 grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
                <div className="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                    <div className="flex items-center gap-3">
                        <div className="rounded-lg bg-blue-50 p-3">
                            <Database className="h-6 w-6 text-blue-600" />
                        </div>
                        <div>
                            <p className="text-sm text-gray-600">Data Entries</p>
                            <p className="text-2xl font-bold text-gray-900">{stats.total_data_entries}</p>
                            <p className="text-xs text-gray-500">{stats.pending_verifications} pending</p>
                        </div>
                    </div>
                </div>

                <div className="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                    <div className="flex items-center gap-3">
                        <div className="rounded-lg bg-green-50 p-3">
                            <DollarSign className="h-6 w-6 text-green-600" />
                        </div>
                        <div>
                            <p className="text-sm text-gray-600">Total Spent</p>
                            <p className="text-2xl font-bold text-gray-900">
                                {stats.total_spent.toLocaleString()} {project.currency}
                            </p>
                            <p className="text-xs text-gray-500">{stats.budget_utilized.toFixed(1)}% utilized</p>
                        </div>
                    </div>
                </div>

                <div className="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                    <div className="flex items-center gap-3">
                        <div className="rounded-lg bg-purple-50 p-3">
                            <FileText className="h-6 w-6 text-purple-600" />
                        </div>
                        <div>
                            <p className="text-sm text-gray-600">Expenditures</p>
                            <p className="text-2xl font-bold text-gray-900">{stats.total_expenditures}</p>
                            <p className="text-xs text-gray-500">{stats.pending_expenditures.toLocaleString()} pending</p>
                        </div>
                    </div>
                </div>

                <div className="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                    <div className="flex items-center gap-3">
                        <div className="rounded-lg bg-orange-50 p-3">
                            <Image className="h-6 w-6 text-orange-600" />
                        </div>
                        <div>
                            <p className="text-sm text-gray-600">Photos</p>
                            <p className="text-2xl font-bold text-gray-900">{stats.total_photos}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div className="grid grid-cols-1 gap-6 lg:grid-cols-2">
                {/* Budget & Timeline */}
                <div className="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <h3 className="mb-4 text-lg font-semibold text-gray-900">Budget & Timeline</h3>
                    <div className="space-y-4">
                        {project.budget && (
                            <div className="flex items-center gap-3">
                                <DollarSign className="h-5 w-5 text-gray-400" />
                                <div>
                                    <p className="text-sm text-gray-600">Budget</p>
                                    <p className="font-medium text-gray-900">
                                        {project.budget.toLocaleString()} {project.currency}
                                    </p>
                                </div>
                            </div>
                        )}
                        {project.start_date && (
                            <div className="flex items-center gap-3">
                                <Calendar className="h-5 w-5 text-gray-400" />
                                <div>
                                    <p className="text-sm text-gray-600">Start Date</p>
                                    <p className="font-medium text-gray-900">{project.start_date}</p>
                                </div>
                            </div>
                        )}
                        {project.end_date && (
                            <div className="flex items-center gap-3">
                                <Calendar className="h-5 w-5 text-gray-400" />
                                <div>
                                    <p className="text-sm text-gray-600">End Date</p>
                                    <p className="font-medium text-gray-900">{project.end_date}</p>
                                </div>
                            </div>
                        )}
                        <div className="flex items-center gap-3">
                            <TrendingUp className="h-5 w-5 text-gray-400" />
                            <div className="flex-1">
                                <p className="text-sm text-gray-600">Completion</p>
                                <div className="mt-2 h-2 w-full rounded-full bg-gray-200">
                                    <div className="h-2 rounded-full bg-blue-600" style={{ width: `${project.completion_percentage}%` }} />
                                </div>
                                <p className="mt-1 text-sm font-medium text-gray-900">{project.completion_percentage}%</p>
                            </div>
                        </div>
                    </div>
                </div>

                {/* Location */}
                <div className="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <h3 className="mb-4 text-lg font-semibold text-gray-900">Location</h3>
                    <div className="space-y-4">
                        {(project.latitude || project.longitude) && (
                            <div className="flex items-start gap-3">
                                <MapPin className="h-5 w-5 text-gray-400" />
                                <div>
                                    <p className="text-sm text-gray-600">Coordinates</p>
                                    <p className="font-medium text-gray-900">
                                        {project.latitude}, {project.longitude}
                                    </p>
                                </div>
                            </div>
                        )}
                        {project.location_description && (
                            <div>
                                <p className="text-sm text-gray-600">Description</p>
                                <p className="mt-1 text-gray-900">{project.location_description}</p>
                            </div>
                        )}
                    </div>
                </div>
            </div>

            {/* Recent Activities */}
            <div className="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-3">
                {/* Recent Data Entries */}
                <div className="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <h3 className="mb-4 text-lg font-semibold text-gray-900">Recent Data Entries</h3>
                    <div className="space-y-3">
                        {project.data_entries.slice(0, 5).map((entry) => (
                            <div key={entry.id} className="border-b border-gray-100 pb-3 last:border-0">
                                <p className="text-sm font-medium text-gray-900">{entry.indicator.name}</p>
                                <p className="text-xs text-gray-600">
                                    Value: {entry.value} • {entry.data_date}
                                </p>
                            </div>
                        ))}
                        {project.data_entries.length === 0 && <p className="text-sm text-gray-500">No data entries yet</p>}
                    </div>
                </div>

                {/* Recent Expenditures */}
                <div className="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <h3 className="mb-4 text-lg font-semibold text-gray-900">Recent Expenditures</h3>
                    <div className="space-y-3">
                        {project.expenditures.slice(0, 5).map((exp) => (
                            <div key={exp.id} className="border-b border-gray-100 pb-3 last:border-0">
                                <p className="text-sm font-medium text-gray-900">{exp.description}</p>
                                <p className="text-xs text-gray-600">
                                    {exp.amount.toLocaleString()} {project.currency} • {exp.expenditure_date}
                                </p>
                            </div>
                        ))}
                        {project.expenditures.length === 0 && <p className="text-sm text-gray-500">No expenditures yet</p>}
                    </div>
                </div>

                {/* Recent Photos */}
                <div className="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <h3 className="mb-4 text-lg font-semibold text-gray-900">Recent Photos</h3>
                    <div className="grid grid-cols-2 gap-2">
                        {project.photo_captures.slice(0, 4).map((photo) => (
                            <div key={photo.id} className="aspect-square overflow-hidden rounded-lg">
                                <img src={`/storage/${photo.thumbnail_path}`} alt={photo.title} className="h-full w-full object-cover" />
                            </div>
                        ))}
                        {project.photo_captures.length === 0 && <p className="col-span-2 text-sm text-gray-500">No photos yet</p>}
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}
