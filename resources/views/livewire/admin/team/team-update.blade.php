<div class="w-full">
    <x-card class="w-3xl mx-auto">
        <x-card-header label="Modifica collaboratore" />

        <form wire:submit.prevent='save' class="w-2xl mx-auto my-10">
            <div class="grid grid-cols-2 gap-6">
                <flux:field>
                    <flux:label>Nome *</flux:label>
                    <flux:input size="sm" wire:model='form.firstName' />
                    <flux:error name="form.firstName" />
                </flux:field>

                <flux:field>
                    <flux:label>Cognome *</flux:label>
                    <flux:input size="sm" wire:model='form.lastName' />
                    <flux:error name="form.lastName" />
                </flux:field>

                <flux:field>
                    <flux:label>Cellulare</flux:label>
                    <flux:input size="sm" wire:model='form.phone' />
                    <flux:error name="form.phone" />
                </flux:field>

                <flux:field>
                    <flux:label>Codice Fiscale</flux:label>
                    <flux:input size="sm" wire:model='form.taxId' />
                    <flux:error name="form.taxId" />
                </flux:field>

                <flux:field>
                    <flux:label>Città</flux:label>
                    <flux:input size="sm" wire:model='form.city' />
                    <flux:error name="form.city" />
                </flux:field>

                <flux:field>
                    <flux:label>Email *</flux:label>
                    <flux:input type="email" size="sm" wire:model='form.email' />
                    <flux:error name="form.email" />
                </flux:field>

                <flux:field>
                    <flux:label>Password *</flux:label>
                    <flux:input type="password" size="sm" wire:model='form.password' viewable />
                    <flux:error name="form.password" />
                </flux:field>

                <flux:field>
                    <flux:label>Conferma Password *</flux:label>
                    <flux:input type="password" size="sm" wire:model='form.passwordConfirmation' viewable />
                    <flux:error name="form.passwordConfirmation" />
                </flux:field>
            </div>

            <div class="flex items-center justify-end gap-x-3 mt-10">
                <flux:button variant="primary" type="submit" size="sm"
                    class="px-10 bg-gray-custom-2 border-gray-custom-2 text-gray-custom-5 hover:bg-blue-custom-dark">
                    Annulla
                </flux:button>

                <flux:button variant="primary" type="submit" size="sm"
                    class="px-10 bg-azure-custom border-azure-custom hover:bg-blue-custom-dark">
                    Modifica
                </flux:button>
            </div>
        </form>
    </x-card>
</div>
