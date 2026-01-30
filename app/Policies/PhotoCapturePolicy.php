<?php

namespace App\Policies;

use App\Models\PhotoCapture;
use App\Models\User;

class PhotoCapturePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('view_photos');
    }

    public function view(User $user, PhotoCapture $photo): bool
    {
        return $user->hasPermission('view_photos');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('capture_photos');
    }

    public function update(User $user, PhotoCapture $photo): bool
    {
        return $user->hasPermission('edit_photos')
            || $user->hasPermission('capture_photos');
    }

    public function delete(User $user, PhotoCapture $photo): bool
    {
        return $user->hasPermission('delete_photos');
    }
}
