<?php

namespace App\Policies;

use App\Enums\CustomerStatus;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CustomerPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user, CustomerStatus $status): bool
    {
        // If the status is CUSTOMER, check if the user has permission to access customers
        if ($status === CustomerStatus::CUSTOMER) {
            return $user->hasPermissionTo('access customers');
        }

        // If the status is LEAD, check if the user has permission to access leads
        if ($status === CustomerStatus::LEAD) {
            return $user->hasPermissionTo('access leads');
        }

        return false;  // Default to false if no conditions are met
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Customer $customer): bool
    {
        // If the customer is a customer, check if the user has permission to view customers
        if ($customer->isCustomer()) {
            return $user->hasPermissionTo('view customers');
        }

        // If the customer is a lead, check if the user has permission to view leads
        if ($customer->isLead()) {
            return $user->hasPermissionTo('view leads');
        }

        return false;  // Default to false if no conditions are met
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user, CustomerStatus $status): bool
    {
        // If the status is CUSTOMER, check if the user has permission to create customers
        if ($status === CustomerStatus::CUSTOMER) {
            return $user->hasPermissionTo('create customers');
        }

        // If the status is LEAD, check if the user has permission to create leads
        if ($status === CustomerStatus::LEAD) {
            return $user->hasPermissionTo('create leads');
        }

        return false;  // Default to false if no conditions are met
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Customer $customer): bool
    {
        // If the customer is a customer, check if the user has permission to update customers
        if ($customer->isCustomer()) {
            return $user->hasPermissionTo('update customers') && $user->id === $customer->user_id;
        }

        // If the customer is a lead, check if the user has permission to update leads
        if ($customer->isLead()) {
            return $user->hasPermissionTo('update leads') && $user->id === $customer->user_id;
        }

        return false;  // Default to false if no conditions are met
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Customer $customer): bool
    {
        // If the customer is a customer, check if the user has permission to delete customers
        if ($customer->isCustomer()) {
            return $user->hasPermissionTo('delete customers') && $user->id === $customer->user_id;
        }

        // If the customer is a lead, check if the user has permission to delete leads
        if ($customer->isLead()) {
            return $user->hasPermissionTo('delete leads') && $user->id === $customer->user_id;
        }

        return false;  // Default to false if no conditions are met
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Customer $customer): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Customer $customer): bool
    {
        return false;
    }
}
