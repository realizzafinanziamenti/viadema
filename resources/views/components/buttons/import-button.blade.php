@props(['size' => 'base'])

<flux:button size="{{ $size }}" type="button"
    {{ $attributes->merge(['class' => 'bg-black-custom! hover:bg-zinc-700! hover:border-zinc-700! border-black-custom! text-white!']) }}>
    {{-- <input type="file" id="fileUpload" wire:model.live="importFile" class="hidden" accept=".xlsx,.xls" /> --}}

    {{-- <label for="fileUpload" class="flex items-center justify-center"> --}}

    <div class="flex items-center gap-2">
        <x-icons.icona-importa />
        Importa da Excel

        {{-- <div wire:loading wire:target="importFile" class="text-white">
                <flux:icon.loading class="size-4" />
            </div> --}}
    </div>
    {{-- </label> --}}
</flux:button>
