<div class="w-full">
    <x-card class="w-3xl mx-auto">
        <x-card-header label="Modifica collaboratore" />

        <form wire:submit.prevent='save' class="w-2xl mx-auto mt-10 mb-5">
            <div class="grid grid-cols-2 gap-6">
                {{-- First Name --}}
                <div class="flex flex-col gap-1.5">
                    <flux:label>Nome *</flux:label>
                    <div class="flex flex-col gap-0.5">
                        <flux:input size="sm" wire:model='form.firstName' />
                        <flux:error name="form.firstName" />
                    </div>
                </div>
                {{-- Last Name --}}
                <div class="flex flex-col gap-1.5">
                    <flux:label>Cognome *</flux:label>
                    <div class="flex flex-col gap-0.5">
                        <flux:input size="sm" wire:model='form.lastName' />
                        <flux:error name="form.lastName" />
                    </div>
                </div>
                {{-- Phone --}}
                <div class="flex flex-col gap-1.5">
                    <flux:label>Cellulare</flux:label>
                    <div class="flex flex-col gap-0.5">
                        <flux:input size="sm" wire:model='form.phone' />
                        <flux:error name="form.phone" />
                    </div>
                </div>
                {{-- Tax ID --}}
                <div class="flex flex-col gap-1.5">
                    <flux:label>Codice Fiscale</flux:label>
                    <div class="flex flex-col gap-0.5">
                        <flux:input size="sm" wire:model='form.taxId' />
                        <flux:error name="form.taxId" />
                    </div>
                </div>
                {{-- City --}}
                <div class="flex flex-col gap-1.5">
                    <flux:label>Città</flux:label>
                    <div class="flex flex-col gap-0.5">
                        <flux:input size="sm" wire:model='form.city' />
                        <flux:error name="form.city" />
                    </div>
                </div>
                {{-- Email --}}
                <div class="flex flex-col gap-1.5">
                    <flux:label>Email *</flux:label>
                    <div class="flex flex-col gap-0.5">
                        <flux:input type="email" size="sm" wire:model='form.email' />
                        <flux:error name="form.email" />
                    </div>
                </div>
                {{-- Password --}}
                <div class="flex flex-col gap-1.5">
                    <flux:label>Password *</flux:label>
                    <div class="flex flex-col gap-0.5">
                        <flux:input type="password" size="sm" wire:model='form.password' viewable />
                        <flux:error name="form.password" />
                    </div>
                </div>
                {{-- Password Confirmation --}}
                <div class="flex flex-col gap-1.5">
                    <flux:label>Conferma Password *</flux:label>
                    <div class="flex flex-col gap-0.5">
                        <flux:input type="password" size="sm" wire:model='form.passwordConfirmation' viewable />
                        <flux:error name="form.passwordConfirmation" />
                    </div>
                </div>
                {{-- Profile Photo --}}
                <div class="flex flex-col gap-1.5 col-span-2">
                    <flux:label>Immagine Profilo</flux:label>

                    @if ($user->profile_photo_path && !$form->profilePhotoRemoved)
                        <x-upload-image-container wire:key="photo-preview" wire:transition
                            wire:transition.duration.500ms wire:transition.scale.95 wire:transition.opacity>
                            <img src="{{ $user->getProfilePhotoUrl() }}" alt="{{ $user->full_name }}"
                                class="rounded w-profile-photo h-50 w-auto" />

                            <x-button-remove-image wire:click="removeProfilePhoto" class="absolute top-2 right-2" />
                        </x-upload-image-container>
                    @else
                        <div wire:key="filepond-upload" wire:transition wire:transition.duration.500ms
                            wire:transition.scale.95 wire:transition.opacity class="relative">
                            <x-filepond::upload wire:model="form.profilePhoto" maxFileSize='4MB'
                                accepted-file-types="image/jpeg,image/png" />

                            <x-button-restore-image wire:click="restoreProfilePhoto" class="absolute top-2 right-2" />
                        </div>
                    @endif

                </div>
            </div>

            <div class="flex items-center justify-end gap-x-3 mt-18 }}">
                <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('user.team.index') }}"
                    wire:navigate>
                    <flux:button variant="primary" type="button" size="sm"
                        class="px-10 bg-gray-custom-2 border-gray-custom-2 text-gray-custom-5 hover:bg-gray-custom-3-hover hover:border-gray-custom-3-hover hover:text-white">
                        Annulla
                    </flux:button>
                </a>

                <flux:button variant="primary" type="submit" size="sm"
                    class="px-10 bg-azure-custom border-azure-custom hover:bg-azure-custom-hover hover:border-azure-custom-hover">
                    Modifica
                </flux:button>
            </div>
        </form>
    </x-card>
</div>
