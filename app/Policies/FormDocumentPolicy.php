<?php

namespace App\Policies;

use App\Models\FormDocument;
use App\Models\User;

class FormDocumentPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, FormDocument $formDocument): bool
    {
        return true;
    }

    /**
     * Determine whether the user can download the model.
     */
    public function download(User $user, FormDocument $formDocument): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ?FormDocument $formDocument = null): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ?FormDocument $formDocument = null): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, FormDocument $formDocument): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, FormDocument $formDocument): bool
    {
        return false;
    }
}
