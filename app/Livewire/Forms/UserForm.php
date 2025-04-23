<?php

namespace App\Livewire\Forms;

use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Validate;
use Livewire\Form;

class UserForm extends Form
{
    public ?User $user;

    public $role = null;
    public $firstName = null;
    public $lastName = null;
    public $email = null;
    public $password = null;
    public $passwordConfirmation = null;
    public $phone = null;
    public $taxId = null;
    public $city = null;
    public $profilePhoto = null;

    protected function rules()
    {
        return [
            'firstName' => 'required|string|max:255',
            'lastName' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->user?->id)],
            'phone' => 'nullable|string|min:10|max:24',
            'taxId' => 'nullable|string|size:16',
            'city' => 'nullable|string|max:255',
            'profilePhoto' => 'nullable|file|mimes:jpeg,png|max:4096',
        ];
    }

    protected function validationAttributes()
    {
        return [
            'firstName' => 'nome',
            'lastName' => 'cognome',
            'email' => 'email',
            'password' => 'password',
            'passwordConfirmation' => 'conferma password',
            'phone' => 'cellulare',
            'taxId' => 'codice fiscale',
            'city' => 'città',
            'profilePhoto' => 'foto profilo',
        ];
    }

    /**
     * set user for update
     */
    public function setUser(User $user)
    {
        $this->user = $user;

        $this->firstName = $user->first_name;
        $this->lastName = $user->last_name;
        $this->email = $user->email;
        $this->phone = $user->profile?->phone;
        $this->taxId = $user->profile?->tax_id;
        $this->city = $user->profile?->city;
        $this->profilePhoto = $user->profile_photo_path;
    }

    /**
     * create user
     */
    public function store()
    {
        // add password validation
        $this->validate(array_merge($this->rules(), [
            'password' => ['required', 'string', 'confirmed', Password::min(8)->letters()->mixedCase()->numbers()->symbols()],
        ]));

        try {
            $user = DB::transaction(function () {
                // check the profile photo
                $profilePhotoPath = $this->profilePhoto
                    ? $this->profilePhoto->store('profile-photo', 'public')
                    : null;

                $user = User::create([
                    'first_name' => $this->firstName,
                    'last_name' => $this->lastName,
                    'email' => $this->email,
                    'password' => Hash::make($this->password),
                    'profile_photo_path' => $profilePhotoPath ?: null,
                ]);

                $user->assignRole($this->role);

                $user->profile()->create([
                    'phone' => $this->phone,
                    'tax_id' => $this->taxId,
                    'city' => $this->city,
                ]);
            });

            session()->flash('success', 'Utente creato con successo.');
            return $user;
        } catch (Exception $e) {
            Log::error('Errore durante la creazione dell\'utente: ' . $e->getMessage());
            session()->flash('error', 'Errore durante la creazione dell\'utente: ' . $e->getMessage());
        }
    }

    /**
     * update user
     */
    public function update()
    {
        $this->validate(array_merge($this->rules(), [
            'password' => ['nullable', 'string', 'confirmed', Password::min(8)->letters()->mixedCase()->numbers()->symbols()],
        ]));

        try {
            DB::transaction(function () {
                // check the password update
                $password = $this->password
                    ? Hash::make($this->password)
                    : $this->user->password;

                // check the profile photo
                $profilePhotoPath = $this->profilePhoto
                    ? $this->profilePhoto->store('profile-photo', 'public')
                    : $this->user->profile_photo_path;

                $this->user->update([
                    'first_name' => $this->firstName,
                    'last_name' => $this->lastName,
                    'email' => $this->email,
                    'password' => $password,
                    'profile_photo_path' => $profilePhotoPath ?: null,
                ]);

                $this->user->profile()->update([
                    'phone' => $this->phone,
                    'tax_id' => $this->taxId,
                    'city' => $this->city,
                ]);
            });

            session()->flash('success', 'Utente aggiornato con successo.');
            return $this->user;
        } catch (Exception $e) {
            Log::error('Errore durante l\'aggiornamento dell\'utente: ' . $e->getMessage());
            session()->flash('error', 'Errore durante l\'aggiornamento dell\'utente: ' . $e->getMessage());
        }
    }
}
