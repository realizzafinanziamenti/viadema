<div>
    <x-card class="max-w-3xl mx-auto">
        <x-card-header class="mb-3" label="Gestione tabella provvigionale" />

        <div class="flex justify-end items-center mb-5">
            @can('create financial tables')
                <x-buttons.create-button size="sm" px="px-6" label="Crea provvigione"
                    wire:click="openCreateFinancialTableModal" />
            @endcan
        </div>

        <x-table minWidth="max-content">
            {{-- Table Header --}}
            <x-slot name="header" class="border-b">
                <x-table-header label="Valore provvigione" />

                <x-table-header class="w-[100px]">
                    {{-- Actions --}}
                </x-table-header>
            </x-slot>

            {{-- Table body --}}
            @foreach ($financialTables as $index => $financialTable)
                <tr wire:key='{{ $financialTable->id }}' class="border-y border-collapse">
                    <x-table-data label="{{ $financialTable->percentage }}" />

                    {{-- Actions --}}
                    <x-table-data>
                        <div class="flex items-center justify-end w-full gap-3">
                            @can('delete', $financialTable)
                                <x-table-action-button-delete
                                    wire:click="selectFinancialTableForDelete({{ $financialTable->id }})"
                                    class="btn btn-danger">Elimina</x-table-action-button-delete>
                            @endcan
                        </div>
                    </x-table-data>
                </tr>
            @endforeach
        </x-table>

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
                    <x-forms.input-with-symbol type="number" min="0.00" max="10000.00" step=".01"
                        size="sm" wire:model="percentage" symbol="%" placeholder="Inserisci provvigione" />
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

    {{-- Delete Financial table Modal --}}
    <x-delete-modal name="delete-financial-table" header="Conferma eliminazione provvigione"
        function="deleteFinancialTable"
        message="Sei sicuro di voler eliminare la provvigione <strong>{{ $selectedFinancialTable?->percentage }}</strong>?" />
</div>
