import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowLeft, Check, X, FileText, Calendar, User, TrendingUp, Paperclip, Save } from 'lucide-react';
import AdminLayout from '@/Layouts/AdminLayout';

interface Project {
    id: number;
    name: string;
    code: string;
}

interface Indicator {
    id: number;
    name: string;
    code: string;
}

interface User {
    id: number;
    full_name: string;
}

interface FileAttachment {
    id: number;
    file_name: string;
    file_path: string;
    file_size: number;
}

interface DataEntry {
    id: number;
    project: Project;
    indicator: Indicator;
    value: number;
    unit: string;
    data_date: string;
    frequency: string;
    verification_status: string;
    verification_notes: string | null;
    notes: string | null;
    entered_by: User;
    verified_by?: User;
    verified_at?: string;
    created_at: string;
    file_attachments: FileAttachment[];
}

interface ShowProps {
    dataEntry: DataEntry;
}

export default function Show({ dataEntry }: ShowProps) {
    const { data, setData, post, processing } = useForm({
        verification_notes: '',
    });

    const handleVerify = () => {
        post(`/admin/data-entries/${dataEntry.id}/verify`);
    };

    const handleReject = () => {
        if (!data.verification_notes) {
            alert('Please provide rejection notes');
            return;
        }
        post(`/admin/data-entries/${dataEntry.id}/reject`);
    };

    const getStatusColor = (status: string) => {
        const colors = {
            pending: 'bg-yellow-100 text-yellow-800',
            verified: 'bg-green-100 text-green-800',
            rejected: 'bg-red-100 text-red-800',
        };
        return colors[status as keyof typeof colors] || 'bg-gray-100 text-gray-800';
    };

    const formatFileSize = (bytes: number) => {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return Math.round((bytes / Math.pow(k, i)) * 100) / 100 + ' ' + sizes[i];
    };

    return (
        <AdminLayout header="Data Entry Details">
            <Head title="Data Entry Details" />

            <div className="mb-6 flex items-center justify-between">
                <Link href="/admin/data-entries" className="inline-flex items-center gap-2 text-gray-600 hover:text-gray-900">
                    <ArrowLeft className="h-4 w-4" />
                    Back to Data Entries
                </Link>

                {dataEntry.verification_status === 'pending' && (
                    <div className="flex gap-2">
                        <button
                            onClick={handleReject}
                            disabled={processing}
                            className="inline-flex items-center gap-2 rounded-lg border border-red-300 px-4 py-2 text-red-700 hover:bg-red-50 disabled:opacity-50"
                        >
                            <X className="h-5 w-5" />
                            Reject
                        </button>
                        <button
                            onClick={handleVerify}
                            disabled={processing}
                            className="inline-flex items-center gap-2 rounded-lg bg-green-600 px-4 py-2 text-white hover:bg-green-700 disabled:opacity-50"
                        >
                            <Check className="h-5 w-5" />
                            Verify
                        </button>
                    </div>
                )}
            </div>

            <div className="grid grid-cols-1 gap-6 lg:grid-cols-3">
                {/* Main Content */}
                <div className="space-y-6 lg:col-span-2">
                    {/* Data Entry Details */}
                    <div className="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                        <h3 className="mb-4 text-lg font-semibold text-gray-900">Entry Information</h3>

                        <div className="space-y-4">
                            <div className="grid grid-cols-2 gap-4">
                                <div>
                                    <p className="text-sm text-gray-600">Project</p>
                                    <p className="font-medium text-gray-900">{dataEntry.project.name}</p>
                                    <p className="text-sm text-gray-500">{dataEntry.project.code}</p>
                                </div>
                                <div>
                                    <p className="text-sm text-gray-600">Indicator</p>
                                    <p className="font-medium text-gray-900">{dataEntry.indicator.name}</p>
                                    <p className="text-sm text-gray-500">{dataEntry.indicator.code}</p>
                                </div>
                            </div>

                            <div className="grid grid-cols-2 gap-4">
                                <div>
                                    <p className="text-sm text-gray-600">Value</p>
                                    <p className="text-2xl font-bold text-blue-600">
                                        {dataEntry.value} {dataEntry.unit}
                                    </p>
                                </div>
                                <div>
                                    <p className="text-sm text-gray-600">Frequency</p>
                                    <p className="font-medium text-gray-900 capitalize">{dataEntry.frequency}</p>
                                </div>
                            </div>

                            <div className="grid grid-cols-2 gap-4">
                                <div className="flex items-center gap-2">
                                    <Calendar className="h-4 w-4 text-gray-400" />
                                    <div>
                                        <p className="text-sm text-gray-600">Data Date</p>
                                        <p className="text-gray-900">{dataEntry.data_date}</p>
                                    </div>
                                </div>
                                <div className="flex items-center gap-2">
                                    <User className="h-4 w-4 text-gray-400" />
                                    <div>
                                        <p className="text-sm text-gray-600">Entered By</p>
                                        <p className="text-gray-900">{dataEntry.entered_by.full_name}</p>
                                    </div>
                                </div>
                            </div>

                            {dataEntry.notes && (
                                <div>
                                    <p className="text-sm text-gray-600">Notes</p>
                                    <p className="text-gray-900">{dataEntry.notes}</p>
                                </div>
                            )}
                        </div>
                    </div>

                    {/* Attachments */}
                    {dataEntry.file_attachments && dataEntry.file_attachments.length > 0 && (
                        <div className="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                            <h3 className="mb-4 flex items-center gap-2 text-lg font-semibold text-gray-900">
                                <Paperclip className="h-5 w-5" />
                                Attachments ({dataEntry.file_attachments.length})
                            </h3>

                            <div className="space-y-2">
                                {dataEntry.file_attachments.map((file) => (
                                    <div key={file.id} className="flex items-center justify-between rounded-lg border border-gray-200 p-3">
                                        <div className="flex items-center gap-3">
                                            <FileText className="h-5 w-5 text-gray-400" />
                                            <div>
                                                <p className="font-medium text-gray-900">{file.file_name}</p>
                                                <p className="text-sm text-gray-500">{formatFileSize(file.file_size)}</p>
                                            </div>
                                        </div>
                                        <a
                                            href={`/storage/${file.file_path}`}
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            className="text-blue-600 hover:text-blue-700"
                                        >
                                            Download
                                        </a>
                                    </div>
                                ))}
                            </div>
                        </div>
                    )}

                    {/* Verification Notes */}
                    {dataEntry.verification_status === 'pending' && (
                        <div className="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                            <h3 className="mb-4 text-lg font-semibold text-gray-900">Verification Notes</h3>
                            <textarea
                                value={data.verification_notes}
                                onChange={(e) => setData('verification_notes', e.target.value)}
                                rows={4}
                                placeholder="Add notes about this data entry (required for rejection)..."
                                className="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                            />
                        </div>
                    )}

                    {dataEntry.verification_notes && (
                        <div className="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                            <h3 className="mb-4 text-lg font-semibold text-gray-900">Verification Notes</h3>
                            <p className="text-gray-900">{dataEntry.verification_notes}</p>
                            {dataEntry.verified_by && (
                                <div className="mt-4 text-sm text-gray-600">
                                    <p>By: {dataEntry.verified_by.full_name}</p>
                                    <p>At: {dataEntry.verified_at}</p>
                                </div>
                            )}
                        </div>
                    )}
                </div>

                {/* Sidebar */}
                <div className="space-y-6">
                    {/* Status Card */}
                    <div className="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                        <h3 className="mb-4 text-lg font-semibold text-gray-900">Status</h3>
                        <div className="space-y-4">
                            <div>
                                <p className="text-sm text-gray-600">Verification Status</p>
                                <span
                                    className={`mt-1 inline-flex rounded-full px-3 py-1 text-sm font-semibold capitalize ${getStatusColor(dataEntry.verification_status)}`}
                                >
                                    {dataEntry.verification_status}
                                </span>
                            </div>

                            {dataEntry.verified_by && (
                                <>
                                    <div>
                                        <p className="text-sm text-gray-600">Verified By</p>
                                        <p className="text-gray-900">{dataEntry.verified_by.full_name}</p>
                                    </div>
                                    <div>
                                        <p className="text-sm text-gray-600">Verified At</p>
                                        <p className="text-gray-900">{dataEntry.verified_at}</p>
                                    </div>
                                </>
                            )}

                            <div>
                                <p className="text-sm text-gray-600">Created At</p>
                                <p className="text-gray-900">{dataEntry.created_at}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}
