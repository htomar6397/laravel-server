<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PhotoCapture;
use App\Models\Project;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

/**
 * Admin Photo Gallery Controller
 */
class AdminPhotoController extends Controller
{
    /**
     * Display photo gallery
     */
    public function index(Request $request)
    {
        $query = PhotoCapture::with(['project', 'capturedBy']);

        // Search
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('title', 'like', "%{$request->search}%")
                  ->orWhere('description', 'like', "%{$request->search}%");
            });
        }

        // Filter by project
        if ($request->project_id) {
            $query->where('project_id', $request->project_id);
        }

        // Filter by date range
        if ($request->date_from) {
            $query->whereDate('captured_at', '>=', $request->date_from);
        }
        if ($request->date_to) {
            $query->whereDate('captured_at', '<=', $request->date_to);
        }

        // Filter by geotagged
        if ($request->has_gps) {
            $query->whereNotNull('latitude')->whereNotNull('longitude');
        }

        // Sort
        $sortField = $request->sort_field ?? 'captured_at';
        $sortDirection = $request->sort_direction ?? 'desc';
        $query->orderBy($sortField, $sortDirection);

        $view = $request->view ?? 'grid'; // grid or map
        $perPage = $view === 'grid' ? 24 : 100;

        $photos = $query->paginate($request->per_page ?? $perPage);

        // For map view, get all geotagged photos
        $geotaggedPhotos = [];
        if ($view === 'map') {
            $geotaggedPhotos = PhotoCapture::with(['project', 'capturedBy'])
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->get(['id', 'title', 'latitude', 'longitude', 'project_id', 'thumbnail_path', 'captured_at']);
        }

        return Inertia::render('Admin/Photos/Index', [
            'photos' => $photos,
            'geotaggedPhotos' => $geotaggedPhotos,
            'filters' => $request->only(['search', 'project_id', 'date_from', 'date_to', 'has_gps', 'view']),
            'projects' => Project::all(['id', 'name', 'code']),
            'view' => $view,
        ]);
    }

    /**
     * Display the specified photo
     */
    public function show(PhotoCapture $photo)
    {
        $photo->load(['project', 'capturedBy']);

        // Get nearby photos (within 1km)
        $nearbyPhotos = [];
        if ($photo->latitude && $photo->longitude) {
            $nearbyPhotos = PhotoCapture::selectRaw(
                    '*,
                    (6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) AS distance',
                    [$photo->latitude, $photo->longitude, $photo->latitude]
                )
                ->where('id', '!=', $photo->id)
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->having('distance', '<', 1)
                ->orderBy('distance')
                ->take(6)
                ->get();
        }

        return Inertia::render('Admin/Photos/Show', [
            'photo' => $photo,
            'nearbyPhotos' => $nearbyPhotos,
        ]);
    }

    /**
     * Download photo
     */
    public function download(PhotoCapture $photo)
    {
        $filePath = storage_path('app/public/' . $photo->file_path);

        if (!file_exists($filePath)) {
            abort(404, 'Photo file not found');
        }

        return response()->download($filePath, $photo->original_filename);
    }

    /**
     * Delete photo
     */
    public function destroy(PhotoCapture $photo)
    {
        // Delete files from storage
        if ($photo->file_path && \Storage::exists($photo->file_path)) {
            \Storage::delete($photo->file_path);
        }
        if ($photo->thumbnail_path && \Storage::exists($photo->thumbnail_path)) {
            \Storage::delete($photo->thumbnail_path);
        }

        $photo->delete();

        return redirect()->route('admin.photos.index')
            ->with('success', 'Photo deleted successfully');
    }

    /**
     * Get photo statistics
     */
    public function stats()
    {
        $stats = [
            'total_photos' => PhotoCapture::count(),
            'geotagged_photos' => PhotoCapture::whereNotNull('latitude')->whereNotNull('longitude')->count(),
            'photos_today' => PhotoCapture::whereDate('created_at', Carbon::today())->count(),
            'photos_this_week' => PhotoCapture::whereBetween('created_at', [
                Carbon::now()->startOfWeek(),
                Carbon::now()->endOfWeek()
            ])->count(),
            'photos_this_month' => PhotoCapture::whereYear('created_at', Carbon::now()->year)
                ->whereMonth('created_at', Carbon::now()->month)
                ->count(),
            'total_storage' => PhotoCapture::sum('file_size'),
        ];

        return response()->json($stats);
    }
}
