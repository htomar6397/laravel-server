<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OrganizationalUnit;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminOrganizationalUnitController extends Controller
{
    /**
     * Display a listing of organizational units
     */
    public function index(Request $request)
    {
        $query = OrganizationalUnit::with('parent');

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by level
        if ($request->filled('level')) {
            $query->where('level', $request->level);
        }

        // Filter by parent
        if ($request->filled('parent')) {
            $query->where('parent_id', $request->parent);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $units = $query->latest()->paginate(15)->withQueryString();

        return Inertia::render('Admin/OrganizationalUnits/Index', [
            'units' => $units,
            'filters' => $request->only(['search', 'level', 'parent', 'status']),
            'parents' => OrganizationalUnit::select('id', 'name', 'level')->orderBy('name')->get(),
        ]);
    }

    /**
     * Show the form for creating a new organizational unit
     */
    public function create()
    {
        return Inertia::render('Admin/OrganizationalUnits/Create', [
            'parents' => OrganizationalUnit::select('id', 'name', 'level')->orderBy('name')->get(),
        ]);
    }

    /**
     * Store a newly created organizational unit
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:organizational_units,code',
            'name' => 'required|string|max:255',
            'level' => 'required|in:municipality,ward,mtaa,village',
            'parent_id' => 'nullable|exists:organizational_units,id',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'population' => 'nullable|integer|min:0',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        OrganizationalUnit::create($validated);

        return redirect()->route('admin.organizational-units.index')
            ->with('success', 'Organizational unit created successfully.');
    }

    /**
     * Show the form for editing the specified organizational unit
     */
    public function edit(OrganizationalUnit $organizationalUnit)
    {
        return Inertia::render('Admin/OrganizationalUnits/Edit', [
            'unit' => $organizationalUnit,
            'parents' => OrganizationalUnit::where('id', '!=', $organizationalUnit->id)
                ->select('id', 'name', 'level')
                ->orderBy('name')
                ->get(),
        ]);
    }

    /**
     * Update the specified organizational unit
     */
    public function update(Request $request, OrganizationalUnit $organizationalUnit)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:organizational_units,code,' . $organizationalUnit->id,
            'name' => 'required|string|max:255',
            'level' => 'required|in:municipality,ward,mtaa,village',
            'parent_id' => 'nullable|exists:organizational_units,id',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'population' => 'nullable|integer|min:0',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $organizationalUnit->update($validated);

        return redirect()->route('admin.organizational-units.index')
            ->with('success', 'Organizational unit updated successfully.');
    }

    /**
     * Remove the specified organizational unit
     */
    public function destroy(OrganizationalUnit $organizationalUnit)
    {
        // Check if has children
        if ($organizationalUnit->children()->count() > 0) {
            return redirect()->route('admin.organizational-units.index')
                ->with('error', 'Cannot delete organizational unit with children.');
        }

        $organizationalUnit->delete();

        return redirect()->route('admin.organizational-units.index')
            ->with('success', 'Organizational unit deleted successfully.');
    }
}
