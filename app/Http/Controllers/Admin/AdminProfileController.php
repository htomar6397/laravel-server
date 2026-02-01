<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

/**
 * Admin Profile Controller
 */
class AdminProfileController extends Controller
{
    /**
     * Display the user profile page
     */
    public function index(Request $request)
    {
        $user = $request->user()->load(['roles', 'organizationalUnit']);

        return Inertia::render('Admin/Profile', [
            'user' => $user,
        ]);
    }

    /**
     * Update user profile
     */
    public function update(Request $request)
    {
        $request->validate([
            'full_name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'department' => 'nullable|string|max:255',
            'position' => 'nullable|string|max:255',
        ]);

        $user = $request->user();
        $user->update($request->only(['full_name', 'phone', 'department', 'position']));

        return redirect()->back()->with('success', 'Profile updated successfully.');
    }
}