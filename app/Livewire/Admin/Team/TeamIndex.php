<?php

namespace App\Livewire\Admin\Team;

use Livewire\Attributes\Layout;
use Livewire\Component;

class TeamIndex extends Component
{
    #[Layout('components.layouts.app')]
    public function render()
    {
        return view('livewire.admin.team.team-index');
    }
}
