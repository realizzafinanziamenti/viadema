<?php

namespace App\Livewire\Admin\Profile;

use Livewire\Attributes\Layout;
use Livewire\Component;

class ProfileShow extends Component
{
    #[Layout('components.layouts.app')]
    public function render()
    {
        return view('livewire.admin.profile.profile-show');
    }
}
