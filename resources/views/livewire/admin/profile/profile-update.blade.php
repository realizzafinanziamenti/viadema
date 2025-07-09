<div class="w-full">
    <x-card class="max-w-3xl mx-auto">
        <x-card-header class="mb-5" label="Modifica dati personali" />

        <form wire:submit.prevent='save' class="w-2xl mx-auto mt-10 mb-5">
            <div class="grid grid-cols-2 gap-6">
                {{-- Profile Photo --}}
                <div class="flex flex-col items-center gap-1.5 col-span-2 mb-6">
                    @if ($form->profilePhoto)
                        {{-- Show temporary new uploaded photo --}}
                        <x-upload-image-container wire:key="new-photo-preview" wire:transition
                            wire:transition.duration.500ms wire:transition.scale.95 wire:transition.opacity>
                            <img src="{{ $form->profilePhoto->temporaryUrl() }}" alt="New profile photo"
                                class="rounded-full w-profile-photo h-40 w-40 object-cover object-center" />

                            <x-button-remove-image wire:click="$set('form.profilePhoto', null)"
                                class="absolute top-2 right-2" />
                        </x-upload-image-container>
                    @elseif (auth()->user()->profile_photo_path && !$form->profilePhotoRemoved)
                        {{-- Show current photo --}}
                        <x-upload-image-container wire:key="photo-preview" wire:transition
                            wire:transition.duration.500ms wire:transition.scale.95 wire:transition.opacity>
                            <img src="{{ auth()->user()->getProfilePhotoUrl() }}" alt="{{ auth()->user()->full_name }}"
                                class="rounded-full w-profile-photo h-40 w-40 object-cover object-center" />

                            <x-button-remove-image wire:click="removeProfilePhoto" class="absolute top-2 right-2" />
                        </x-upload-image-container>
                    @else
                        {{-- Show input and label --}}
                        <div class="flex flex-col items-center gap-1.5 " wire:key="input-photo" wire:transition
                            wire:transition.duration.500ms wire:transition.scale.95 wire:transition.opacity>

                            <input type="file" id="fileUpload" wire:model.live="form.profilePhoto" class="hidden"
                                accept="image/jpeg,image/png" />
                            <flux:error name="form.profilePhoto" />

                            <label for="fileUpload" class="cursor-pointer relative">
                                <x-upload-image-container class="h-40 w-40 border-dashed text-gray-custom-3">

                                    {{-- Camera icon --}}
                                    <div wire:loading.remove wire:target="form.profilePhoto">
                                        <flux:icon.camera class="size-10" />
                                    </div>

                                    {{-- Loading spinner --}}
                                    <div wire:loading wire:target="form.profilePhoto">
                                        <flux:icon.loading class="size-10" />
                                    </div>

                                    {{-- Restore photo button --}}
                                    @if (auth()->user()->profile_photo_path && $form->profilePhotoRemoved)
                                        <x-button-restore-image wire:click="restoreProfilePhoto"
                                            class="absolute top-2 right-2" />
                                    @endif

                                </x-upload-image-container>
                            </label>
                        </div>
                    @endif
                </div>

                {{-- First Name --}}
                <div class="flex flex-col gap-1.5">
                    <flux:label>Nome</flux:label>
                    <div class="flex flex-col gap-0.5">
                        <flux:input size="sm" wire:model='form.firstName' />
                        <flux:error name="form.firstName" />
                    </div>
                </div>

                {{-- Last Name --}}
                <div class="flex flex-col gap-1.5">
                    <flux:label>Cognome</flux:label>
                    <div class="flex flex-col gap-0.5">
                        <flux:input size="sm" wire:model='form.lastName' />
                        <flux:error name="form.lastName" />
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

                @if (auth()->user()->profile)
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
                @endif
            </div>

            {{-- Buttons --}}
            <div class="flex items-center justify-end gap-x-3 mt-18">
                <a href="{{ route('profile.show') }}" wire:navigate>
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
