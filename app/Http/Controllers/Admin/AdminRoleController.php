<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminRoleController extends Controller
{
    /**
     * All available permissions in the system
     */
    private function getAvailablePermissions()
    {
        return [
            'User Management' => [
                'users.view' => 'View users',
                'users.create' => 'Create users',
                'users.edit' => 'Edit users',
                'users.delete' => 'Delete users',
                'users.activate' => 'Activate/Deactivate users',
            ],
            'Role Management' => [
                'roles.view' => 'View roles',
                'roles.create' => 'Create roles',
                'roles.edit' => 'Edit roles',
                'roles.delete' => 'Delete roles',
            ],
            'Project Management' => [
                'projects.view' => 'View projects',
                'projects.create' => 'Create projects',
                'projects.edit' => 'Edit projects',
                'projects.delete' => 'Delete projects',
                'projects.approve' => 'Approve projects',
            ],
            'Indicator Management' => [
                'indicators.view' => 'View indicators',
                'indicators.create' => 'Create indicators',
                'indicators.edit' => 'Edit indicators',
                'indicators.delete' => 'Delete indicators',
            ],
            'Data Entry' => [
                'data-entries.view' => 'View data entries',
                'data-entries.create' => 'Create data entries',
                'data-entries.edit' => 'Edit data entries',
                'data-entries.delete' => 'Delete data entries',
                'data-entries.verify' => 'Verify data entries',
                'data-entries.export' => 'Export data entries',
            ],
            'Expenditure Management' => [
                'expenditures.view' => 'View expenditures',
                'expenditures.create' => 'Create expenditures',
                'expenditures.edit' => 'Edit expenditures',
                'expenditures.delete' => 'Delete expenditures',
                'expenditures.approve' => 'Approve expenditures',
                'expenditures.verify' => 'Verify expenditures',
                'expenditures.export' => 'Export expenditures',
            ],
            'Photo Gallery' => [
                'photos.view' => 'View photos',
                'photos.create' => 'Upload photos',
                'photos.delete' => 'Delete photos',
                'photos.download' => 'Download photos',
            ],
            'Reports' => [
                'reports.view' => 'View reports',
                'reports.generate' => 'Generate reports',
                'reports.export' => 'Export reports',
                'reports.ai' => 'Generate AI reports',
            ],
            'Organizational Units' => [
                'organizational-units.view' => 'View organizational units',
                'organizational-units.create' => 'Create organizational units',
                'organizational-units.edit' => 'Edit organizational units',
                'organizational-units.delete' => 'Delete organizational units',
            ],
            'Themes' => [
                'themes.view' => 'View themes',
                'themes.create' => 'Create themes',
                'themes.edit' => 'Edit themes',
                'themes.delete' => 'Delete themes',
            ],
            'Audit Logs' => [
                'audit-logs.view' => 'View audit logs',
                'audit-logs.export' => 'Export audit logs',
            ],
            'Notifications' => [
                'notifications.view' => 'View notifications',
                'notifications.send' => 'Send notifications',
            ],
            'Integrations' => [
                'integrations.dhis2' => 'Manage DHIS2 integration',
                'integrations.planrep' => 'Manage PlanRep integration',
            ],
            'Settings' => [
                'settings.view' => 'View settings',
                'settings.edit' => 'Edit settings',
            ],
        ];
    }

    /**
     * Display a listing of roles
     */
    public function index(Request $request)
    {
        $query = Role::withCount('users');

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $roles = $query->latest()->paginate(15)->withQueryString();

        return Inertia::render('Admin/Roles/Index', [
            'roles' => $roles,
            'filters' => $request->only(['search']),
        ]);
    }

    /**
     * Show the form for creating a new role
     */
    public function create()
    {
        return Inertia::render('Admin/Roles/Create', [
            'availablePermissions' => $this->getAvailablePermissions(),
        ]);
    }

    /**
     * Store a newly created role
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'description' => 'nullable|string',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string',
        ]);

        Role::create($validated);

        return redirect()->route('admin.roles.index')
            ->with('success', 'Role created successfully.');
    }

    /**
     * Show the form for editing the specified role
     */
    public function edit(Role $role)
    {
        if ($role->is_system) {
            return redirect()->route('admin.roles.index')
                ->with('error', 'System roles cannot be edited.');
        }

        return Inertia::render('Admin/Roles/Edit', [
            'role' => $role,
            'availablePermissions' => $this->getAvailablePermissions(),
        ]);
    }

    /**
     * Update the specified role
     */
    public function update(Request $request, Role $role)
    {
        if ($role->is_system) {
            return redirect()->route('admin.roles.index')
                ->with('error', 'System roles cannot be edited.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
            'description' => 'nullable|string',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string',
        ]);

        $role->update($validated);

        return redirect()->route('admin.roles.index')
            ->with('success', 'Role updated successfully.');
    }

    /**
     * Remove the specified role
     */
    public function destroy(Role $role)
    {
        if ($role->is_system) {
            return redirect()->route('admin.roles.index')
                ->with('error', 'System roles cannot be deleted.');
        }

        if ($role->users()->count() > 0) {
            return redirect()->route('admin.roles.index')
                ->with('error', 'Cannot delete role with assigned users.');
        }

        $role->delete();

        return redirect()->route('admin.roles.index')
            ->with('success', 'Role deleted successfully.');
    }
}
