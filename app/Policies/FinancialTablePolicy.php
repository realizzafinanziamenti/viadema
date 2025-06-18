<?php

namespace App\Policies;

use App\Models\FinancialTable;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class FinancialTablePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, FinancialTable $financialTable): bool
    {
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create financial tables');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, FinancialTable $financialTable): bool
    {
        return $user->hasPermissionTo('update financial tables');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, FinancialTable $financialTable): bool
    {
        return $user->hasPermissionTo('delete financial tables');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, FinancialTable $financialTable): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, FinancialTable $financialTable): bool
    {
        return false;
    }
}
