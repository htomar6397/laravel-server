<?php

namespace App\Policies;

use App\Models\Expenditure;
use App\Models\User;

class ExpenditurePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('view_expenditures');
    }

    public function view(User $user, Expenditure $expenditure): bool
    {
        return $user->hasPermission('view_expenditure_details')
            || $user->hasPermission('view_expenditures');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('create_expenditures');
    }

    public function update(User $user, Expenditure $expenditure): bool
    {
        return $user->hasPermission('edit_expenditures');
    }

    public function delete(User $user, Expenditure $expenditure): bool
    {
        return $user->hasPermission('delete_expenditures');
    }

    public function approve(User $user, Expenditure $expenditure): bool
    {
        return $user->hasPermission('approve_expenditures');
    }

    public function verify(User $user, Expenditure $expenditure): bool
    {
        return $user->hasPermission('verify_expenditures');
    }
}
