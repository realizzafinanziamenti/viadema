<?php

namespace App\Livewire\Admin\Team;

use App\Livewire\Forms\UserForm;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Spatie\LivewireFilepond\WithFilePond;

class TeamUpdate extends Component
{
    use WithFilePond;

    public User $user;
    // user form component
    public UserForm $form;

    /**
     * edit assignment
     */
    public function save(): void
    {
        Gate::authorize('update', User::class);
        $user = $this->form->update();

        $this->redirectRoute('user.team.show', ['id' => $user->id], navigate: true);
    }

    public function mount($id)
    {
        Gate::authorize('update', User::class);
        $this->user = User::findOrFail($id);

        $this->form->setUser($this->user);
    }

    #[Layout('components.layouts.app')]
    public function render()
    {
        return view('livewire.admin.team.team-update');
    }
}
