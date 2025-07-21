<div class="w-full">
    <x-card class="w-3xl mx-auto">
        <x-card-header label="Crea nuovo collaboratore" />

        <form wire:submit.prevent='save' class="w-2xl mx-auto mt-10 mb-5">
            {{-- Profile Photo --}}
            <div class="flex flex-col items-center gap-1.5 col-span-2 mb-12">
                @if ($form->profilePhoto)
                    {{-- Show temporary new uploaded photo --}}
                    <x-uploaded-image-container wire:key="new-photo-preview" wire:transition
                        wire:transition.duration.500ms wire:transition.scale.95 wire:transition.opacity>
                        <img src="{{ $form->profilePhoto->temporaryUrl() }}" alt="New profile photo"
                            class="rounded-full w-profile-photo h-40 w-40 object-cover object-center" />

                        <x-button-remove-image wire:click="$set('form.profilePhoto', null)"
                            class="absolute top-2 right-2" />
                    </x-uploaded-image-container>
                @else
                    {{-- Show input and label --}}
                    <div class="flex flex-col items-center justify-center gap-1.5 " wire:key="input-photo"
                        wire:transition wire:transition.duration.500ms wire:transition.scale.95 wire:transition.opacity>

                        <div class="flex items-center justify-center">
                            <x-upload-image-container model="form.profilePhoto" :has-error="$errors->has('form.profilePhoto')"
                                acceptedFileTypes="images" />
                        </div>
                        <flux:error name="form.profilePhoto" />
                    </div>
                @endif
            </div>

            @include('partials.user.user-form-fields', [
                'form' => $form,
            ])

            {{-- Submit Buttons --}}
            <div class="flex items-center justify-end gap-x-3 mt-18">
                <a href="{{ route('user.index') }}" wire:navigate>
                    <flux:button variant="primary" type="button" size="sm"
                        class="px-10 bg-gray-custom-2 border-gray-custom-2 text-gray-custom-5 hover:bg-gray-custom-3-hover hover:border-gray-custom-3-hover hover:text-white">
                        Annulla
                    </flux:button>
                </a>

                <flux:button variant="primary" type="submit" size="sm"
                    class="px-10 bg-azure-custom border-azure-custom hover:bg-azure-custom-hover hover:border-azure-custom-hover">
                    Crea
                </flux:button>
            </div>
        </form>
    </x-card>
</div>
