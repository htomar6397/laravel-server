<?php

namespace App\Http\Controllers;

use App\Models\{User, Role, OrganizationalUnit};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

/**
 * UserController
 * 
 * Handles user management operations for the KMC M&E System
 */
class UserController extends Controller
{
    /**
     * Display a listing of users
     */
    public function index(Request $request)
    {
        $query = User::with(['roles', 'organizationalUnit']);

        // Apply filters
        if ($request->has('status') && $request->status != '') {
            if ($request->status === 'active') {
                $query->active();
            } elseif ($request->status === 'inactive') {
                $query->inactive();
            }
        }

        if ($request->has('role') && $request->role != '') {
            $query->withRole($request->role);
        }

        if ($request->has('org_unit') && $request->org_unit != '') {
            $query->byOrgUnit($request->org_unit);
        }

        if ($request->has('search') && $request->search != '') {
            $query->search($request->search);
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Paginate
        $users = $query->paginate(20);

        // Get filter options
        $roles = Role::orderBy('name')->get();
        $orgUnits = OrganizationalUnit::active()->orderBy('name')->get();

        // Calculate statistics
        $stats = [
            'total' => User::count(),
            'active' => User::active()->count(),
            'inactive' => User::inactive()->count(),
            'this_month' => User::whereMonth('created_at', now()->month)->count(),
        ];

        return view('users.index', compact(
            'users',
            'roles',
            'orgUnits',
            'stats'
        ));
    }

    /**
     * Show the form for creating a new user
     */
    public function create()
    {
        $roles = Role::orderBy('name')->get();
        $orgUnits = OrganizationalUnit::active()->orderBy('name')->get();

        return view('users.create', compact('roles', 'orgUnits'));
    }

    /**
     * Store a newly created user
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required|string|max:50|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Password::defaults()],
            'full_name' => 'required|string|max:255',
            'org_unit_id' => 'nullable|exists:organizational_units,id',
            'department' => 'nullable|string|max:100',
            'position' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:20',
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,id',
            'is_active' => 'boolean',
        ]);

        $user = User::create([
            'username' => $validated['username'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'full_name' => $validated['full_name'],
            'org_unit_id' => $validated['org_unit_id'],
            'department' => $validated['department'],
            'position' => $validated['position'],
            'phone' => $validated['phone'],
            'is_active' => $validated['is_active'] ?? true,
        ]);

        // Assign roles
        $user->syncRoles($validated['roles']);

        return redirect()
            ->route('users.show', $user)
            ->with('success', 'User created successfully!');
    }

    /**
     * Display the specified user
     */
    public function show(User $user)
    {
        $user->load(['roles', 'organizationalUnit', 'supervisor', 'supervisedUsers']);
        
        $statistics = $user->getStatistics();
        $permissions = $user->getAllPermissions();

        return view('users.show', compact('user', 'statistics', 'permissions'));
    }

    /**
     * Show the form for editing the specified user
     */
    public function edit(User $user)
    {
        $roles = Role::orderBy('name')->get();
        $orgUnits = OrganizationalUnit::active()->orderBy('name')->get();
        $userRoles = $user->roles->pluck('id')->toArray();

        return view('users.edit', compact('user', 'roles', 'orgUnits', 'userRoles'));
    }

    /**
     * Update the specified user
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'username' => 'required|string|max:50|unique:users,username,' . $user->id,
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'full_name' => 'required|string|max:255',
            'org_unit_id' => 'nullable|exists:organizational_units,id',
            'department' => 'nullable|string|max:100',
            'position' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:20',
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,id',
            'is_active' => 'boolean',
        ]);

        $user->update($validated);

        // Update roles
        $user->syncRoles($validated['roles']);

        return redirect()
            ->route('users.show', $user)
            ->with('success', 'User updated successfully!');
    }

    /**
     * Remove the specified user
     */
    public function destroy(User $user)
    {
        // Prevent deletion of the last system administrator
        if ($user->hasRole('System Administrator')) {
            $adminCount = User::withRole('System Administrator')->count();
            if ($adminCount <= 1) {
                return back()
                    ->with('error', 'Cannot delete the last system administrator.');
            }
        }

        $user->delete();

        return redirect()
            ->route('users.index')
            ->with('success', 'User deleted successfully!');
    }

    /**
     * Toggle user active status
     */
    public function toggleStatus(User $user)
    {
        // Prevent deactivation of the last system administrator
        if ($user->hasRole('System Administrator') && $user->is_active) {
            $adminCount = User::withRole('System Administrator')->active()->count();
            if ($adminCount <= 1) {
                return back()
                    ->with('error', 'Cannot deactivate the last active system administrator.');
            }
        }

        if ($user->is_active) {
            $user->deactivate();
            $message = 'User deactivated successfully!';
        } else {
            $user->activate();
            $message = 'User activated successfully!';
        }

        return back()
            ->with('success', $message);
    }

    /**
     * Show user roles management page
     */
    public function roles(User $user)
    {
        $user->load('roles');
        $availableRoles = Role::orderBy('name')->get();

        return view('users.roles', compact('user', 'availableRoles'));
    }

    /**
     * Update user roles
     */
    public function updateRoles(Request $request, User $user)
    {
        $validated = $request->validate([
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,id',
        ]);

        // Prevent removal of system administrator role from last admin
        if ($user->hasRole('System Administrator')) {
            $adminCount = User::withRole('System Administrator')->count();
            if (!in_array(Role::where('name', 'System Administrator')->first()->id, $validated['roles']) && $adminCount <= 1) {
                return back()
                    ->with('error', 'Cannot remove system administrator role from the last administrator.');
            }
        }

        $user->syncRoles($validated['roles']);

        return back()
            ->with('success', 'User roles updated successfully!');
    }

    /**
     * Reset user password
     */
    public function resetPassword(Request $request, User $user)
    {
        $validated = $request->validate([
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()
            ->with('success', 'Password reset successfully!');
    }

    /**
     * Get user statistics (API)
     */
    public function statistics(Request $request)
    {
        $stats = [
            'total_users' => User::count(),
            'active_users' => User::active()->count(),
            'inactive_users' => User::inactive()->count(),
            'users_by_role' => Role::withCount('users')->get()->map(function ($role) {
                return [
                    'name' => $role->name,
                    'count' => $role->users_count,
                ];
            }),
            'users_by_org_unit' => OrganizationalUnit::withCount('users')
                ->where('is_active', true)
                ->get()
                ->map(function ($unit) {
                    return [
                        'name' => $unit->name,
                        'count' => $unit->users_count,
                    ];
                }),
            'recent_users' => User::with(['roles', 'organizationalUnit'])
                ->latest()
                ->take(5)
                ->get()
                ->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->full_name,
                        'email' => $user->email,
                        'roles' => $user->roles->pluck('name'),
                        'org_unit' => $user->organizationalUnit?->name,
                        'created_at' => $user->created_at->format('Y-m-d H:i:s'),
                    ];
                }),
        ];

        return response()->json($stats);
    }

