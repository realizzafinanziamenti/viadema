<?php

namespace App\Livewire\Admin\Profile;

use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Masmerise\Toaster\Toaster;

class ProfileUpdatePassword extends Component
{
    public string $current_password = '';
    public string $password = '';
    public string $password_confirmation = '';

    protected function validationAttributes()
    {
        return [
            'current_password' => 'password attuale',
            'password' => 'nuova password',
            'password_confirmation' => 'conferma password',
        ];
    }

    /**
     * Update the password for the currently authenticated user.
     */
    public function updatePassword(): void
    {
        $validated = $this->validate([
            'current_password' => ['required', 'string', 'current_password'],
            'password' => ['required', 'string', Password::min(8)->letters()->mixedCase()->numbers()->symbols(), 'confirmed'],
        ]);

        auth()->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        $this->reset('current_password', 'password', 'password_confirmation');

        Toaster::success('Password aggiornata con successo');
        $this->redirectRoute('profile.show', navigate: true);
    }

    #[Layout('components.layouts.app')]
    public function render()
    {
        return view('livewire.admin.profile.profile-update-password');
    }
}
