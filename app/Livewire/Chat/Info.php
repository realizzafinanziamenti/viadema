<?php

namespace App\Livewire\Chat;

class Info extends \Namu\WireChat\Livewire\Chat\Info
{
    public function deleteChat()
    {
        abort_unless(auth()->user()?->hasRole('superadmin'), 403);

        return parent::deleteChat();
    }
}
