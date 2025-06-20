<?php

namespace App\Livewire\Admin\User;

use App\Livewire\Forms\UserForm;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Spatie\LivewireFilepond\WithFilePond;

class UserCreate extends Component
{
    use WithFilePond;

    // user form component
    public UserForm $form;

    /**
     * Save team member
     */
    public function save(): void
    {
        Gate::authorize('create', User::class);
        $user = $this->form->store();

        $this->redirectRoute('user.show', ['id' => $user->id], navigate: true);
    }

    public function mount()
    {
        Gate::authorize('create', User::class);
        // set user rule to team member
        $this->form->role = 'team_member';
    }

    #[Layout('components.layouts.app')]
    public function render()
    {
        return view('livewire.admin.user.user-create');
    }
}
