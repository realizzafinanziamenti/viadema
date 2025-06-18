<?php

namespace App\Policies;

use App\Models\ProductSubtype;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ProductSubtypePolicy
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
    public function view(User $user, ProductSubtype $productSubtype): bool
    {
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create product subtypes');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ProductSubtype $productSubtype): bool
    {
        return $user->hasPermissionTo('update product subtypes');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ProductSubtype $productSubtype): bool
    {
        return $user->hasPermissionTo('delete product subtypes');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ProductSubtype $productSubtype): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ProductSubtype $productSubtype): bool
    {
        return false;
    }
}
