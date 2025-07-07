<?php

namespace App\Policies;

use App\Enums\PracticeStatus;
use App\Models\Practice;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PracticePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('access practices');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Practice $practice): bool
    {
        return $user->hasPermissionTo('view practices');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create practices');
    }

    /**
     * Determine whether the user can import models.
     */
    public function importPractice(User $user): bool
    {
        return $user->hasPermissionTo('import practices');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Practice $practice): bool
    {
        return $user->hasPermissionTo('update practices') && $user->id === $practice->user_id;
    }

    /**
     * Determine whether the user can update status of the model.
     */
    public function updateStatus(User $user, Practice $practice): bool
    {
        return $user->hasPermissionTo('update practices') && $user->id === $practice->user_id
            && $practice->practice_status !== PracticeStatus::DISBURSED->value;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Practice $practice): bool
    {
        return $user->hasPermissionTo('delete practices') && $user->id === $practice->user_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Practice $practice): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Practice $practice): bool
    {
        return false;
    }
}
