<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

/**
 * Admin User Management Controller
 */
class AdminUserController extends Controller
{
    /**
     * Display a listing of users
     */
    public function index(Request $request)
    {
        $query = User::with(['roles', 'organizationalUnit'])
            ->withCount(['dataEntries', 'expenditures', 'photoCaptures']);

        // Search
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('full_name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%")
                  ->orWhere('username', 'like', "%{$request->search}%");
            });
        }

        // Filter by status
        if ($request->status !== null) {
            $query->where('is_active', $request->status === 'active');
        }

        // Filter by role
        if ($request->role) {
            $query->whereHas('roles', function($q) use ($request) {
                $q->where('name', $request->role);
            });
        }

        // Sort
        $sortField = $request->sort_field ?? 'created_at';
        $sortDirection = $request->sort_direction ?? 'desc';
        $query->orderBy($sortField, $sortDirection);

        $users = $query->paginate($request->per_page ?? 15);

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'filters' => $request->only(['search', 'status', 'role', 'sort_field', 'sort_direction']),
            'roles' => Role::all(),
        ]);
    }

    /**
     * Show the form for creating a new user
     */
    public function create()
    {
        return Inertia::render('Admin/Users/Create', [
            'roles' => Role::all(),
        ]);
    }

    /**
     * Store a newly created user
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required|string|max:255|unique:users',
            'email' => 'required|email|max:255|unique:users',
            'full_name' => 'required|string|max:255',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'department' => 'nullable|string|max:255',
            'position' => 'nullable|string|max:255',
            'org_unit_id' => 'nullable|exists:organizational_units,id',
            'is_active' => 'boolean',
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,id',
        ]);

        $user = User::create([
            'username' => $validated['username'],
            'email' => $validated['email'],
            'full_name' => $validated['full_name'],
            'password' => Hash::make($validated['password']),
            'phone' => $validated['phone'] ?? null,
            'department' => $validated['department'] ?? null,
            'position' => $validated['position'] ?? null,
            'org_unit_id' => $validated['org_unit_id'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        $user->roles()->sync($validated['roles']);

        return redirect()->route('admin.users.index')
            ->with('success', 'User created successfully');
    }

    /**
     * Display the specified user
     */
    public function show(User $user)
    {
        $user->load([
            'roles', 
            'organizationalUnit',
            'dataEntries' => fn($q) => $q->latest()->take(10),
            'expenditures' => fn($q) => $q->latest()->take(10),
            'photoCaptures' => fn($q) => $q->latest()->take(10),
        ]);

        return Inertia::render('Admin/Users/Show', [
            'user' => $user,
        ]);
    }

    /**
     * Show the form for editing the specified user
     */
    public function edit(User $user)
    {
        $user->load('roles');

        return Inertia::render('Admin/Users/Edit', [
            'user' => $user,
            'roles' => Role::all(),
        ]);
    }

    /**
     * Update the specified user
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'username' => ['required', 'string', 'max:255', Rule::unique('users')->ignore($user->id)],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'full_name' => 'required|string|max:255',
            'password' => 'nullable|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'department' => 'nullable|string|max:255',
            'position' => 'nullable|string|max:255',
            'org_unit_id' => 'nullable|exists:organizational_units,id',
            'is_active' => 'boolean',
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,id',
        ]);

        $updateData = [
            'username' => $validated['username'],
            'email' => $validated['email'],
            'full_name' => $validated['full_name'],
            'phone' => $validated['phone'] ?? null,
            'department' => $validated['department'] ?? null,
            'position' => $validated['position'] ?? null,
            'org_unit_id' => $validated['org_unit_id'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
        ];

        if (!empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);
        $user->roles()->sync($validated['roles']);

        return redirect()->route('admin.users.index')
            ->with('success', 'User updated successfully');
    }

    /**
     * Remove the specified user
     */
    public function destroy(User $user)
    {
        // Prevent deleting yourself
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account');
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted successfully');
    }

    /**
     * Toggle user active status
     */
    public function toggleStatus(User $user)
    {
        $user->update(['is_active' => !$user->is_active]);

        return back()->with('success', 'User status updated successfully');
    }
}
