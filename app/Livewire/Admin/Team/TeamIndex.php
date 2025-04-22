<?php

namespace App\Livewire\Admin\Team;

use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;

class TeamIndex extends Component
{
    use WithPagination, WithoutUrlPagination;

    #[Layout('components.layouts.app')]
    public function render()
    {
        $query = User::teamMembers()
            ->orderByDesc('updated_at');
        $teamMembers = $query->paginate(15);

        return view('livewire.admin.team.team-index', [
            'teamMembers' => $teamMembers,
        ]);
    }
}
