<?php

namespace App\Livewire\Admin\User;

use App\Enums\UserDepartment;
use App\Livewire\Forms\UserForm;
use App\Models\User;
use App\Traits\EnumHelper;
use App\Traits\InteractsWithDropdowns;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Spatie\LivewireFilepond\WithFilePond;

class UserCreate extends Component
{
    use WithFilePond, InteractsWithDropdowns, EnumHelper;

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
        $this->departments = $this->getEnumOptions(UserDepartment::class);
    }

    #[Layout('components.layouts.app')]
    public function render()
    {
        return view('livewire.admin.user.user-create');
    }
}
