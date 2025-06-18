<div>
    <x-card class="max-w-3xl mx-auto">
        <x-card-header class="mb-6" label="Gestione assicurazioni" />

        <div class="flex justify-end items-center mb-4">
            @can('create insurances')
                <x-buttons.create-button size="sm" px="px-6" label="Crea nuova assicurazione"
                    wire:click="openCreateInsuranceModal" />
            @endcan
        </div>

        @foreach ($insurances as $index => $insurance)
            <div
                class="w-full flex justify-between items-center truncate py-2 px-3
                    text-sm bg-white border-b {{ $index === 0 ? 'border-t' : '' }} border-zinc-200 text-zinc-500">
                <span>{{ $insurance->name }}</span>

                <div class="flex space-x-2">
                    @can('update', $insurance)
                        <x-table-action-button-edit wire:click="selectInsuranceForUpdate({{ $insurance->id }})"
                            class="btn btn-primary">Modifica</x-table-action-button-edit>
                    @endcan

                    @can('delete', $insurance)
                        <x-table-action-button-delete wire:click="selectInsuranceForDelete({{ $insurance->id }})"
                            class="btn btn-danger">Elimina</x-table-action-button-delete>
                    @endcan
                </div>
            </div>
        @endforeach

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
                    <flux:label>Nome assicurazione</flux:label>
                    <flux:input size="sm" placeholder="Inserisci nome assicurazione" wire:model='name' />
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
