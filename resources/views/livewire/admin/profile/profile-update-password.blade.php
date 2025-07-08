<div class="w-full">
    <x-card class="max-w-md mx-auto">
        <x-card-header class="mb-5" label="Modifica password" />

        <form wire:submit.prevent='updatePassword' class="w-sm mx-auto mt-10 mb-5">
            <div class="flex flex-col gap-6">
                {{-- Current Password --}}
                <div class="flex flex-col gap-1.5">
                    <flux:label>Password attuale</flux:label>
                    <div class="flex flex-col gap-0.5">
                        <flux:input type="password" viewable size="sm" wire:model='current_password'
                            id="current_password" name="current_password" />
                        <flux:error name="current_password" />
                    </div>
                </div>

                {{-- New Password --}}
                <div class="flex flex-col gap-1.5">
                    <flux:label>Nuova password</flux:label>
                    <div class="flex flex-col gap-0.5">
                        <flux:input type="password" viewable size="sm" wire:model='password' id="password"
                            name="password" />
                        <flux:error name="password" />
                    </div>
                </div>

                {{-- Confirm New Password --}}
                <div class="flex flex-col gap-1.5">
                    <flux:label>Conferma nuova password</flux:label>
                    <div class="flex flex-col gap-0.5">
                        <flux:input type="password" viewable size="sm" wire:model='password_confirmation'
                            id="password_confirmation" name="password_confirmation" />
                        <flux:error name="password_confirmation" />
                    </div>
                </div>
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
