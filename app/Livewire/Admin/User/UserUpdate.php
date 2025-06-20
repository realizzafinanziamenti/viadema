<?php

namespace App\Livewire\Admin\User;

use App\Livewire\Forms\UserForm;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Spatie\LivewireFilepond\WithFilePond;

class UserUpdate extends Component
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
        Gate::authorize('update', $this->user);
        $user = $this->form->update();

        $this->redirectRoute('user.show', ['id' => $user->id], navigate: true);
    }

    /**
     * remove profile photo
     */
    public function removeProfilePhoto(): void
    {
        Gate::authorize('update', User::class);
        $this->form->profilePhotoRemoved = true;
        $this->form->profilePhotoUrl = null;
    }

    /**
     * restore removed profile photo
     */
    public function restoreProfilePhoto(): void
    {
        $this->form->profilePhotoRemoved = false;
        $this->form->profilePhotoUrl = $this->user->profile_photo_path;
    }

    public function mount($id)
    {
        $this->user = User::findOrFail($id);
        Gate::authorize('update', $this->user);

        $this->form->setUser($this->user);
    }

    #[Layout('components.layouts.app')]
    public function render()
    {
        return view('livewire.admin.user.user-update');
    }
}
