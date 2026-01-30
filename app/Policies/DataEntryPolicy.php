<?php

namespace App\Policies;

use App\Models\DataEntry;
use App\Models\User;

class DataEntryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('view_data_history')
            || $user->hasPermission('enter_data');
    }

    public function view(User $user, DataEntry $dataEntry): bool
    {
        return $user->hasPermission('view_data_history')
            || $user->hasPermission('enter_data');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('enter_data');
    }

    public function update(User $user, DataEntry $dataEntry): bool
    {
        return $user->hasPermission('edit_data');
    }

    public function delete(User $user, DataEntry $dataEntry): bool
    {
        return $user->hasPermission('delete_data');
    }

    public function verify(User $user, DataEntry $dataEntry): bool
    {
        return $user->hasPermission('verify_data');
    }
}
