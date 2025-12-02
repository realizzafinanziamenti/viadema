<?php

namespace App\Livewire\Admin\Profile;

use App\Livewire\Forms\ProfileForm;
use App\Traits\AcceptedFileTypes;
use Illuminate\Support\Facades\Validator;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

class ProfileUpdate extends Component
{
    use WithFileUploads, AcceptedFileTypes;

    public ProfileForm $form;

    public function updatedFormProfilePhoto()
    {
        $validator = Validator::make(
            ['profilePhoto' => $this->form->profilePhoto],
            ['profilePhoto' => ['nullable', 'image', 'mimetypes:' . implode(',', $this->acceptedFileTypesArray(['images'])), 'max:10240']],
            [
                'profilePhoto.image' => 'Il file deve essere un\'immagine valida.',
                'profilePhoto.mimetypes' => 'Formato immagine non valido. I formati accettati sono: jpg, jpeg, png, webp.',
                'profilePhoto.max' => 'L\'immagine non può superare i 10MB.',
            ]
        );

        if ($validator->fails()) {
            $this->addError('form.profilePhoto', $validator->errors()->first('profilePhoto'));
            $this->reset('form.profilePhoto');
            return;
        }

        // Se la validazione ha successo, rimuove eventuali errori precedenti e mantiene il file
        $this->clearValidation('form.profilePhoto');
    }

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
