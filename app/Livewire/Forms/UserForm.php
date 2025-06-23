<?php

namespace App\Livewire\Forms;

use App\Enums\UserDepartment;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Validate;
use Livewire\Form;
use Masmerise\Toaster\Toaster;

class UserForm extends Form
{
    public ?User $user = null;

    public $role = null;
    public $department = null;
    public $firstName = null;
    public $lastName = null;
    public $email = null;
    public $password = null;
    public $passwordConfirmation = null;
    public $phone = null;
    public $taxId = null;
    public $city = null;
    public $profilePhoto = null;
    public $profilePhotoUrl = null;
    public $profilePhotoRemoved = false;

    protected function rules()
    {
        return [
            'role' => ['required', 'string', Rule::in(UserDepartment::getRoles())],
            'department' => ['required', 'string', new Enum(UserDepartment::class)],
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
            'role' => 'ruolo',
            'department' => 'dipartimento',
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

        $this->role = $user->getRoleNames()->first();  // Get the first role assigned to the user - correct because a user can have only one role in this context
        $this->department = $user->profile?->user_department?->value;
        $this->firstName = $user->first_name;
        $this->lastName = $user->last_name;
        $this->email = $user->email;
        $this->phone = $user->profile?->phone;
        $this->taxId = $user->profile?->tax_id;
        $this->city = $user->profile?->city;
        $this->profilePhotoUrl = $user->profile_photo_path;
    }

    /**
     * create user
     */
    public function store()
    {
        // add password validation
        $this->validate(array_merge($this->rules(), [
            'password' => ['required', 'string', 'confirmed:passwordConfirmation', Password::min(8)->letters()->mixedCase()->numbers()->symbols()],
        ]));

        try {
            $user = DB::transaction(function () {
                // check the profile photo
                $this->profilePhotoUrl = $this->profilePhoto
                    ? $this->profilePhoto->store('profile-photo', 'public')
                    : null;

                $user = User::create([
                    'first_name' => $this->firstName,
                    'last_name' => $this->lastName,
                    'email' => $this->email,
                    'password' => Hash::make($this->password),
                    'profile_photo_path' => $this->profilePhotoUrl ?: null,
                ]);

                $user->assignRole($this->role);

                $user->profile()->create([
                    'user_department' => $this->department,
                    'phone' => $this->phone,
                    'tax_id' => $this->taxId,
                    'city' => $this->city,
                ]);

                return $user;
            });

            Toaster::success('Utente creato con successo.');
            return $user;
        } catch (Exception $e) {
            Log::error('Errore durante la creazione dell\'utente: ' . $e->getMessage());
            Toaster::error('Errore durante la creazione dell\'utente: ' . $e->getMessage());
        }
    }

    /**
     * update user
     */
    public function update()
    {
        $this->validate(array_merge($this->rules(), [
            'password' => ['nullable', 'string', 'confirmed:passwordConfirmation', Password::min(8)->letters()->mixedCase()->numbers()->symbols()],
        ]));

        try {
            DB::transaction(function () {
                // check the password update
                $password = $this->password
                    ? Hash::make($this->password)
                    : $this->user->password;

                // check the profile photo removed
                // if the profile photo is removed, delete it from storage
                if ($this->profilePhotoRemoved && $this->user->profile_photo_path) {
                    if (Storage::exists($this->user->profile_photo_path)) {
                        Storage::delete($this->user->profile_photo_path);
                    }

                    $this->user->profile_photo_path = null;
                }

                // check the profile photo
                $this->profilePhotoUrl = $this->profilePhoto
                    ? $this->profilePhoto->store('profile-photo', 'public')
                    : $this->user->profile_photo_path;

                $this->user->update([
                    'first_name' => $this->firstName,
                    'last_name' => $this->lastName,
                    'email' => $this->email,
                    'password' => $password,
                    'profile_photo_path' => $this->profilePhotoUrl ?: null,
                ]);

                $this->user->syncRoles($this->role);  // SYNC ROLES

                $this->user->profile()->update([
                    'user_department' => $this->department,
                    'phone' => $this->phone,
                    'tax_id' => $this->taxId,
                    'city' => $this->city,
                ]);
            });

            Toaster::success('Utente aggiornato con successo.');
            return $this->user;
        } catch (Exception $e) {
            Log::error('Errore durante l\'aggiornamento dell\'utente: ' . $e->getMessage());
            Toaster::error('Errore durante l\'aggiornamento dell\'utente: ' . $e->getMessage());
        }
    }
}
