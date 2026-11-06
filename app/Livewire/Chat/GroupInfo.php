<?php

namespace App\Livewire\Chat;

class GroupInfo extends \Namu\WireChat\Livewire\Chat\Group\Info
{
    public function deleteGroup()
    {
        abort_unless(auth()->user()?->hasRole('superadmin'), 403);

        // Preserve WireChat's membership, ownership and empty-group safeguards.
        return parent::deleteGroup();
    }
}
