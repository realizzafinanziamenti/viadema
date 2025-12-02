<?php

namespace App\Livewire\Admin\User;

use App\Enums\UserDepartment;
use App\Livewire\Forms\UserForm;
use App\Models\User;
use App\Traits\AcceptedFileTypes;
use App\Traits\EnumHelper;
use App\Traits\InteractsWithDropdowns;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

class UserUpdate extends Component
{
    use WithFileUploads, InteractsWithDropdowns, EnumHelper, AcceptedFileTypes;

    public User $user;
    // user form component
    public UserForm $form;
    public array $departments = [];

    /**
     * Set department
     */
    public function setDepartment(?string $value = null): void
    {
        $this->setFormSelectValue('department', $value);
        $this->setRole($value);
    }

    /**
     * Set role based on department
     */
    public function setRole(?string $value): void
    {
        if ($value && $department = UserDepartment::tryFrom($value)) {
            $this->form->role = $department->getRole();
        }
    }

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
     * update profile photo callback for validation
     */
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
        $this->departments = $this->getEnumOptions(UserDepartment::class);
    }

    #[Layout('components.layouts.app')]
    public function render()
    {
        return view('livewire.admin.user.user-update');
    }
}
