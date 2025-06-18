<div>
    <x-card class="max-w-3xl mx-auto">
        <x-card-header class="mb-6" label="Gestione assicurazioni" />

        <div class="flex justify-end items-center mb-4">
            @can('create customer types')
                <x-buttons.create-button size="sm" px="px-6" label="Crea nuovo tipo cliente"
                    x-on:click="$dispatch('open-modal', 'create-customer-type')" />
            @endcan
        </div>

        @foreach ($customerTypes as $index => $customerType)
            <div
                class="w-full flex justify-between items-center truncate py-2 px-3
                    text-sm bg-white border-b {{ $index === 0 ? 'border-t' : '' }} border-zinc-200 text-zinc-500">
                <span>{{ $customerType->name }}</span>

                <div class="flex space-x-2">
                    @can('update', $customerType)
                        <x-table-action-button-edit wire:click="selectCustomerTypeForUpdate({{ $customerType->id }})"
                            class="btn btn-primary">Modifica</x-table-action-button-edit>
                    @endcan

                    @can('delete', $customerType)
                        <x-table-action-button-delete wire:click="selectCustomerTypeForDelete({{ $customerType->id }})"
                            class="btn btn-danger">Elimina</x-table-action-button-delete>
                    @endcan
                </div>
            </div>
        @endforeach

        {{-- Pagination --}}
        <div class="mt-5">
            {{ $customerTypes->links() }}
        </div>
    </x-card>

    {{-- Create Customer Type Modal --}}
    <x-modal name="create-customer-type">
        <div class="flex flex-col"></div>
        <x-modal-header label="Crea tipo cliente" class="mb-6" />

        <form wire:submit.prevent='createCustomerType'>
            {{-- Customer Type Name --}}
            <div class="flex flex-col gap-1.5">
                <flux:label>Nome tipo cliente</flux:label>
                <flux:input size="sm" placeholder="Inserisci nome tipo cliente" wire:model='name' />
                <flux:error name="name" />
            </div>

            {{-- Buttons --}}
            <div class="flex gap-3 justify-end mt-16">
                <flux:button variant="primary" type="button" size="sm"
                    x-on:click="$dispatch('close-modal', 'create-customer-type')"
                    class="px-10 bg-gray-custom-2 border-gray-custom-2 text-gray-custom-5 hover:bg-gray-custom-3-hover hover:border-gray-custom-3-hover hover:text-white">
                    Annulla
                </flux:button>

                <flux:button variant="primary" type="submit" size="sm"
                    class="px-10 bg-azure-custom border-azure-custom hover:bg-azure-custom-hover hover:border-azure-custom-hover">
                    Crea
                </flux:button>
            </div>
        </form>
    </x-modal>

    {{-- Update Customer Type Modal --}}
    <x-modal name="update-customer-type">
        <div class="flex flex-col">
            <x-modal-header label="Modifica tipo cliente" class="mb-6" />

            <form wire:submit.prevent='updateCustomerType'>
                {{-- Customer Type Name --}}
                <div class="flex flex-col gap-1.5">
                    <flux:label>Nome tipo cliente</flux:label>
                    <flux:input size="sm" placeholder="Inserisci nome tipo cliente" wire:model='name' />
                    <flux:error name="name" />
                </div>

                {{-- Buttons --}}
                <div class="flex gap-3 justify-end mt-16">
                    <flux:button variant="primary" type="button" size="sm"
                        x-on:click="$dispatch('close-modal', 'update-customer-type')"
                        class="px-10 bg-gray-custom-2 border-gray-custom-2 text-gray-custom-5 hover:bg-gray-custom-3-hover hover:border-gray-custom-3-hover hover:text-white">
                        Annulla
                    </flux:button>

                    <flux:button variant="primary" type="submit" size="sm"
                        class="px-10 bg-azure-custom border-azure-custom hover:bg-azure-custom-hover hover:border-azure-custom-hover">
                        Modifica
                    </flux:button>
                </div>
            </form>
        </div>
    </x-modal>

    {{-- Delete Customer Type Modal --}}
    <x-delete-modal name="delete-customer-type" header="Conferma eliminazione tipo cliente"
        function="deleteCustomerType"
        message="Sei sicuro di voler eliminare il tipo cliente <strong>{{ $selectedInsurance?->name }}</strong>?" />
</div>
