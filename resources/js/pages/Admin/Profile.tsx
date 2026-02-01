import { usePage } from '@inertiajs/react';
import { User, Mail, Phone, Building, MapPin, Calendar, Shield } from 'lucide-react';
import AdminLayout from '@/Layouts/AdminLayout';
import type { SharedData } from '@/types';

export default function Profile() {
    const { auth } = usePage<SharedData>().props;
    const user = auth.user;

    // Mock additional user data - in real app this would come from the backend
    const profileData = {
        username: user.username || 'N/A',
        email: user.email,
        full_name: user.full_name || 'N/A',
        department: user.department || 'N/A',
        position: String(user.position || 'N/A'),
        phone: user.phone || 'N/A',
        org_unit: typeof user.org_unit === 'object' && user.org_unit ? user.org_unit.name : user.org_unit || 'N/A',
        is_active: user.is_active,
        last_login: user.last_login_at || 'Never',
        created_at: user.created_at,
        roles: user.roles || [], // Assuming roles are loaded
    };

    return (
        <AdminLayout header="Profile">
            <div className="mx-auto max-w-4xl">
                <div className="rounded-lg border border-gray-200 bg-white shadow-sm">
                    {/* Profile Header */}
                    <div className="border-b border-gray-200 px-6 py-8">
                        <div className="flex items-center gap-6">
                            <div className="flex h-20 w-20 items-center justify-center rounded-full bg-linear-to-br from-blue-500 to-blue-600 text-2xl font-bold text-white">
                                {profileData.full_name
                                    .split(' ')
                                    .map((n) => n[0])
                                    .join('')
                                    .toUpperCase()}
                            </div>
                            <div>
                                <h1 className="text-2xl font-bold text-gray-900">{profileData.full_name}</h1>
                                <p className="text-gray-600">{profileData.position}</p>
                                <div className="mt-1 flex items-center gap-2">
                                    <div className={`h-2 w-2 rounded-full ${profileData.is_active ? 'bg-green-500' : 'bg-red-500'}`}></div>
                                    <span className="text-sm text-gray-500">{profileData.is_active ? 'Active' : 'Inactive'}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Profile Details */}
                    <div className="px-6 py-6">
                        <div className="grid grid-cols-1 gap-6 md:grid-cols-2">
                            {/* Personal Information */}
                            <div>
                                <h3 className="mb-4 text-lg font-semibold text-gray-900">Personal Information</h3>
                                <div className="space-y-4">
                                    <div className="flex items-center gap-3">
                                        <User className="h-5 w-5 text-gray-400" />
                                        <div>
                                            <p className="text-sm font-medium text-gray-900">Full Name</p>
                                            <p className="text-sm text-gray-600">{profileData.full_name}</p>
                                        </div>
                                    </div>
                                    <div className="flex items-center gap-3">
                                        <Mail className="h-5 w-5 text-gray-400" />
                                        <div>
                                            <p className="text-sm font-medium text-gray-900">Email</p>
                                            <p className="text-sm text-gray-600">{profileData.email}</p>
                                        </div>
                                    </div>
                                    <div className="flex items-center gap-3">
                                        <Phone className="h-5 w-5 text-gray-400" />
                                        <div>
                                            <p className="text-sm font-medium text-gray-900">Phone</p>
                                            <p className="text-sm text-gray-600">{profileData.phone}</p>
                                        </div>
                                    </div>
                                    <div className="flex items-center gap-3">
                                        <Shield className="h-5 w-5 text-gray-400" />
                                        <div>
                                            <p className="text-sm font-medium text-gray-900">Username</p>
                                            <p className="text-sm text-gray-600">{profileData.username}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {/* Work Information */}
                            <div>
                                <h3 className="mb-4 text-lg font-semibold text-gray-900">Work Information</h3>
                                <div className="space-y-4">
                                    <div className="flex items-center gap-3">
                                        <Building className="h-5 w-5 text-gray-400" />
                                        <div>
                                            <p className="text-sm font-medium text-gray-900">Department</p>
                                            <p className="text-sm text-gray-600">{profileData.department}</p>
                                        </div>
                                    </div>
                                    <div className="flex items-center gap-3">
                                        <MapPin className="h-5 w-5 text-gray-400" />
                                        <div>
                                            <p className="text-sm font-medium text-gray-900">Position</p>
                                            <p className="text-sm text-gray-600">{profileData.position}</p>
                                        </div>
                                    </div>
                                    <div className="flex items-center gap-3">
                                        <Building className="h-5 w-5 text-gray-400" />
                                        <div>
                                            <p className="text-sm font-medium text-gray-900">Organization Unit</p>
                                            <p className="text-sm text-gray-600">{profileData.org_unit}</p>
                                        </div>
                                    </div>
                                    <div className="flex items-center gap-3">
                                        <Calendar className="h-5 w-5 text-gray-400" />
                                        <div>
                                            <p className="text-sm font-medium text-gray-900">Last Login</p>
                                            <p className="text-sm text-gray-600">{profileData.last_login}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {/* Account Information */}
                        <div className="mt-8 border-t border-gray-200 pt-6">
                            <h3 className="mb-4 text-lg font-semibold text-gray-900">Account Information</h3>
                            <div className="grid grid-cols-1 gap-4 md:grid-cols-3">
                                <div className="rounded-lg bg-gray-50 p-4">
                                    <p className="text-sm font-medium text-gray-900">Member Since</p>
                                    <p className="text-lg font-semibold text-blue-600">{new Date(profileData.created_at).toLocaleDateString()}</p>
                                </div>
                                <div className="rounded-lg bg-gray-50 p-4">
                                    <p className="text-sm font-medium text-gray-900">Account Status</p>
                                    <p className={`text-lg font-semibold ${profileData.is_active ? 'text-green-600' : 'text-red-600'}`}>
                                        {profileData.is_active ? 'Active' : 'Inactive'}
                                    </p>
                                </div>
                                <div className="rounded-lg bg-gray-50 p-4">
                                    <p className="text-sm font-medium text-gray-900">Roles</p>
                                    <p className="text-lg font-semibold text-gray-900">
                                        {profileData.roles.length > 0 ? profileData.roles.join(', ') : 'No roles assigned'}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}
