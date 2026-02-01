<?php

namespace App\Http\Controllers;

use App\Models\{Project, DataEntry, Expenditure, PhotoCapture};
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class SyncController extends Controller
{
    use ApiResponseTrait;
    /**
     * Get sync status for all entities
     */
    public function status(Request $request)
    {
        try {
            $user = $request->user();
            
            // Get last sync timestamps for different entities
            $status = [
                'user_id' => $user->id,
                'server_time' => now()->toISOString(),
                'last_sync' => [
                    'projects' => Project::where('updated_at', '>=', now()->subDays(30))
                        ->max('updated_at'),
                    'data_entries' => DataEntry::where('updated_at', '>=', now()->subDays(30))
                        ->max('updated_at'),
                    'expenditures' => Expenditure::where('updated_at', '>=', now()->subDays(30))
                        ->max('updated_at'),
                    'photos' => PhotoCapture::where('updated_at', '>=', now()->subDays(30))
                        ->max('updated_at'),
                ],
                'pending_counts' => [
                    'projects' => Project::where('updated_at', '>', $request->get('last_sync', now()->subDays(7)))
                        ->count(),
                    'data_entries' => DataEntry::where('updated_at', '>', $request->get('last_sync', now()->subDays(7)))
                        ->count(),
                    'expenditures' => Expenditure::where('updated_at', '>', $request->get('last_sync', now()->subDays(7)))
                        ->count(),
                    'photos' => PhotoCapture::where('updated_at', '>', $request->get('last_sync', now()->subDays(7)))
                        ->count(),
                ],
            ];

            return $this->successResponse($status, 'Sync status retrieved successfully');

        } catch (\Exception $e) {
            \Log::error('Sync status error: ' . $e->getMessage());
            return $this->errorResponse('Failed to get sync status', $e->getMessage(), 500);
        }
    }

    /**
     * Bulk sync - upload/download multiple records
     */
    public function bulkSync(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'last_sync' => 'nullable|date',
            'data_entries' => 'nullable|array',
            'expenditures' => 'nullable|array',
            'photos' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', $validator->errors(), 422);
        }

        DB::beginTransaction();
        
        try {
            $user = $request->user();
            $lastSync = $request->get('last_sync', now()->subDays(7));
            $results = [
                'uploaded' => [],
                'downloaded' => [],
                'conflicts' => [],
            ];

            // Process uploaded data entries
            if ($request->has('data_entries')) {
                $results['uploaded']['data_entries'] = $this->syncDataEntries(
                    $request->data_entries,
                    $user
                );
            }

            // Process uploaded expenditures
            if ($request->has('expenditures')) {
                $results['uploaded']['expenditures'] = $this->syncExpenditures(
                    $request->expenditures,
                    $user
                );
            }

            // Process uploaded photos
            if ($request->has('photos')) {
                $results['uploaded']['photos'] = $this->syncPhotos(
                    $request->photos,
                    $user
                );
            }

            // Get updated records from server
            $results['downloaded'] = [
                'projects' => Project::where('updated_at', '>', $lastSync)
                    ->with(['theme', 'organizationalUnit', 'indicators'])
                    ->get(),
                'data_entries' => DataEntry::where('updated_at', '>', $lastSync)
                    ->with(['project', 'indicator', 'user'])
                    ->get(),
                'expenditures' => Expenditure::where('updated_at', '>', $lastSync)
                    ->with(['project', 'user'])
                    ->get(),
                'photos' => PhotoCapture::where('updated_at', '>', $lastSync)
                    ->with(['project', 'user'])
                    ->get(),
            ];

            DB::commit();

            return $this->successResponse([
                ...$results,
                'sync_timestamp' => now()->toISOString(),
            ], 'Bulk sync completed successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Bulk sync error: ' . $e->getMessage());
            
            return $this->errorResponse('Bulk sync failed', $e->getMessage(), 500);
        }
    }

    /**
     * Sync data entries
     */
    private function syncDataEntries(array $entries, $user)
    {
        $results = ['created' => 0, 'updated' => 0, 'failed' => 0];

        foreach ($entries as $entryData) {
            try {
                // Check if entry exists by temp_id or id
                $dataEntry = null;
                if (isset($entryData['id'])) {
                    $dataEntry = DataEntry::find($entryData['id']);
                }

                if ($dataEntry) {
                    // Update existing
                    $dataEntry->update($entryData);
                    $results['updated']++;
                } else {
                    // Create new
                    $entryData['user_id'] = $user->id;
                    DataEntry::create($entryData);
                    $results['created']++;
                }
            } catch (\Exception $e) {
                \Log::error('Data entry sync error: ' . $e->getMessage());
                $results['failed']++;
            }
        }

        return $results;
    }

    /**
     * Sync expenditures
     */
    private function syncExpenditures(array $expenditures, $user)
    {
        $results = ['created' => 0, 'updated' => 0, 'failed' => 0];

        foreach ($expenditures as $expenditureData) {
            try {
                $expenditure = null;
                if (isset($expenditureData['id'])) {
                    $expenditure = Expenditure::find($expenditureData['id']);
                }

                if ($expenditure) {
                    $expenditure->update($expenditureData);
                    $results['updated']++;
                } else {
                    $expenditureData['user_id'] = $user->id;
                    Expenditure::create($expenditureData);
                    $results['created']++;
                }
            } catch (\Exception $e) {
                \Log::error('Expenditure sync error: ' . $e->getMessage());
                $results['failed']++;
            }
        }

        return $results;
    }

    /**
     * Sync photos
     */
    private function syncPhotos(array $photos, $user)
    {
        $results = ['created' => 0, 'updated' => 0, 'failed' => 0];

        foreach ($photos as $photoData) {
            try {
                $photo = null;
                if (isset($photoData['id'])) {
                    $photo = PhotoCapture::find($photoData['id']);
                }

                if ($photo) {
                    $photo->update($photoData);
                    $results['updated']++;
                } else {
                    $photoData['user_id'] = $user->id;
                    PhotoCapture::create($photoData);
                    $results['created']++;
                }
            } catch (\Exception $e) {
                \Log::error('Photo sync error: ' . $e->getMessage());
                $results['failed']++;
            }
        }

        return $results;
    }
}
