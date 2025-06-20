<div>
    <x-card class="max-w-3xl mx-auto">
        <x-card-header class="mb-6" label="Gestione numero rate" />

        <div class="flex justify-end items-center mb-4">
            @can('create installments')
                <x-buttons.create-button size="sm" px="px-6" label="Crea numero rate"
                    wire:click="openCreateInstallmentModal" />
            @endcan
        </div>

        @foreach ($installments as $index => $installment)
            <div
                class="w-full flex justify-between items-center truncate py-2 px-3
                    text-sm bg-white border-b {{ $index === 0 ? 'border-t' : '' }} border-zinc-200 text-zinc-500">
                <span>{{ $installment->value }}</span>

                <div class="flex space-x-2">
                    @if ($installment->isEditable())
                        @can('update', $installment)
                            <x-table-action-button-edit wire:click="selectInstallmentForUpdate({{ $installment->id }})"
                                class="btn btn-primary">Modifica</x-table-action-button-edit>
                        @endcan
                    @endif

                    @can('delete', $installment)
                        <x-table-action-button-delete wire:click="selectInstallmentForDelete({{ $installment->id }})"
                            class="btn btn-danger">Elimina</x-table-action-button-delete>
                    @endcan
                </div>
            </div>
        @endforeach

        {{-- Pagination --}}
        <div class="mt-5">
            {{ $installments->links() }}
        </div>
    </x-card>

    {{-- Create Installment Modal --}}
    <x-modal name="create-installment">
        <div class="flex flex-col">
            <x-modal-header label="Crea rate" class="mb-6" />

            <form wire:submit.prevent='createInstallment'>
                {{-- Installment Name --}}
                <div class="flex flex-col gap-1.5">
                    <flux:label>Numero rate</flux:label>
                    <flux:input size="sm" placeholder="Inserisci numero rate" wire:model='value' />
                    <flux:error name="value" />
                </div>

                {{-- Buttons --}}
                <div class="flex gap-3 justify-end mt-16">
                    <flux:button variant="primary" type="button" size="sm"
                        x-on:click="$dispatch('close-modal', 'create-installment')"
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

    {{-- Update Installment Modal --}}
    <x-modal name="update-installment">
        <div class="flex flex-col">
            <x-modal-header label="Modifica rate" class="mb-6" />

            <form wire:submit.prevent='updateInstallment'>
                {{-- Installment Name --}}
                <div class="flex flex-col gap-1.5">
                    <flux:label>Numero rate</flux:label>
                    <flux:input size="sm" placeholder="Inserisci numero rate" wire:model='value' />
                    <flux:error name="value" />
                </div>

                {{-- Buttons --}}
                <div class="flex gap-3 justify-end mt-16">
                    <flux:button variant="primary" type="button" size="sm"
                        x-on:click="$dispatch('close-modal', 'update-installment')"
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

    {{-- Delete Installment Modal --}}
    <x-delete-modal name="delete-installment" header="Conferma eliminazione rate" function="deleteInstallment"
        message="Sei sicuro di voler eliminare le rate <strong>{{ $selectedInstallment?->value }}</strong>?" />
</div>
