<flux:button wire:click='openFilterModal' :loading="true"
    {{ $attributes->merge(['class' => 'flex items-center justify-center w-full bg-orange-custom! hover:bg-orange-custom-hover! text-white! border-orange-custom!']) }}>

    <div class="flex items-center justify-center gap-2">
        <x-icons.ion-filter />
        Filtra
    </div>
</flux:button>
