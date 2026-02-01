import { Head, Link, router } from '@inertiajs/react';
import { Search, Filter, Download, Trash2, Grid3x3, Map as MapIcon, Eye } from 'lucide-react';
import { useState } from 'react';
import AdminLayout from '@/Layouts/AdminLayout';

interface Photo {
    id: number;
    title: string;
    description: string;
    file_path: string;
    thumbnail_path: string;
    latitude: number | null;
    longitude: number | null;
    captured_at: string;
    project?: { id: number; name: string };
    captured_by: { full_name: string };
}

interface PhotosIndexProps {
    photos: {
        data: Photo[];
        links: Array<{ url: string | null; label: string; active: boolean }>;
        meta: { current_page: number; last_page: number; per_page: number; total: number; from: number; to: number };
    };
    geotaggedPhotos: Photo[];
    filters: any;
    projects: Array<{ id: number; name: string }>;
    view: 'grid' | 'map';
}

export default function Index({ photos, geotaggedPhotos, filters, projects, view }: PhotosIndexProps) {
    const [search, setSearch] = useState(filters.search || '');
    const [projectFilter, setProjectFilter] = useState(filters.project_id || '');
    const [viewMode, setViewMode] = useState<'grid' | 'map'>(view);

    const handleSearch = () => {
        router.get('/admin/photos', { search, project_id: projectFilter, view: viewMode }, { preserveState: true });
    };

    const deletePhoto = (photoId: number) => {
        if (confirm('Are you sure you want to delete this photo?')) {
            router.delete(`/admin/photos/${photoId}`);
        }
    };

    const getImageUrl = (path: string) => {
        return `/storage/${path}`;
    };

    return (
        <AdminLayout header="Photo Gallery">
            <Head title="Photos" />

            {/* Filters & Actions */}
            <div className="mb-6 rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <div className="grid grid-cols-1 gap-4 md:grid-cols-4">
                    <div className="md:col-span-2">
                        <div className="relative">
                            <Search className="absolute top-1/2 left-3 h-5 w-5 -translate-y-1/2 transform text-gray-400" />
                            <input
                                type="text"
                                placeholder="Search photos..."
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                                onKeyPress={(e) => e.key === 'Enter' && handleSearch()}
                                className="w-full rounded-lg border border-gray-300 py-2 pr-4 pl-10 focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                            />
                        </div>
                    </div>

                    <select
                        value={projectFilter}
                        onChange={(e) => setProjectFilter(e.target.value)}
                        className="rounded-lg border border-gray-300 px-4 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                    >
                        <option value="">All Projects</option>
                        {projects.map((project) => (
                            <option key={project.id} value={project.id}>
                                {project.name}
                            </option>
                        ))}
                    </select>

                    <div className="flex gap-2">
                        <button
                            onClick={() => setViewMode('grid')}
                            className={`flex flex-1 items-center justify-center gap-2 rounded-lg px-4 py-2 transition-colors ${
                                viewMode === 'grid' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
                            }`}
                        >
                            <Grid3x3 className="h-4 w-4" />
                            Grid
                        </button>
                        <button
                            onClick={() => setViewMode('map')}
                            className={`flex flex-1 items-center justify-center gap-2 rounded-lg px-4 py-2 transition-colors ${
                                viewMode === 'map' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
                            }`}
                        >
                            <MapIcon className="h-4 w-4" />
                            Map
                        </button>
                    </div>
                </div>

                <div className="mt-4 flex gap-3">
                    <button
                        onClick={handleSearch}
                        className="flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-white transition-colors hover:bg-blue-700"
                    >
                        <Filter className="h-4 w-4" />
                        Apply Filters
                    </button>
                </div>
            </div>

            {viewMode === 'grid' ? (
                <>
                    {/* Photo Grid */}
                    <div className="mb-6 grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6">
                        {photos?.data?.map((photo) => (
                            <div
                                key={photo.id}
                                className="group relative overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm transition-all hover:shadow-md"
                            >
                                <div className="aspect-square overflow-hidden bg-gray-100">
                                    <img
                                        src={getImageUrl(photo.thumbnail_path || photo.file_path)}
                                        alt={photo.title}
                                        className="h-full w-full object-cover transition-transform duration-300 group-hover:scale-110"
                                    />
                                </div>

                                {/* Overlay on hover */}
                                <div className="absolute inset-0 flex flex-col items-center justify-center gap-2 bg-black/60 p-4 opacity-0 transition-opacity group-hover:opacity-100">
                                    <h4 className="line-clamp-2 text-center text-sm font-medium text-white">{photo.title}</h4>

                                    {photo.latitude && photo.longitude && (
                                        <div className="flex items-center gap-1 text-xs text-white">
                                            <MapIcon className="h-3 w-3" />
                                            <span>Geotagged</span>
                                        </div>
                                    )}

                                    <div className="mt-2 flex gap-2">
                                        <Link
                                            href={`/admin/photos/${photo.id}`}
                                            className="flex items-center gap-1 rounded bg-white px-3 py-1 text-xs font-medium text-gray-900 hover:bg-gray-100"
                                        >
                                            <Eye className="h-3 w-3" />
                                            View
                                        </Link>
                                        <a
                                            href={`/admin/photos/${photo.id}/download`}
                                            className="flex items-center gap-1 rounded bg-blue-600 px-3 py-1 text-xs font-medium text-white hover:bg-blue-700"
                                        >
                                            <Download className="h-3 w-3" />
                                            Download
                                        </a>
                                        <button
                                            onClick={() => deletePhoto(photo.id)}
                                            className="flex items-center gap-1 rounded bg-red-600 px-3 py-1 text-xs font-medium text-white hover:bg-red-700"
                                        >
                                            <Trash2 className="h-3 w-3" />
                                        </button>
                                    </div>
                                </div>

                                <div className="p-3">
                                    <p className="line-clamp-1 text-xs text-gray-600">{photo.project?.name || 'No Project'}</p>
                                    <p className="mt-1 text-xs text-gray-500">{new Date(photo.captured_at).toLocaleDateString()}</p>
                                </div>
                            </div>
                        ))}
                    </div>

                    {/* Pagination */}
                    {photos.links && photos.meta && (
                        <div className="rounded-xl border border-gray-200 bg-white px-6 py-4 shadow-sm">
                            <div className="flex items-center justify-between">
                                <div className="text-sm text-gray-700">
                                    Showing {photos.meta.from || 0} to {photos.meta.to || 0} of {photos.meta.total || 0} photos
                                </div>
                                <div className="flex gap-2">
                                    {photos.links.map((link, index: number) => (
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
                </>
            ) : (
                <div className="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <div className="flex h-[600px] w-full items-center justify-center rounded-lg bg-gray-100">
                        <div className="text-center">
                            <MapIcon className="mx-auto mb-4 h-16 w-16 text-gray-400" />
                            <p className="text-lg font-medium text-gray-600">Map View</p>
                            <p className="mt-2 text-sm text-gray-500">{geotaggedPhotos.length} geotagged photos available</p>
                            <p className="mt-2 text-xs text-gray-400">Interactive map integration requires Leaflet configuration</p>
                        </div>
                    </div>

                    {/* List of geotagged photos */}
                    <div className="mt-6 grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                        {geotaggedPhotos.slice(0, 12).map((photo) => (
                            <div key={photo.id} className="flex items-center gap-3 rounded-lg bg-gray-50 p-3">
                                <img
                                    src={getImageUrl(photo.thumbnail_path || photo.file_path)}
                                    alt={photo.title}
                                    className="h-16 w-16 rounded object-cover"
                                />
                                <div className="min-w-0 flex-1">
                                    <p className="truncate text-sm font-medium text-gray-900">{photo.title}</p>
                                    <p className="text-xs text-gray-500">{photo.project?.name}</p>
                                    <p className="mt-1 text-xs text-gray-400">
                                        {photo.latitude?.toFixed(4)}, {photo.longitude?.toFixed(4)}
                                    </p>
                                </div>
                                <Link href={`/admin/photos/${photo.id}`} className="text-blue-600 hover:text-blue-900">
                                    <Eye className="h-5 w-5" />
                                </Link>
                            </div>
                        ))}
                    </div>
                </div>
            )}
        </AdminLayout>
    );
}