    /**
     * Search users (API)
     */
    public function search(Request $request)
    {
        $search = $request->get('q');
        
        if (!$search) {
            return response()->json([]);
        }

        $users = User::search($search)
            ->with(['roles', 'organizationalUnit'])
            ->limit(10)
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->full_name,
                    'email' => $user->email,
                    'username' => $user->username,
                    'roles' => $user->roles->pluck('name'),
                    'org_unit' => $user->organizationalUnit?->name,
                    'is_active' => $user->is_active,
                ];
            });

        return response()->json($users);
    }

    /**
     * Get user details (API)
     */
    public function showApi(User $user)
    {
        $user->load(['roles', 'organizationalUnit', 'supervisor', 'supervisedUsers']);
        
        return response()->json([
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'full_name' => $user->full_name,
                'department' => $user->department,
                'position' => $user->position,
                'phone' => $user->phone,
                'is_active' => $user->is_active,
                'last_login' => $user->last_login_at,
                'created_at' => $user->created_at->format('Y-m-d H:i:s'),
                'organizational_unit' => $user->organizationalUnit?->name,
                'roles' => $user->roles->map(function ($role) {
                    return [
                        'id' => $role->id,
                        'name' => $role->name,
                        'description' => $role->description,
                        'permissions' => $role->permissions,
                    ];
                }),
                'supervisor' => $user->supervisor ? [
                    'id' => $user->supervisor->id,
                    'name' => $user->supervisor->full_name,
                ] : null,
                'supervised_users' => $user->supervisedUsers->map(function ($supervised) {
                    return [
                        'id' => $supervised->id,
                        'name' => $supervised->full_name,
                    ];
                }),
                'statistics' => $user->getStatistics(),
                'permissions' => $user->getAllPermissions(),
            ],
        ]);
    }
}
