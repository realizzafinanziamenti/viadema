<div>
    <x-card class="max-w-3xl mx-auto">
        <x-card-header class="mb-6" label="Gestione tabella provvigionale" />

        <div class="flex justify-end items-center mb-4">
            @can('create financial tables')
                <x-buttons.create-button size="sm" px="px-6" label="Crea nuova provvigione"
                    wire:click="openCreateFinancialTableModal" />
            @endcan
        </div>

        @foreach ($financialTables as $index => $financialTable)
            <div
                class="w-full flex justify-between items-center truncate py-2 px-3
                    text-sm bg-white border-b {{ $index === 0 ? 'border-t' : '' }} border-zinc-200 text-zinc-500">
                <span>{{ $financialTable->percentage }}</span>

                <div class="flex space-x-2">
                    {{-- DISABLED --}}
                    {{-- @can('update', $financialTable)
                        <x-table-action-button-edit wire:click="selectFinancialTableForUpdate({{ $financialTable->id }})"
                            class="btn btn-primary">Modifica</x-table-action-button-edit>
                    @endcan --}}

                    @can('delete', $financialTable)
                        <x-table-action-button-delete wire:click="selectFinancialTableForDelete({{ $financialTable->id }})"
                            class="btn btn-danger">Elimina</x-table-action-button-delete>
                    @endcan
                </div>
            </div>
        @endforeach

        {{-- Pagination --}}
        <div class="mt-5">
            {{ $financialTables->links() }}
        </div>
    </x-card>

    {{-- Create Insurance Modal --}}
    <x-modal name="create-financial-table">
        <div class="flex flex-col">
            <x-modal-header label="Crea provvigione" class="mb-6" />

            <form wire:submit.prevent='createFinancialTable'>
                {{-- Financial Table Name --}}
                <div class="flex flex-col gap-1.5">
                    <flux:label>Provvigione</flux:label>
                    <flux:input size="sm" placeholder="Inserisci provvigione" wire:model='percentage' />
                    <flux:error name="percentage" />
                </div>

                {{-- Buttons --}}
                <div class="flex gap-3 justify-end mt-16">
                    <flux:button variant="primary" type="button" size="sm"
                        x-on:click="$dispatch('close-modal', 'create-financial-table')"
                        class="px-10 bg-gray-custom-2 border-gray-custom-2 text-gray-custom-5 hover:bg-gray-custom-3-hover hover:border-gray-custom-3-hover hover:text-white">
                        Annulla
                    </flux:button>

                    <flux:button variant="primary" type="submit" size="sm"
                        class="px-10 bg-azure-custom border-azure-custom hover:bg-azure-custom-hover hover:border-azure-custom-hover">
                        Crea
                    </flux:button>
                </div>
            </form>
        </div>
    </x-modal>

    {{-- Update Financial Table Modal --}}
    <x-modal name="update-financial-table">
        <div class="flex flex-col">
            <x-modal-header label="Modifica provvigione" class="mb-6" />

            <form wire:submit.prevent='updateFinancialTable'>
                {{-- Financial Table Name --}}
                <div class="flex flex-col gap-1.5">
                    <flux:label>Provvigione</flux:label>
                    <flux:input size="sm" placeholder="Inserisci provvigione" wire:model='percentage' />
                    <flux:error name="percentage" />
                </div>

                {{-- Buttons --}}
                <div class="flex gap-3 justify-end mt-16">
                    <flux:button variant="primary" type="button" size="sm"
                        x-on:click="$dispatch('close-modal', 'update-financial-table')"
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

    {{-- Delete Financial table Modal --}}
    <x-delete-modal name="delete-financial-table" header="Conferma eliminazione provvigione"
        function="deleteFinancialTable"
        message="Sei sicuro di voler eliminare la provvigione <strong>{{ $selectedFinancialTable?->percentage }}</strong>?" />
</div>
