import { Link, usePage } from '@inertiajs/react';
import {
    Home,
    Users,
    FolderKanban,
    FileText,
    DollarSign,
    Camera,
    Settings,
    Menu,
    X,
    BarChart3,
    TrendingUp,
    Shield,
    Building2,
    Palette,
    FileSearch,
    Sparkles,
    FileBarChart,
    Activity,
    Network,
    ChevronDown,
    User,
} from 'lucide-react';
import type { PropsWithChildren } from 'react';
import { useState, useEffect, useRef } from 'react';
import { ErrorBoundary } from '@/components';
import { useErrorReporting } from '@/hooks/useErrorReporting';
import type { Auth } from '@/types';

interface AdminLayoutProps {
    header?: string;
}

export default function AdminLayout({ children, header }: PropsWithChildren<AdminLayoutProps>) {
    const [sidebarOpen, setSidebarOpen] = useState(false);
    const [profileDropdownOpen, setProfileDropdownOpen] = useState(false);
    const page = usePage();
    const auth = page.props.auth as Auth;
    const user = auth.user;
    const currentUrl = page.url || window.location.pathname || '';
    const profileDropdownRef = useRef<HTMLDivElement>(null);
    const { reportError } = useErrorReporting();

    // Close profile dropdown when clicking outside
    useEffect(() => {
        function handleClickOutside(event: MouseEvent) {
            if (profileDropdownRef.current && !profileDropdownRef.current.contains(event.target as Node)) {
                setProfileDropdownOpen(false);
            }
        }

        document.addEventListener('mousedown', handleClickOutside);
        return () => {
            document.removeEventListener('mousedown', handleClickOutside);
        };
    }, []);

    const navigationGroups = [
        {
            title: 'Core',
            items: [
                { name: 'Dashboard', href: '/admin/dashboard', icon: Home },
                { name: 'Users', href: '/admin/users', icon: Users },
                { name: 'Roles', href: '/admin/roles', icon: Shield },
            ],
        },
        {
            title: 'Projects & Data',
            items: [
                { name: 'Projects', href: '/admin/projects', icon: FolderKanban },
                { name: 'Indicators', href: '/admin/indicators', icon: TrendingUp },
                { name: 'Data Entries', href: '/admin/data-entries', icon: FileText },
                { name: 'Expenditures', href: '/admin/expenditures', icon: DollarSign },
                { name: 'Photo Gallery', href: '/admin/photos', icon: Camera },
            ],
        },
        {
            title: 'Organization',
            items: [
                { name: 'Org. Units', href: '/admin/organizational-units', icon: Building2 },
                { name: 'Themes', href: '/admin/themes', icon: Palette },
            ],
        },
        {
            title: 'Reports & Analytics',
            items: [
                { name: 'Reports', href: '/admin/reports', icon: BarChart3 },
                { name: 'AI Reports', href: '/admin/ai-reports', icon: Sparkles },
                { name: 'Quarterly Pack', href: '/admin/quarterly-pack', icon: FileBarChart },
                { name: 'Audit Logs', href: '/admin/audit-logs', icon: FileSearch },
            ],
        },
        {
            title: 'Integrations',
            items: [
                { name: 'DHIS2', href: '/admin/dhis2', icon: Activity },
                { name: 'PlanRep', href: '/admin/planrep', icon: Network },
            ],
        },
        {
            title: 'System',
            items: [
                // { name: 'Notifications', href: '/admin/notifications', icon: BellRing },
                { name: 'Settings', href: '/admin/settings', icon: Settings },
            ],
        },
    ];

    const isActive = (path: string) => (currentUrl && typeof currentUrl === 'string' ? currentUrl.startsWith(path) : false);

    return (
        <div className="min-h-screen bg-gray-50">
            {/* Sidebar */}
            <div
                className={`fixed inset-y-0 left-0 z-50 flex w-64 flex-col bg-linear-to-b from-blue-900 to-blue-800 transition-transform duration-300 ease-in-out ${
                    sidebarOpen ? 'translate-x-0' : '-translate-x-full'
                } lg:translate-x-0`}
            >
                <div className="flex h-16 items-center justify-between border-b border-blue-700 px-6">
                    <div className="flex items-center gap-3">
                        <div className="flex h-10 w-10 items-center justify-center rounded-lg bg-white">
                            <TrendingUp className="h-6 w-6 text-blue-900" />
                        </div>
                        <span className="text-xl font-bold text-white">KMC M&E</span>
                    </div>
                    <button onClick={() => setSidebarOpen(false)} className="text-white hover:text-gray-300 lg:hidden">
                        <X className="h-6 w-6" />
                    </button>
                </div>

                <nav className="custom-scrollbar mt-6 flex-1 space-y-6 overflow-y-auto px-3 pb-4">
                    {navigationGroups.map((group) => (
                        <div key={group.title}>
                            <h3 className="mb-2 px-3 text-xs font-semibold tracking-wider text-blue-200 uppercase">{group.title}</h3>
                            <div className="space-y-1">
                                {group.items.map((item) => {
                                    const Icon = item.icon;
                                    const active = isActive(item.href);

                                    return (
                                        <Link
                                            key={item.name}
                                            href={item.href}
                                            className={`flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-colors ${
                                                active ? 'bg-blue-700 text-white' : 'text-blue-100 hover:bg-blue-700/50 hover:text-white'
                                            }`}
                                        >
                                            <Icon className="h-4 w-4" />
                                            {item.name}
                                        </Link>
                                    );
                                })}
                            </div>
                        </div>
                    ))}
                </nav>
            </div>

            {/* Main Content */}
            <div className="transition-all duration-300 lg:pl-64">
                {/* Top Bar */}
                <div className="sticky top-0 z-40 border-b border-gray-200 bg-white shadow-sm">
                    <div className="flex h-16 items-center justify-between px-4 sm:px-6 lg:px-8">
                        <div className="flex items-center gap-4">
                            <button onClick={() => setSidebarOpen(!sidebarOpen)} className="text-gray-500 hover:text-gray-700 lg:hidden">
                                <Menu className="h-6 w-6" />
                            </button>

                            {header && <h1 className="text-2xl font-bold text-gray-900">{header}</h1>}
                        </div>

                        <div className="flex items-center gap-4">
                            {/* <button className="relative rounded-lg p-2 text-gray-500 hover:bg-gray-100 hover:text-gray-700">
                                <Bell className="h-6 w-6" />
                                <span className="absolute top-1 right-1 h-2 w-2 rounded-full bg-red-500"></span>
                            </button> */}

                            <div className="relative" ref={profileDropdownRef}>
                                <button
                                    onClick={() => setProfileDropdownOpen(!profileDropdownOpen)}
                                    className="flex items-center gap-3 rounded-lg p-2 text-gray-500 hover:bg-gray-100 hover:text-gray-700"
                                >
                                    <div className="flex h-8 w-8 items-center justify-center rounded-full bg-linear-to-br from-blue-500 to-blue-600 text-sm font-semibold text-white">
                                        {user?.full_name
                                            ?.split(' ')
                                            .map((n) => n[0])
                                            .join('')
                                            .toUpperCase() || 'U'}
                                    </div>
                                    <ChevronDown className="h-4 w-4" />
                                </button>

                                {profileDropdownOpen && (
                                    <div className="absolute right-0 z-50 mt-2 w-64 rounded-lg border border-gray-200 bg-white shadow-lg">
                                        <div className="border-b border-gray-200 px-4 py-3">
                                            <p className="text-sm font-medium text-gray-900">{user?.full_name || 'User'}</p>
                                            <p className="text-sm text-gray-500">{user?.email}</p>
                                            <p className="mt-1 text-xs text-gray-400">{user?.position || 'No position'}</p>
                                        </div>
                                        <div className="py-1">
                                            <Link
                                                href="/admin/profile"
                                                className="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                                onClick={() => setProfileDropdownOpen(false)}
                                            >
                                                <User className="h-4 w-4" />
                                                View Profile
                                            </Link>
                                            <Link
                                                href="/admin/settings"
                                                className="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                                onClick={() => setProfileDropdownOpen(false)}
                                            >
                                                <Settings className="h-4 w-4" />
                                                Settings
                                            </Link>
                                        </div>
                                    </div>
                                )}
                            </div>
                        </div>
                    </div>
                </div>

                {/* Page Content */}
                <main className="p-4 sm:p-6 lg:p-8">
                    <ErrorBoundary
                        onError={(error, errorInfo) => {
                            const fixedErrorInfo = { ...errorInfo, componentStack: errorInfo.componentStack ?? undefined };
                            reportError(error, fixedErrorInfo);
                        }}
                        showDetails={process.env.NODE_ENV === 'development'}
                        className="min-h-[calc(100vh-8rem)]"
                    >
                        {children}
                    </ErrorBoundary>
                </main>
            </div>

            {/* Mobile Sidebar Overlay */}
            {sidebarOpen && <div className="fixed inset-0 z-40 bg-black/50 lg:hidden" onClick={() => setSidebarOpen(false)} />}
        </div>
    );
}
