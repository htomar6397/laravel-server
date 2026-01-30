<?php

namespace App\Http\Controllers;

use App\Models\{PhotoCapture, Project, User};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * PhotoCaptureController
 * 
 * Handles photo capture management operations for the KMC M&E System
 */
class PhotoCaptureController extends Controller
{
    /**
     * Display a listing of photo captures
     */
    public function index(Request $request)
    {
        $query = PhotoCapture::with(['project', 'capturer'])->active();

        // Apply filters
        if ($request->has('project') && $request->project != '') {
            $query->byProject($request->project);
        }

        if ($request->has('user') && $request->user != '') {
            $query->byUser($request->user);
        }

        if ($request->has('date_from') && $request->date_from != '') {
            $query->where('captured_at', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to != '') {
            $query->where('captured_at', '<=', $request->date_to);
        }

        if ($request->has('search') && $request->search != '') {
            $query->search($request->search);
        }

        // Sort
        $sortBy = $request->get('sort_by', 'captured_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Paginate
        $photos = $query->paginate(20);

        // Get filter options
        $projects = Project::orderBy('name')->get();
        $users = User::orderBy('full_name')->get();

        // Calculate statistics
        $stats = [
            'total' => PhotoCapture::active()->count(),
            'this_month' => PhotoCapture::active()->whereMonth('captured_at', now()->month)->count(),
            'with_gps' => PhotoCapture::active()->withGPS()->count(),
            'total_size' => PhotoCapture::active()->sum('file_size'),
        ];

        return view('photos.index', compact(
            'photos',
            'projects',
            'users',
            'stats'
        ));
    }

    /**
     * Show the form for creating a new photo capture
     */
    public function create()
    {
        $projects = Project::orderBy('name')->get();

        return view('photos.create', compact('projects'));
    }

    /**
     * Store a newly created photo capture
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'nullable|exists:projects,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'photo' => 'required|file|image|max:10240', // 10MB max
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'accuracy' => 'nullable|numeric|min:0',
            'captured_at' => 'nullable|date',
        ]);

        // Handle file upload
        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $filename = uniqid() . '_' . Str::slug($validated['title']) . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('photos', $filename, 'public');

            // Get image dimensions
            $imageInfo = getimagesize(Storage::disk('public')->path($path));
            $width = $imageInfo[0] ?? null;
            $height = $imageInfo[1] ?? null;

            $validated['file_path'] = $path;
            $validated['original_filename'] = $file->getClientOriginalName();
            $validated['mime_type'] = $file->getMimeType();
            $validated['file_size'] = $file->getSize();
            $validated['width'] = $width;
            $validated['height'] = $height;
            $validated['captured_by'] = Auth::id();
        }

        $photo = PhotoCapture::create($validated);

        // Generate thumbnail
        if ($photo->is_image) {
            $photo->generateThumbnail();
        }

        return redirect()
            ->route('photos.show', $photo)
            ->with('success', 'Photo captured successfully!');
    }

    /**
     * Display the specified photo capture
     */
    public function show(PhotoCapture $photo)
    {
        $photo->load(['project', 'capturer', 'fileAttachments.uploader']);

        return view('photos.show', compact('photo'));
    }

    /**
     * Show the form for editing the specified photo capture
     */
    public function edit(PhotoCapture $photo)
    {
        if (!$photo->canUserPerformAction(Auth::user(), 'edit')) {
            abort(403, 'Unauthorized action.');
        }

        $projects = Project::orderBy('name')->get();

        return view('photos.edit', compact('photo', 'projects'));
    }

    /**
     * Update the specified photo capture
     */
    public function update(Request $request, PhotoCapture $photo)
    {
        if (!$photo->canUserPerformAction(Auth::user(), 'edit')) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'project_id' => 'nullable|exists:projects,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'accuracy' => 'nullable|numeric|min:0',
            'captured_at' => 'nullable|date',
        ]);

        $photo->update($validated);

        return redirect()
            ->route('photos.show', $photo)
            ->with('success', 'Photo updated successfully!');
    }

    /**
     * Remove the specified photo capture
     */
    public function destroy(PhotoCapture $photo)
    {
        if (!$photo->canUserPerformAction(Auth::user(), 'delete')) {
            abort(403, 'Unauthorized action.');
        }

        $photo->softDeletePhoto();

        return redirect()
            ->route('photos.index')
            ->with('success', 'Photo deleted successfully!');
    }

    /**
     * Restore a deleted photo
     */
    public function restore(PhotoCapture $photo)
    {
        if (!Auth::user()->hasPermission('delete_photos')) {
            abort(403, 'Unauthorized action.');
        }

        $photo->restorePhoto();

        return back()
            ->with('success', 'Photo restored successfully!');
    }

    /**
     * Download photo
     */
    public function download(PhotoCapture $photo)
    {
        if (!$photo->canUserDownload(Auth::user())) {
            abort(403, 'Unauthorized action.');
        }

        return Storage::disk('public')->download($photo->file_path, $photo->original_filename);
    }

