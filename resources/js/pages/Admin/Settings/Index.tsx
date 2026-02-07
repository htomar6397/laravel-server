import { Head, useForm } from '@inertiajs/react';
import { Save, Settings as SettingsIcon, Shield, Database, Mail } from 'lucide-react';
import { useState } from 'react';
import AdminLayout from '@/Layouts/AdminLayout';

export default function Index() {
    const [activeTab, setActiveTab] = useState('general');

    const { data, setData, post, processing } = useForm({
        site_name: 'KMC M&E System',
        site_email: 'admin@kmc.go.tz',
        // notifications_enabled: true,
        // email_notifications: true,
        backup_enabled: true,
        backup_frequency: 'daily',
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post('/admin/settings');
    };

    const tabs = [
        { id: 'general', name: 'General', icon: SettingsIcon },
        // { id: 'notifications', name: 'Notifications', icon: Bell },
        { id: 'security', name: 'Security', icon: Shield },
        { id: 'backup', name: 'Backup', icon: Database },
        { id: 'email', name: 'Email', icon: Mail },
    ];

    return (
        <AdminLayout header="System Settings">
            <Head title="Settings" />

            <div className="grid grid-cols-1 gap-6 lg:grid-cols-4">
                {/* Tabs */}
                <div className="lg:col-span-1">
                    <div className="rounded-xl border border-gray-200 bg-white shadow-sm">
                        <div className="p-4">
                            <h3 className="font-semibold text-gray-900">Settings</h3>
                        </div>
                        <nav className="space-y-1 p-2">
                            {tabs.map((tab) => {
                                const Icon = tab.icon;
                                return (
                                    <button
                                        key={tab.id}
                                        onClick={() => setActiveTab(tab.id)}
                                        className={`flex w-full items-center gap-3 rounded-lg px-3 py-2 text-sm ${
                                            activeTab === tab.id ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-50'
                                        }`}
                                    >
                                        <Icon className="h-4 w-4" />
                                        {tab.name}
                                    </button>
                                );
                            })}
                        </nav>
                    </div>
                </div>

                {/* Content */}
                <div className="lg:col-span-3">
                    <form onSubmit={handleSubmit} className="space-y-6">
                        {/* General Settings */}
                        {activeTab === 'general' && (
                            <div className="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                                <h3 className="mb-4 text-lg font-semibold text-gray-900">General Settings</h3>

                                <div className="space-y-4">
                                    <div>
                                        <label className="mb-2 block text-sm font-medium text-gray-700">Site Name</label>
                                        <input
                                            type="text"
                                            value={data.site_name}
                                            onChange={(e) => setData('site_name', e.target.value)}
                                            className="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                                        />
                                    </div>

                                    <div>
                                        <label className="mb-2 block text-sm font-medium text-gray-700">Site Email</label>
                                        <input
                                            type="email"
                                            value={data.site_email}
                                            onChange={(e) => setData('site_email', e.target.value)}
                                            className="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                                        />
                                    </div>
                                </div>
                            </div>
                        )}

                        {/* Notification Settings */}
                        {/*
                        {activeTab === 'notifications' && (
                            <div className="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                                <h3 className="mb-4 text-lg font-semibold text-gray-900">Notification Settings</h3>

                                <div className="space-y-4">
                                    <label className="flex items-center gap-3">
                                        <input
                                            type="checkbox"
                                            checked={data.notifications_enabled}
                                            onChange={(e) => setData('notifications_enabled', e.target.checked)}
                                            className="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                        />
                                        <span className="text-sm font-medium text-gray-700">Enable System Notifications</span>
                                    </label>

                                    <label className="flex items-center gap-3">
                                        <input
                                            type="checkbox"
                                            checked={data.email_notifications}
                                            onChange={(e) => setData('email_notifications', e.target.checked)}
                                            className="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                        />
                                        <span className="text-sm font-medium text-gray-700">Enable Email Notifications</span>
                                    </label>
                                </div>
                            </div>
                        )}
                        */}

                        {/* Security Settings */}
                        {activeTab === 'security' && (
                            <div className="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                                <h3 className="mb-4 text-lg font-semibold text-gray-900">Security Settings</h3>
                                <p className="text-sm text-gray-600">Security settings will be configured here.</p>
                            </div>
                        )}

                        {/* Backup Settings */}
                        {activeTab === 'backup' && (
                            <div className="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                                <h3 className="mb-4 text-lg font-semibold text-gray-900">Backup Settings</h3>

                                <div className="space-y-4">
                                    <label className="flex items-center gap-3">
                                        <input
                                            type="checkbox"
                                            checked={data.backup_enabled}
                                            onChange={(e) => setData('backup_enabled', e.target.checked)}
                                            className="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                        />
                                        <span className="text-sm font-medium text-gray-700">Enable Automatic Backups</span>
                                    </label>

                                    <div>
                                        <label className="mb-2 block text-sm font-medium text-gray-700">Backup Frequency</label>
                                        <select
                                            value={data.backup_frequency}
                                            onChange={(e) => setData('backup_frequency', e.target.value)}
                                            className="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                                        >
                                            <option value="hourly">Hourly</option>
                                            <option value="daily">Daily</option>
                                            <option value="weekly">Weekly</option>
                                            <option value="monthly">Monthly</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        )}

                        {/* Email Settings */}
                        {activeTab === 'email' && (
                            <div className="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                                <h3 className="mb-4 text-lg font-semibold text-gray-900">Email Settings</h3>
                                <p className="text-sm text-gray-600">Email configuration will be managed here.</p>
                            </div>
                        )}

                        {/* Save Button */}
                        <div className="flex justify-end">
                            <button
                                type="submit"
                                disabled={processing}
                                className="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-6 py-2 text-white hover:bg-blue-700 disabled:opacity-50"
                            >
                                <Save className="h-4 w-4" />
                                Save Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </AdminLayout>
    );
}
