<div>
    <x-card class="max-w-3xl mx-auto">
        <x-card-header class="mb-3" label="Gestione tipologia cliente" />

        <div class="flex justify-end items-center mb-5">
            @can('create customer types')
                <x-buttons.create-button size="sm" px="px-6" label="Crea tipo cliente"
                    wire:click="openCreateCustomerTypeModal" />
            @endcan
        </div>

        <x-table minWidth="max-content">
            {{-- Table Header --}}
            <x-slot name="header" class="border-b">
                <x-table-header label="Tipo cliente" />

                <x-table-header class="w-[100px]">
                    {{-- Actions --}}
                </x-table-header>
            </x-slot>

            {{-- Table body --}}
            @foreach ($customerTypes as $index => $customerType)
                <tr wire:key='{{ $customerType->id }}' class="border-y border-collapse">
                    <x-table-data label="{{ $customerType->name }}" />

                    {{-- Actions --}}
                    <x-table-data>
                        <div class="flex items-center justify-end w-full gap-3">
                            @can('delete', $customerType)
                                <x-table-action-button-delete
                                    wire:click="selectCustomerTypeForDelete({{ $customerType->id }})"
                                    class="btn btn-danger">Elimina</x-table-action-button-delete>
                            @endcan
                        </div>
                    </x-table-data>
                </tr>
            @endforeach
        </x-table>

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
                <flux:label>Tipo cliente</flux:label>
                <flux:input size="sm" placeholder="Inserisci tipo cliente" wire:model='name' />
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

    {{-- Delete Customer Type Modal --}}
    <x-delete-modal name="delete-customer-type" header="Conferma eliminazione tipologia cliente"
        function="deleteCustomerType"
        message="Sei sicuro di voler eliminare il tipologia cliente <strong>{{ $selectedCustomerType?->name }}</strong>?" />
</div>
