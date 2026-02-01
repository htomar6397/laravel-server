import { Head, Link } from '@inertiajs/react';
import { ArrowLeft, Download, MapPin, Calendar, User, Trash2, ExternalLink } from 'lucide-react';
import AdminLayout from '@/Layouts/AdminLayout';

interface Project {
    id: number;
    name: string;
    code: string;
}

interface User {
    full_name: string;
}

interface PhotoCapture {
    id: number;
    title: string;
    description: string | null;
    file_path: string;
    thumbnail_path: string | null;
    original_filename: string;
    file_size: number;
    latitude: number | null;
    longitude: number | null;
    captured_at: string;
    project?: Project;
    captured_by: User;
}

interface ShowProps {
    photo: PhotoCapture;
    nearbyPhotos: PhotoCapture[];
}

export default function Show({ photo, nearbyPhotos }: ShowProps) {
    const getImageUrl = (path: string) => {
        if (!path) return '';
        return path.startsWith('http') ? path : `/storage/${path}`;
    };

    const formatFileSize = (bytes: number) => {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return Math.round((bytes / Math.pow(k, i)) * 100) / 100 + ' ' + sizes[i];
    };

    return (
        <AdminLayout header={photo.title}>
            <Head title={photo.title} />

            <div className="mb-6 flex items-center justify-between">
                <Link href="/admin/photos" className="inline-flex items-center gap-2 text-gray-600 hover:text-gray-900">
                    <ArrowLeft className="h-4 w-4" />
                    Back to Gallery
                </Link>
                <div className="flex gap-2">
                    <a
                        href={`/admin/photos/${photo.id}/download`}
                        className="inline-flex items-center gap-2 rounded-lg border border-gray-300 px-4 py-2 text-gray-700 hover:bg-gray-50"
                    >
                        <Download className="h-4 w-4" />
                        Download
                    </a>
                    <Link
                        href={`/admin/photos/${photo.id}`}
                        method="delete"
                        as="button"
                        className="inline-flex items-center gap-2 rounded-lg bg-red-600 px-4 py-2 text-white hover:bg-red-700"
                    >
                        <Trash2 className="h-4 w-4" />
                        Delete
                    </Link>
                </div>
            </div>

            <div className="grid grid-cols-1 gap-6 lg:grid-cols-3">
                {/* Photo Display */}
                <div className="lg:col-span-2">
                    <div className="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                        <div className="aspect-video w-full bg-gray-100">
                            <img src={getImageUrl(photo.file_path)} alt={photo.title} className="h-full w-full object-contain" />
                        </div>
                    </div>

                    {/* Nearby Photos */}
                    {nearbyPhotos.length > 0 && (
                        <div className="mt-6">
                            <h3 className="mb-4 text-lg font-semibold text-gray-900">Nearby Photos</h3>
                            <div className="grid grid-cols-2 gap-4 md:grid-cols-3">
                                {nearbyPhotos.map((nearby) => (
                                    <Link
                                        key={nearby.id}
                                        href={`/admin/photos/${nearby.id}`}
                                        className="group overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm transition-all hover:shadow-md"
                                    >
                                        <div className="aspect-square overflow-hidden bg-gray-100">
                                            <img
                                                src={getImageUrl(nearby.thumbnail_path || nearby.file_path)}
                                                alt={nearby.title}
                                                className="h-full w-full object-cover transition-transform duration-300 group-hover:scale-110"
                                            />
                                        </div>
                                        <div className="p-2">
                                            <p className="truncate text-sm font-medium text-gray-900">{nearby.title}</p>
                                        </div>
                                    </Link>
                                ))}
                            </div>
                        </div>
                    )}
                </div>

                {/* Photo Details */}
                <div className="space-y-6">
                    {/* Basic Info */}
                    <div className="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                        <h3 className="mb-4 text-lg font-semibold text-gray-900">Details</h3>

                        <div className="space-y-4">
                            <div>
                                <p className="text-sm text-gray-600">Title</p>
                                <p className="font-medium text-gray-900">{photo.title}</p>
                            </div>

                            {photo.description && (
                                <div>
                                    <p className="text-sm text-gray-600">Description</p>
                                    <p className="text-gray-900">{photo.description}</p>
                                </div>
                            )}

                            {photo.project && (
                                <div>
                                    <p className="text-sm text-gray-600">Project</p>
                                    <Link
                                        href={`/admin/projects/${photo.project.id}`}
                                        className="inline-flex items-center gap-1 font-medium text-blue-600 hover:text-blue-700"
                                    >
                                        {photo.project.name}
                                        <ExternalLink className="h-3 w-3" />
                                    </Link>
                                </div>
                            )}

                            <div className="flex items-center gap-2">
                                <Calendar className="h-4 w-4 text-gray-400" />
                                <div>
                                    <p className="text-sm text-gray-600">Captured</p>
                                    <p className="text-gray-900">{photo.captured_at}</p>
                                </div>
                            </div>

                            <div className="flex items-center gap-2">
                                <User className="h-4 w-4 text-gray-400" />
                                <div>
                                    <p className="text-sm text-gray-600">Captured By</p>
                                    <p className="text-gray-900">{photo.captured_by.full_name}</p>
                                </div>
                            </div>

                            <div>
                                <p className="text-sm text-gray-600">File Name</p>
                                <p className="text-gray-900">{photo.original_filename}</p>
                            </div>

                            <div>
                                <p className="text-sm text-gray-600">File Size</p>
                                <p className="text-gray-900">{formatFileSize(photo.file_size)}</p>
                            </div>
                        </div>
                    </div>

                    {/* Location */}
                    {photo.latitude && photo.longitude && (
                        <div className="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                            <h3 className="mb-4 text-lg font-semibold text-gray-900">Location</h3>

                            <div className="space-y-4">
                                <div className="flex items-start gap-2">
                                    <MapPin className="mt-1 h-4 w-4 text-gray-400" />
                                    <div>
                                        <p className="text-sm text-gray-600">Coordinates</p>
                                        <p className="font-mono text-sm text-gray-900">
                                            {Number(photo.latitude).toFixed(6)}, {Number(photo.longitude).toFixed(6)}
                                        </p>
                                    </div>
                                </div>

                                <a
                                    href={`https://www.google.com/maps?q=${Number(photo.latitude)},${Number(photo.longitude)}`}
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    className="inline-flex items-center gap-2 text-sm text-blue-600 hover:text-blue-700"
                                >
                                    View on Google Maps
                                    <ExternalLink className="h-3 w-3" />
                                </a>

                                {/* Simple map preview */}
                                <div className="aspect-video w-full overflow-hidden rounded-lg border border-gray-200">
                                    <iframe
                                        width="100%"
                                        height="100%"
                                        frameBorder="0"
                                        src={`https://www.google.com/maps/embed/v1/place?key=YOUR_API_KEY&q=${Number(photo.latitude)},${Number(photo.longitude)}&zoom=15`}
                                        allowFullScreen
                                    ></iframe>
                                </div>
                            </div>
                        </div>
                    )}
                </div>
            </div>
        </AdminLayout>
    );
}
