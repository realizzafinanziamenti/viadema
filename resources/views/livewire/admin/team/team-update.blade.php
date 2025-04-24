<div class="w-full">
    <x-card class="w-3xl mx-auto">
        <x-card-header label="Modifica collaboratore" />

        <form wire:submit.prevent='save' class="w-2xl mx-auto my-10">
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
            </div>

            <div class="flex items-center justify-end gap-x-3 mt-10">
                <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('user.team.index') }}"
                    wire:navigate>
                    <flux:button variant="primary" type="button" size="sm"
                        class="px-10 bg-gray-custom-2 border-gray-custom-2 text-gray-custom-5 hover:bg-gray-custom-3 hover:border-gray-custom-3 hover:text-white">
                        Annulla
                    </flux:button>
                </a>

                <flux:button variant="primary" type="submit" size="sm"
                    class="px-10 bg-azure-custom border-azure-custom hover:bg-azure-custom-dark hover:border-azure-custom-dark">
                    Modifica
                </flux:button>
            </div>
        </form>
    </x-card>
</div>
