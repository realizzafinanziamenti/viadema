<?php

namespace App\Policies;

use App\Models\CustomerType;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CustomerTypePolicy
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
    public function view(User $user, CustomerType $customerType): bool
    {
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create customer types');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, CustomerType $customerType): bool
    {
        return $user->hasPermissionTo('update customer types');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, CustomerType $customerType): bool
    {
        return $user->hasPermissionTo('delete customer types');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, CustomerType $customerType): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, CustomerType $customerType): bool
    {
        return false;
    }
}
