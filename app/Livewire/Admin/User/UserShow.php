<?php

namespace App\Livewire\Admin\User;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Component;

class UserShow extends Component
{
    public User $user;

    public function mount($id)
    {
        $this->user = User::with('profile')->findOrFail($id);
        Gate::authorize('view', $this->user);
    }

    #[Layout('components.layouts.app')]
    public function render()
    {
        return view('livewire.admin.user.user-show');
    }
}
