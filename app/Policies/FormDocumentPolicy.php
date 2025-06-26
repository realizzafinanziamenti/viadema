<?php

namespace App\Policies;

use App\Models\FormDocument;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class FormDocumentPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('access form documents');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, FormDocument $formDocument): bool
    {
        return $user->hasPermissionTo('view form documents');
    }

    /**
     * Determine whether the user can download the model.
     */
    public function download(User $user, FormDocument $formDocument): bool
    {
        return $user->hasPermissionTo('download form documents');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create form documents');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, FormDocument $formDocument): bool
    {
        return $user->hasPermissionTo('update form documents');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, FormDocument $formDocument): bool
    {
        return $user->hasPermissionTo('delete form documents');
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
