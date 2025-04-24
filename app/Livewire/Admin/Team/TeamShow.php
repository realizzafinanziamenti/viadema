<?php

namespace App\Livewire\Admin\Team;

use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Component;

class TeamShow extends Component
{
    public User $user;

    public function mount($id)
    {
        $this->user = User::with('profile')->findOrFail($id);
    }

    #[Layout('components.layouts.app')]
    public function render()
    {
        return view('livewire.admin.team.team-show');
    }
}