    /**
     * Upload photo via API (for mobile app)
     */
    public function uploadApi(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'nullable|exists:projects,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'photo' => 'required|file|image|max:10240',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'accuracy' => 'nullable|numeric|min:0',
            'captured_at' => 'nullable|date',
        ]);

        // Handle file upload
        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $filename = uniqid() . '_' . Str::slug($validated['title']) . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('photos', $filename, 'public');

            // Get image dimensions
            $imageInfo = getimagesize(Storage::disk('public')->path($path));
            $width = $imageInfo[0] ?? null;
            $height = $imageInfo[1] ?? null;

            $validated['file_path'] = $path;
            $validated['original_filename'] = $file->getClientOriginalName();
            $validated['mime_type'] = $file->getMimeType();
            $validated['file_size'] = $file->getSize();
            $validated['width'] = $width;
            $validated['height'] = $height;
            $validated['captured_by'] = Auth::id();
        }

        $photo = PhotoCapture::create($validated);

        // Generate thumbnail
        if ($photo->is_image) {
            $photo->generateThumbnail();
        }

        return response()->json([
            'success' => true,
            'photo' => $photo->getApiMetadata(),
        ]);
    }

    /**
     * Get photo statistics (API)
     */
    public function statistics(Request $request)
    {
        $query = PhotoCapture::active();

        // Apply date range filter
        if ($request->has('start_date') && $request->start_date) {
            $query->where('captured_at', '>=', $request->start_date);
        }
        if ($request->has('end_date') && $request->end_date) {
            $query->where('captured_at', '<=', $request->end_date);
        }

        $stats = [
            'total_photos' => $query->count(),
            'total_size' => $query->sum('file_size'),
            'with_gps' => $query->withGPS()->count(),
            'by_project' => $query->with('project')
                ->get()
                ->groupBy('project.name')
                ->map->count(),
            'monthly_trend' => $query->selectRaw('DATE_FORMAT(captured_at, "%Y-%m") as month, COUNT(*) as count')
                ->groupBy('month')
                ->orderBy('month')
                ->get(),
            'recent_photos' => $query->with(['project', 'capturer'])
                ->latest()
                ->take(5)
                ->get()
                ->map(function ($photo) {
                    return [
                        'id' => $photo->id,
                        'title' => $photo->title,
                        'thumbnail_url' => $photo->thumbnail_url,
                        'project' => $photo->project?->name,
                        'captured_by' => $photo->capturer->full_name,
                        'captured_at' => $photo->captured_at->format('Y-m-d H:i:s'),
                    ];
                }),
        ];

        return response()->json($stats);
    }

    /**
     * Search photos (API)
     */
    public function search(Request $request)
    {
        $search = $request->get('q');
        
        if (!$search) {
            return response()->json([]);
        }

        $photos = PhotoCapture::active()
            ->search($search)
            ->with(['project', 'capturer'])
            ->limit(10)
            ->get()
            ->map(function ($photo) {
                return [
                    'id' => $photo->id,
                    'title' => $photo->title,
                    'thumbnail_url' => $photo->thumbnail_url,
                    'project' => $photo->project?->name,
                    'captured_by' => $photo->capturer->full_name,
                    'captured_at' => $photo->captured_at->format('Y-m-d H:i:s'),
                ];
            });

        return response()->json($photos);
    }

    /**
     * Get photo details (API)
     */
    public function showApi(PhotoCapture $photo)
    {
        if (!$photo->canUserDownload(Auth::user())) {
            abort(403, 'Unauthorized action.');
        }

        return response()->json([
            'photo' => $photo->getApiMetadata(),
        ]);
    }

    /**
     * Get photos by project (API)
     */
    public function byProject(Project $project, Request $request)
    {
        $query = $project->photoCaptures()->active();

        // Apply pagination
        $limit = $request->get('limit', 20);
        $offset = $request->get('offset', 0);

        $photos = $query->latest()
            ->skip($offset)
            ->take($limit)
            ->get()
            ->map(function ($photo) {
                return $photo->getApiMetadata();
            });

        return response()->json([
            'photos' => $photos,
            'total' => $project->photoCaptures()->active()->count(),
        ]);
    }

    /**
     * Get photos with GPS coordinates (API)
     */
    public function withGPS(Request $request)
    {
        $query = PhotoCapture::active()->withGPS();

        // Apply bounds filter if provided
        if ($request->has('bounds')) {
            $bounds = $request->get('bounds');
            $query->whereBetween('latitude', [$bounds['south'], $bounds['north']])
                  ->whereBetween('longitude', [$bounds['west'], $bounds['east']]);
        }

        $photos = $query->with(['project', 'capturer'])
            ->get()
            ->map(function ($photo) {
                return [
                    'id' => $photo->id,
                    'title' => $photo->title,
                    'description' => $photo->description,
                    'thumbnail_url' => $photo->thumbnail_url,
                    'image_url' => $photo->image_url,
                    'latitude' => $photo->latitude,
                    'longitude' => $photo->longitude,
                    'accuracy' => $photo->accuracy,
                    'project' => $photo->project?->name,
                    'captured_by' => $photo->capturer->full_name,
                    'captured_at' => $photo->captured_at->toISOString(),
                ];
            });

        return response()->json($photos);
    }

    /**
     * Bulk delete photos
     */
    public function bulkDelete(Request $request)
    {
        if (!Auth::user()->hasPermission('delete_photos')) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'photo_ids' => 'required|array',
            'photo_ids.*' => 'exists:photo_captures,id',
        ]);

        $deletedCount = 0;
        foreach ($validated['photo_ids'] as $photoId) {
            $photo = PhotoCapture::findOrFail($photoId);
            if ($photo->canUserPerformAction(Auth::user(), 'delete')) {
                $photo->softDeletePhoto();
                $deletedCount++;
            }
        }

        return back()
            ->with('success', "Successfully deleted {$deletedCount} photos!");
    }
}
