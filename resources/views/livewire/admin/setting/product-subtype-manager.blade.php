<div>
    <x-card class="max-w-3xl mx-auto">
        <x-card-header class="mb-6" label="Gestione tipo prodotti" />

        <div class="flex justify-end items-center mb-4">
            @can('create product subtypes')
                <x-buttons.create-button size="sm" px="px-6" label="Crea nuovo tipo prodotto"
                    wire:click="openCreateProductSubtypeModal" />
            @endcan
        </div>

        @foreach ($productSubtypes as $index => $subtype)
            <div
                class="w-full flex justify-between items-center truncate py-2 px-3
                    text-sm bg-white border-b {{ $index === 0 ? 'border-t' : '' }} border-zinc-200 text-zinc-500">
                <span>{{ $subtype->name }}</span>

                <div class="flex space-x-2">
                    @if ($subtype->isEditable())
                        @can('update', $subtype)
                            <x-table-action-button-edit wire:click="selectProductSubtypeForUpdate({{ $subtype->id }})"
                                class="btn btn-primary">Modifica</x-table-action-button-edit>
                        @endcan
                    @endif

                    @can('delete', $subtype)
                        <x-table-action-button-delete wire:click="selectProductSubtypeForDelete({{ $subtype->id }})"
                            class="btn btn-danger">Elimina</x-table-action-button-delete>
                    @endcan
                </div>
            </div>
        @endforeach

        {{-- Pagination --}}
        <div class="mt-5">
            {{ $productSubtypes->links() }}
        </div>
    </x-card>

    {{-- Create Product Subtype Modal --}}
    <x-modal name="create-product-subtype">
        <div class="flex flex-col">
            <x-modal-header label="Crea tipo prodotto" class="mb-6" />

            <form wire:submit.prevent='createProductSubtype'>
                {{-- Product Subtype Name --}}
                <div class="flex flex-col gap-1.5">
                    <flux:label>Nome tipo prodotto</flux:label>
                    <flux:input size="sm" placeholder="Inserisci nome tipo prodotto" wire:model='name' />
                    <flux:error name="name" />
                </div>

                {{-- Buttons --}}
                <div class="flex gap-3 justify-end mt-16">
                    <flux:button variant="primary" type="button" size="sm"
                        x-on:click="$dispatch('close-modal', 'create-product-subtype')"
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

    {{-- Update Product Subtype Modal --}}
    <x-modal name="update-product-subtype">
        <div class="flex flex-col">
            <x-modal-header label="Modifica tipo prodotto" class="mb-6" />

            <form wire:submit.prevent='updateProductSubtype'>
                {{-- Product Subtype Name --}}
                <div class="flex flex-col gap-1.5">
                    <flux:label>Nome tipo prodotto</flux:label>
                    <flux:input size="sm" placeholder="Inserisci nome tipo prodotto" wire:model='name' />
                    <flux:error name="name" />
                </div>

                {{-- Buttons --}}
                <div class="flex gap-3 justify-end mt-16">
                    <flux:button variant="primary" type="button" size="sm"
                        x-on:click="$dispatch('close-modal', 'update-product-subtype')"
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

    {{-- Delete Product Subtype Modal --}}
    <x-delete-modal name="delete-product-subtype" header="Conferma eliminazione tipo prodotto"
        function="deleteProductSubtype"
        message="Sei sicuro di voler eliminare il tipo prodotto <strong>{{ $selectedProductSubtype?->name }}</strong>?" />
</div>
