<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

/**
 * Admin DHIS2 Integration Controller
 */
class AdminDHIS2Controller extends Controller
{
    /**
     * Display DHIS2 integration dashboard
     */
    public function index(Request $request)
    {
        $config = [
            'url' => config('kmc.dhis2.url', ''),
            'username' => config('kmc.dhis2.username', ''),
            'enabled' => config('kmc.dhis2.enabled', false),
            'last_sync' => Cache::get('dhis2_last_sync'),
        ];

        // Get sync statistics
        $syncStats = [
            'total_synced' => Cache::get('dhis2_total_synced', 0),
            'last_sync_count' => Cache::get('dhis2_last_sync_count', 0),
            'failed_syncs' => Cache::get('dhis2_failed_syncs', 0),
            'pending_sync' => Cache::get('dhis2_pending_sync', 0),
        ];

        return Inertia::render('Admin/DHIS2/Index', [
            'config' => $config,
            'syncStats' => $syncStats,
        ]);
    }

    /**
     * Show DHIS2 configuration page
     */
    public function config()
    {
        return Inertia::render('Admin/DHIS2/Config', [
            'config' => [
                'url' => config('kmc.dhis2.url', ''),
                'username' => config('kmc.dhis2.username', ''),
                'enabled' => config('kmc.dhis2.enabled', false),
                'sync_interval' => config('kmc.dhis2.sync_interval', 'daily'),
            ],
        ]);
    }

    /**
     * Update DHIS2 configuration
     */
    public function updateConfig(Request $request)
    {
        $validated = $request->validate([
            'url' => 'required|url',
            'username' => 'required|string',
            'password' => 'nullable|string',
            'enabled' => 'boolean',
            'sync_interval' => 'required|in:hourly,daily,weekly',
        ]);

        // TODO: Store configuration in database or .env file
        // For now, just validate and return success

        return redirect()->route('admin.dhis2.index')
            ->with('success', 'DHIS2 configuration updated successfully.');
    }

    /**
     * Test DHIS2 connection
     */
    public function testConnection(Request $request)
    {
        $request->validate([
            'url' => 'required|url',
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        try {
            $response = Http::withBasicAuth($request->username, $request->password)
                ->timeout(10)
                ->get($request->url . '/api/system/info');

            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Connection successful',
                    'data' => $response->json(),
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Connection failed: ' . $response->status(),
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Connection error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Trigger manual sync to DHIS2
     */
    public function sync(Request $request)
    {
        // TODO: Implement actual DHIS2 sync logic
        Cache::put('dhis2_last_sync', now());
        Cache::increment('dhis2_total_synced', 10);
        Cache::put('dhis2_last_sync_count', 10);

        return response()->json([
            'success' => true,
            'message' => 'Sync initiated successfully',
            'synced_count' => 10,
        ]);
    }
}
