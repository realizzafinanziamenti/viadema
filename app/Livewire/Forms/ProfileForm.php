<?php

namespace App\Livewire\Forms;

use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Validate;
use Livewire\Form;
use Masmerise\Toaster\Toaster;

class ProfileForm extends Form
{
    public ?User $user;
    public string $firstName = '';
    public ?string $lastName = null;
    public string $email = '';
    public bool $notificationsEnabled = false;
    public ?string $taxId = null;
    public ?string $phone = null;
    public ?string $city = null;
    public $profilePhoto = null;
    public $profilePhotoUrl = null;
    public $profilePhotoRemoved = false;

    protected function rules(): array
    {
        return [
            'firstName' => ['required', 'string', 'max:255'],
            'lastName' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->user?->id)],
            'notificationsEnabled' => ['boolean'],
            'profilePhoto' => ['nullable', 'image', 'max:4096'], // 1MB max
            'taxId' => ['nullable', 'string', 'size:16'],
            'phone' => ['nullable', 'string', 'min:10', 'max:24'],
            'city' => ['nullable', 'string', 'max:255'],
        ];
    }

    protected function validationAttributes()
    {
        return [
            'firstName' => 'nome',
            'lastName' => 'cognome',
            'email' => 'email',
            'notificationsEnabled' => 'notifiche',
            'profilePhoto' => 'foto profilo',
            'taxId' => 'codice fiscale',
            'phone' => 'cellulare',
            'city' => 'città',
        ];
    }

    /**
     * set profile for update
     */
    public function setProfile(User $user)
    {
        $this->user = $user;

        $this->firstName = $user->first_name;
        $this->lastName = $user->last_name;
        $this->email = $user->email;
        $this->phone = $user->profile?->phone;
        $this->taxId = $user->profile?->tax_id;
        $this->city = $user->profile?->city;
        $this->profilePhotoUrl = $user->profile_photo_path;
        $this->notificationsEnabled = $user->profile?->notifications_enabled ?? false;
    }

    /**
     * update user
     */
    public function update()
    {
        $this->validate();

        try {
            DB::transaction(function () {
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
                    'profile_photo_path' => $this->profilePhotoUrl ?: null,
                ]);

                $this->user->profile()->update([
                    'phone' => $this->phone,
                    'tax_id' => $this->taxId,
                    'city' => $this->city,
                ]);
            });

            Toaster::success('Profilo aggiornato con successo.');
            return $this->user;
        } catch (Exception $e) {
            Log::error('Errore durante l\'aggiornamento del profilo: ' . $e->getMessage());
            Toaster::error('Errore durante l\'aggiornamento del profilo: ' . $e->getMessage());
        }
    }
}
