@props(['name', 'header', 'message', 'function'])

<flux:modal :name="$name" class="max-w-md flex flex-col items-center p-10" :closable="false">
    <div class="text-red-600 border-2 border-red-600 rounded-full flex items-center justify-center w-12 h-12">
        <x-icons.icon-akar-trash-can />
    </div>

    <x-modal-header :label="$header" class="mt-5" />

    <div class="text-sm text-gray-custom-4 text-center mt-5">
        {!! $message !!}
    </div>

    {{-- Buttons --}}
    <div class="grid justify-items-stretch gap-3 mt-6">
        <flux:modal.close name="delete-user">
            <flux:button variant="primary" type="button" size="sm"
                class="px-10 w-full bg-gray-custom-2 border-gray-custom-2 text-gray-custom-5 hover:bg-gray-custom-3-hover hover:border-gray-custom-3-hover hover:text-white">
                Annulla
            </flux:button>
        </flux:modal.close>

        <flux:button variant="primary" type="button" size="sm" wire:click='{{ $function }}'
            class="px-10 w-full bg-red-600 border-red-600 hover:bg-red-800 hover:border-red-800">
            Procedi ed elimina
        </flux:button>
    </div>
</flux:modal>
