<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

/**
 * Admin Settings Controller
 */
class AdminSettingsController extends Controller
{
    /**
     * Display the settings page
     */
    public function index()
    {
        return Inertia::render('Admin/Settings/Index', [
            //
        ]);
    }

    /**
     * Update settings
     */
    public function update(Request $request)
    {
        // Handle settings update
        return redirect()->route('admin.settings')
            ->with('success', 'Settings updated successfully');
    }
}
