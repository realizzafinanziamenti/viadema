<?php

namespace App\Livewire\Admin\Profile;

use App\Livewire\Forms\ProfileForm;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Spatie\LivewireFilepond\WithFilePond;

class ProfileUpdate extends Component
{
    use WithFilePond;

    public ProfileForm $form;

    /**
     * edit profile
     */
    public function save(): void
    {
        // Gate::authorize('update', $this->user);
        $this->form->update();
        $this->redirectRoute('profile.show',  navigate: true);
    }

    /**
     * remove profile photo
     */
    public function removeProfilePhoto(): void
    {
        // Gate::authorize('update', User::class);
        $this->form->profilePhotoRemoved = true;
        $this->form->profilePhotoUrl = null;
    }

    /**
     * restore removed profile photo
     */
    public function restoreProfilePhoto(): void
    {
        $this->form->profilePhotoRemoved = false;
        $this->form->profilePhotoUrl = auth()->user()->profile_photo_path;
    }

    public function mount()
    {
        $this->form->setProfile(auth()->user());
    }

    #[Layout('components.layouts.app')]
    public function render()
    {
        return view('livewire.admin.profile.profile-update');
    }
}
