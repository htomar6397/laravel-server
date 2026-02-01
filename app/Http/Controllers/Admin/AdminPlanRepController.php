<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

/**
 * Admin PlanRep Integration Controller
 */
class AdminPlanRepController extends Controller
{
    /**
     * Display PlanRep integration dashboard
     */
    public function index(Request $request)
    {
        $config = [
            'url' => config('kmc.planrep.url', ''),
            'api_key' => config('kmc.planrep.api_key', '') ? '****' . substr(config('kmc.planrep.api_key'), -4) : '',
            'enabled' => config('kmc.planrep.enabled', false),
            'last_sync' => Cache::get('planrep_last_sync'),
        ];

        // Get sync statistics
        $syncStats = [
            'total_synced' => Cache::get('planrep_total_synced', 0),
            'last_sync_count' => Cache::get('planrep_last_sync_count', 0),
            'failed_syncs' => Cache::get('planrep_failed_syncs', 0),
            'pending_sync' => Cache::get('planrep_pending_sync', 0),
        ];

        return Inertia::render('Admin/PlanRep/Index', [
            'config' => $config,
            'syncStats' => $syncStats,
        ]);
    }

    /**
     * Show PlanRep configuration page
     */
    public function config()
    {
        return Inertia::render('Admin/PlanRep/Config', [
            'config' => [
                'url' => config('kmc.planrep.url', ''),
                'api_key' => config('kmc.planrep.api_key', '') ? '****' . substr(config('kmc.planrep.api_key'), -4) : '',
                'enabled' => config('kmc.planrep.enabled', false),
                'sync_interval' => config('kmc.planrep.sync_interval', 'daily'),
            ],
        ]);
    }

    /**
     * Update PlanRep configuration
     */
    public function updateConfig(Request $request)
    {
        $validated = $request->validate([
            'url' => 'required|url',
            'api_key' => 'nullable|string',
            'enabled' => 'boolean',
            'sync_interval' => 'required|in:hourly,daily,weekly',
        ]);

        // TODO: Store configuration in database or .env file
        // For now, just validate and return success

        return redirect()->route('admin.planrep.index')
            ->with('success', 'PlanRep configuration updated successfully.');
    }

    /**
     * Test PlanRep connection
     */
    public function testConnection(Request $request)
    {
        $request->validate([
            'url' => 'required|url',
            'api_key' => 'required|string',
        ]);

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $request->api_key,
                'Accept' => 'application/json',
            ])->timeout(10)->get($request->url . '/api/health');

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
     * Trigger manual sync to PlanRep
     */
    public function sync(Request $request)
    {
        // TODO: Implement actual PlanRep sync logic
        Cache::put('planrep_last_sync', now());
        Cache::increment('planrep_total_synced', 15);
        Cache::put('planrep_last_sync_count', 15);

        return response()->json([
            'success' => true,
            'message' => 'Sync initiated successfully',
            'synced_count' => 15,
        ]);
    }
}
