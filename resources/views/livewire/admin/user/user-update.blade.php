<div class="w-full">
    <x-card class="w-3xl mx-auto">
        <x-card-header label="Modifica collaboratore" />

        <form wire:submit.prevent='save' class="w-2xl mx-auto mt-10 mb-5">

            @include('partials.user.user-form-fields', [
                'form' => $form,
            ])

            {{-- Profile Photo --}}
            <div class="flex flex-col gap-1.5 col-span-2 mt-6">
                <flux:label>Immagine Profilo</flux:label>

                @if ($user->profile_photo_path && !$form->profilePhotoRemoved)
                    <x-upload-image-container wire:key="photo-preview" wire:transition wire:transition.duration.500ms
                        wire:transition.scale.95 wire:transition.opacity>
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

            {{-- Submit Buttons --}}
            <div class="flex items-center justify-end gap-x-3 mt-18 }}">
                <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('user.index') }}"
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
