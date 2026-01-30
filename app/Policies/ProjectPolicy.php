<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('view_projects');
    }

    public function view(User $user, Project $project): bool
    {
        return $user->hasPermission('view_project_details')
            || $user->hasPermission('view_projects');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('create_projects');
    }

    public function update(User $user, Project $project): bool
    {
        return $user->hasPermission('edit_projects');
    }

    public function delete(User $user, Project $project): bool
    {
        return $user->hasPermission('delete_projects');
    }
}
