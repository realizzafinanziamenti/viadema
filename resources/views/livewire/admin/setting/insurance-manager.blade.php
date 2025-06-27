<div>
    <x-card class="max-w-3xl mx-auto">
        <x-card-header class="mb-3" label="Gestione assicurazioni" />

        <div class="flex justify-end items-center mb-5">
            @can('create insurances')
                <x-buttons.create-button size="sm" px="px-6" label="Crea assicurazione"
                    wire:click="openCreateInsuranceModal" />
            @endcan
        </div>

        <x-table minWidth="max-content">
            {{-- Table Header --}}
            <x-slot name="header" class="border-b">
                <x-table-header label="Nome assicurazione" />

                <x-table-header class="w-[100px]">
                    {{-- Actions --}}
                </x-table-header>
            </x-slot>

            {{-- Table body --}}
            @foreach ($insurances as $index => $insurance)
                <tr wire:key='{{ $insurance->id }}' class="border-y border-collapse">
                    <x-table-data label="{{ $insurance->name }}" />

                    <x-table-data>
                        <div class="flex items-center justify-end w-full gap-3">
                            @if ($insurance->isEditable())
                                @can('update', $insurance)
                                    <x-table-action-button-edit wire:click="selectInsuranceForUpdate({{ $insurance->id }})"
                                        class="btn btn-primary">Modifica</x-table-action-button-edit>
                                @endcan
                            @endif

                            @can('delete', $insurance)
                                <x-table-action-button-delete wire:click="selectInsuranceForDelete({{ $insurance->id }})"
                                    class="btn btn-danger">Elimina</x-table-action-button-delete>
                            @endcan
                        </div>
                    </x-table-data>
                </tr>
            @endforeach
        </x-table>

        {{-- Pagination --}}
        <div class="mt-5">
            {{ $insurances->links() }}
        </div>
    </x-card>

    {{-- Create Insurance Modal --}}
    <x-modal name="create-insurance">
        <div class="flex flex-col">
            <x-modal-header label="Crea assicurazione" class="mb-6" />

            <form wire:submit.prevent='createInsurance'>
                {{-- Insurance Name --}}
                <div class="flex flex-col gap-1.5">
                    <flux:label>Assicurazione</flux:label>
                    <flux:input size="sm" placeholder="Inserisci assicurazione" wire:model='name' />
                    <flux:error name="name" />
                </div>

                {{-- Buttons --}}
                <div class="flex gap-3 justify-end mt-16">
                    <flux:button variant="primary" type="button" size="sm"
                        x-on:click="$dispatch('close-modal', 'create-insurance')"
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

    {{-- Update Insurance Modal --}}
    <x-modal name="update-insurance">
        <div class="flex flex-col">
            <x-modal-header label="Modifica assicurazione" class="mb-6" />

            <form wire:submit.prevent='updateInsurance'>
                {{-- Insurance Name --}}
                <div class="flex flex-col gap-1.5">
                    <flux:label>Nome assicurazione</flux:label>
                    <flux:input size="sm" placeholder="Inserisci nome assicurazione" wire:model='name' />
                    <flux:error name="name" />
                </div>

                {{-- Buttons --}}
                <div class="flex gap-3 justify-end mt-16">
                    <flux:button variant="primary" type="button" size="sm"
                        x-on:click="$dispatch('close-modal', 'update-insurance')"
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

    {{-- Delete Insurance Modal --}}
    <x-delete-modal name="delete-insurance" header="Conferma eliminazione assicurazione" function="deleteInsurance"
        message="Sei sicuro di voler eliminare l'assicurazione <strong>{{ $selectedInsurance?->name }}</strong>?" />
</div>
