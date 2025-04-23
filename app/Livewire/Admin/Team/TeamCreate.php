<?php

namespace App\Livewire\Admin\Team;

use App\Livewire\Forms\UserForm;
use Livewire\Attributes\Layout;
use Livewire\Component;

class TeamCreate extends Component
{
    // user form component
    public UserForm $form;

    /**
     * Save team member
     */
    public function save(): void
    {
        // Gate::authorize('create', Assignment::class);
        $user = $this->form->store();

        // $this->redirectRoute('user.team.show', ['id' => $user->id], navigate: true);
    }

    public function mount()
    {
        // set user rule to team member
        $this->form->role = 'team_member';
    }

    #[Layout('components.layouts.app')]
    public function render()
    {
        return view('livewire.admin.team.team-create');
    }
}
