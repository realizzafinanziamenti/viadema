<?php

namespace App\Policies;

use App\Enums\PracticeStatus;
use App\Models\Practice;
use App\Models\User;

class PracticePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('access practices');
    }

    public function view(User $user, Practice $practice): bool
    {
        if (! $user->hasPermissionTo('view practices')) {
            return false;
        }

        return $this->canAccessPractice($user, $practice);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create practices');
    }

    public function update(User $user, Practice $practice): bool
    {
        if (! $user->hasPermissionTo('update practices')) {
            return false;
        }

        return $this->canAccessPractice($user, $practice);
    }

    public function updateStatus(User $user, Practice $practice): bool
    {
        if (! $user->hasPermissionTo('update practice status')) {
            return false;
        }

        if ($practice->practice_status === PracticeStatus::DISBURSED) {
            return false;
        }

        return $this->canAccessPractice($user, $practice);
    }

    public function delete(User $user, Practice $practice): bool
    {
        if (! $user->hasPermissionTo('delete practices')) {
            return false;
        }

        return $this->canAccessPractice($user, $practice);
    }

    public function restore(User $user, Practice $practice): bool
    {
        return $user->hasPermissionTo('restore trash')
            && (
                $user->hasPermissionTo('view all trash')
                || $practice->deleted_by === $user->getKey()
            );
    }

    public function forceDelete(User $user, Practice $practice): bool
    {
        return $user->hasPermissionTo('force delete trash')
            && (
                $user->hasPermissionTo('view all trash')
                || $practice->deleted_by === $user->getKey()
            );
    }

    public function importPractice(User $user): bool
    {
        return $user->hasPermissionTo('import practices');
    }

    public function exportPractice(User $user): bool
    {
        return $user->hasPermissionTo('export practices');
    }

    private function canAccessPractice(
        User $user,
        Practice $practice
    ): bool {
        if ($user->isConsultant() || $user->isExternal()) {
            return $user->getKey() === $practice->user_id;
        }

        return true;
    }
}
