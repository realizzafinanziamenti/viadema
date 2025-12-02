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

class UserCreate extends Component
{
    use WithFileUploads, InteractsWithDropdowns, EnumHelper, AcceptedFileTypes;

    // user form component
    public UserForm $form;
    public array $departments = [];

    /**
     * Set department
     */
    public function setDepartment(?string $value = null): void
    {
        $this->setFormSelectValue('department', $value);
    }

    /**
     * Save team member
     */
    public function save(): void
    {
        Gate::authorize('create', User::class);
        $user = $this->form->store();

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
     * remove temporary profile photo
     */
    public function removeProfilePhoto(): void
    {
        $this->form->profilePhotoRemoved = true;
        $this->form->profilePhotoUrl = null;
    }

    public function mount()
    {
        Gate::authorize('create', User::class);
        $this->departments = $this->getEnumOptions(UserDepartment::class);
    }

    #[Layout('components.layouts.app')]
    public function render()
    {
        return view('livewire.admin.user.user-create');
    }
}
