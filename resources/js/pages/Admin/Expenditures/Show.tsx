import { Head, Link, router } from '@inertiajs/react';
import { ArrowLeft, CheckCircle, XCircle, AlertCircle, Calendar, DollarSign, User, FileText, Building2, Receipt, Package } from 'lucide-react';
import AdminLayout from '@/Layouts/AdminLayout';

interface User {
    id: number;
    full_name: string;
}

interface Project {
    id: number;
    name: string;
    code: string;
}

interface FileAttachment {
    id: number;
    file_name: string;
    file_path: string;
    file_size: number;
}

interface Expenditure {
    id: number;
    code: string;
    description: string;
    amount: number;
    currency: string;
    expenditure_date: string;
    category: string;
    subcategory?: string;
    vendor?: string;
    invoice_number?: string;
    receipt_number?: string;
    status: 'PENDING' | 'APPROVED' | 'REJECTED' | 'VERIFIED';
    approval_notes?: string;
    approved_at?: string;
    verification_notes?: string;
    verified_at?: string;
    project: Project;
    entered_by: User;
    approved_by?: User;
    verified_by?: User;
    file_attachments?: FileAttachment[];
    created_at: string;
}

interface ShowProps {
    expenditure: Expenditure;
}

export default function Show({ expenditure }: ShowProps) {
    const getStatusBadge = (status: string) => {
        const styles = {
            PENDING: 'bg-yellow-100 text-yellow-800',
            APPROVED: 'bg-green-100 text-green-800',
            REJECTED: 'bg-red-100 text-red-800',
            VERIFIED: 'bg-blue-100 text-blue-800',
        };
        return styles[status as keyof typeof styles] || 'bg-gray-100 text-gray-800';
    };

    const handleApprove = () => {
        const notes = prompt('Approval notes (optional):');
        if (notes !== null) {
            router.post(`/admin/expenditures/${expenditure.id}/approve`, { approval_notes: notes });
        }
    };

    const handleReject = () => {
        const notes = prompt('Rejection reason:');
        if (notes) {
            router.post(`/admin/expenditures/${expenditure.id}/reject`, { approval_notes: notes });
        }
    };

    const handleVerify = () => {
        const notes = prompt('Verification notes (optional):');
        if (notes !== null) {
            router.post(`/admin/expenditures/${expenditure.id}/verify`, { verification_notes: notes });
        }
    };

    const formatCurrency = (amount: number, currency: string = 'TZS') => {
        return new Intl.NumberFormat('en-TZ', {
            style: 'currency',
            currency: currency,
        }).format(amount);
    };

    const formatFileSize = (bytes: number) => {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return Math.round((bytes / Math.pow(k, i)) * 100) / 100 + ' ' + sizes[i];
    };

    return (
        <AdminLayout header={`Expenditure: ${expenditure.code}`}>
            <Head title={`Expenditure: ${expenditure.code}`} />

            {/* Header */}
            <div className="mb-6 flex items-center justify-between">
                <Link href="/admin/expenditures" className="inline-flex items-center gap-2 text-gray-600 hover:text-gray-900">
                    <ArrowLeft className="h-4 w-4" />
                    Back to Expenditures
                </Link>
                <div className="flex gap-2">
                    {expenditure.status === 'PENDING' && (
                        <>
                            <button
                                onClick={handleApprove}
                                className="inline-flex items-center gap-2 rounded-lg bg-green-600 px-4 py-2 text-white hover:bg-green-700"
                            >
                                <CheckCircle className="h-4 w-4" />
                                Approve
                            </button>
                            <button
                                onClick={handleReject}
                                className="inline-flex items-center gap-2 rounded-lg bg-red-600 px-4 py-2 text-white hover:bg-red-700"
                            >
                                <XCircle className="h-4 w-4" />
                                Reject
                            </button>
                        </>
                    )}
                    {expenditure.status === 'APPROVED' && (
                        <button
                            onClick={handleVerify}
                            className="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-white hover:bg-blue-700"
                        >
                            <CheckCircle className="h-4 w-4" />
                            Verify
                        </button>
                    )}
                </div>
            </div>

            <div className="grid grid-cols-1 gap-6 lg:grid-cols-3">
                {/* Main Details */}
                <div className="space-y-6 lg:col-span-2">
                    {/* Basic Information */}
                    <div className="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                        <div className="mb-4 flex items-center justify-between">
                            <h2 className="text-xl font-semibold text-gray-900">Expenditure Details</h2>
                            <span className={`rounded-full px-3 py-1 text-sm font-medium ${getStatusBadge(expenditure.status)}`}>
                                {expenditure.status}
                            </span>
                        </div>

                        <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div>
                                <p className="text-sm text-gray-600">Code</p>
                                <p className="font-medium text-gray-900">{expenditure.code}</p>
                            </div>
                            <div>
                                <p className="text-sm text-gray-600">Amount</p>
                                <p className="text-lg font-bold text-gray-900">{formatCurrency(expenditure.amount, expenditure.currency)}</p>
                            </div>
                            <div>
                                <p className="text-sm text-gray-600">Date</p>
                                <div className="flex items-center gap-2">
                                    <Calendar className="h-4 w-4 text-gray-400" />
                                    <p className="text-gray-900">{new Date(expenditure.expenditure_date).toLocaleDateString()}</p>
                                </div>
                            </div>
                            <div>
                                <p className="text-sm text-gray-600">Category</p>
                                <div className="flex items-center gap-2">
                                    <Package className="h-4 w-4 text-gray-400" />
                                    <p className="text-gray-900">{expenditure.category}</p>
                                </div>
                            </div>
                            {expenditure.subcategory && (
                                <div>
                                    <p className="text-sm text-gray-600">Subcategory</p>
                                    <p className="text-gray-900">{expenditure.subcategory}</p>
                                </div>
                            )}
                            <div>
                                <p className="text-sm text-gray-600">Project</p>
                                <Link href={`/admin/projects/${expenditure.project.id}`} className="font-medium text-blue-600 hover:text-blue-700">
                                    {expenditure.project.name} ({expenditure.project.code})
                                </Link>
                            </div>
                        </div>

                        <div className="mt-4">
                            <p className="text-sm text-gray-600">Description</p>
                            <p className="mt-1 text-gray-900">{expenditure.description}</p>
                        </div>
                    </div>

                    {/* Vendor & Receipt Information */}
                    {(expenditure.vendor || expenditure.invoice_number || expenditure.receipt_number) && (
                        <div className="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                            <h2 className="mb-4 text-lg font-semibold text-gray-900">Payment Information</h2>
                            <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                                {expenditure.vendor && (
                                    <div>
                                        <p className="text-sm text-gray-600">Vendor</p>
                                        <div className="flex items-center gap-2">
                                            <Building2 className="h-4 w-4 text-gray-400" />
                                            <p className="text-gray-900">{expenditure.vendor}</p>
                                        </div>
                                    </div>
                                )}
                                {expenditure.invoice_number && (
                                    <div>
                                        <p className="text-sm text-gray-600">Invoice Number</p>
                                        <div className="flex items-center gap-2">
                                            <FileText className="h-4 w-4 text-gray-400" />
                                            <p className="text-gray-900">{expenditure.invoice_number}</p>
                                        </div>
                                    </div>
                                )}
                                {expenditure.receipt_number && (
                                    <div>
                                        <p className="text-sm text-gray-600">Receipt Number</p>
                                        <div className="flex items-center gap-2">
                                            <Receipt className="h-4 w-4 text-gray-400" />
                                            <p className="text-gray-900">{expenditure.receipt_number}</p>
                                        </div>
                                    </div>
                                )}
                            </div>
                        </div>
                    )}

                    {/* Attachments */}
                    {expenditure.file_attachments && expenditure.file_attachments.length > 0 && (
                        <div className="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                            <h2 className="mb-4 text-lg font-semibold text-gray-900">Attachments</h2>
                            <div className="space-y-2">
                                {expenditure.file_attachments.map((file) => (
                                    <a
                                        key={file.id}
                                        href={`/storage/${file.file_path}`}
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        className="flex items-center justify-between rounded-lg border border-gray-200 p-3 hover:bg-gray-50"
                                    >
                                        <div className="flex items-center gap-3">
                                            <FileText className="h-5 w-5 text-gray-400" />
                                            <div>
                                                <p className="text-sm font-medium text-gray-900">{file.file_name}</p>
                                                <p className="text-xs text-gray-500">{formatFileSize(file.file_size)}</p>
                                            </div>
                                        </div>
                                    </a>
                                ))}
                            </div>
                        </div>
                    )}
                </div>

                {/* Sidebar */}
                <div className="space-y-6">
                    {/* Workflow Status */}
                    <div className="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                        <h3 className="mb-4 text-lg font-semibold text-gray-900">Workflow</h3>
                        <div className="space-y-4">
                            {/* Created */}
                            <div>
                                <div className="flex items-center gap-2">
                                    <User className="h-4 w-4 text-gray-400" />
                                    <p className="text-sm font-medium text-gray-900">Created By</p>
                                </div>
                                <p className="ml-6 text-sm text-gray-600">{expenditure.entered_by.full_name}</p>
                                <p className="ml-6 text-xs text-gray-500">{new Date(expenditure.created_at).toLocaleString()}</p>
                            </div>

                            {/* Approved */}
                            {expenditure.approved_by && (
                                <div className="border-t pt-4">
                                    <div className="flex items-center gap-2">
                                        <CheckCircle className="h-4 w-4 text-green-600" />
                                        <p className="text-sm font-medium text-gray-900">Approved By</p>
                                    </div>
                                    <p className="ml-6 text-sm text-gray-600">{expenditure.approved_by.full_name}</p>
                                    <p className="ml-6 text-xs text-gray-500">
                                        {expenditure.approved_at ? new Date(expenditure.approved_at).toLocaleString() : ''}
                                    </p>
                                    {expenditure.approval_notes && (
                                        <p className="mt-1 ml-6 text-sm text-gray-600 italic">"{expenditure.approval_notes}"</p>
                                    )}
                                </div>
                            )}

                            {/* Verified */}
                            {expenditure.verified_by && (
                                <div className="border-t pt-4">
                                    <div className="flex items-center gap-2">
                                        <CheckCircle className="h-4 w-4 text-blue-600" />
                                        <p className="text-sm font-medium text-gray-900">Verified By</p>
                                    </div>
                                    <p className="ml-6 text-sm text-gray-600">{expenditure.verified_by.full_name}</p>
                                    <p className="ml-6 text-xs text-gray-500">
                                        {expenditure.verified_at ? new Date(expenditure.verified_at).toLocaleString() : ''}
                                    </p>
                                    {expenditure.verification_notes && (
                                        <p className="mt-1 ml-6 text-sm text-gray-600 italic">"{expenditure.verification_notes}"</p>
                                    )}
                                </div>
                            )}

                            {/* Rejected */}
                            {expenditure.status === 'REJECTED' && expenditure.approval_notes && (
                                <div className="border-t pt-4">
                                    <div className="flex items-center gap-2">
                                        <XCircle className="h-4 w-4 text-red-600" />
                                        <p className="text-sm font-medium text-gray-900">Rejection Reason</p>
                                    </div>
                                    <p className="mt-1 ml-6 text-sm text-gray-600 italic">"{expenditure.approval_notes}"</p>
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}
