@props([
    'name' => 'filter-modal',
    'header' => '',
    'submitFunction' => 'filter',
    'resetFunction' => 'resetFilter',
    'show' => false,
    'maxWidth' => 'lg',
])

<x-modal :name="$name" :show="$show" maxWidth="{{ $maxWidth }}" alignItems="items-start">
    <div class="flex flex-col">
        <x-modal-header :label="$header" class="mb-6" />

        <form wire:submit.prevent='{{ $submitFunction }}'>
            {{ $slot }}

            {{-- Buttons --}}
            <div class="flex gap-3 justify-end mt-16">
                <flux:button variant="primary" type="button" size="sm"
                    x-on:click="$dispatch('close-modal', '{{ $name }}')"
                    class="px-10 bg-gray-custom-2 border-gray-custom-2 text-gray-custom-5 hover:bg-gray-custom-3-hover hover:border-gray-custom-3-hover hover:text-white">
                    Annulla
                </flux:button>

                <flux:button variant="primary" type="button" size="sm" wire:click="{{ $resetFunction }}"
                    class="px-10 bg-gray-custom-2 border-gray-custom-2 text-gray-custom-5 hover:bg-gray-custom-3-hover hover:border-gray-custom-3-hover hover:text-white">
                    Reset
                </flux:button>

                <flux:button variant="primary" type="submit" size="sm"
                    class="px-10 bg-azure-custom border-azure-custom hover:bg-azure-custom-hover hover:border-azure-custom-hover">
                    Filtra
                </flux:button>
            </div>
        </form>

    </div>
</x-modal>
