<flux:button wire:click='openFilterModal' :loading="false"
    {{ $attributes->merge(['class' => 'flex items-center justify-center w-full bg-orange-custom! hover:bg-orange-custom-hover! text-white! border-orange-custom!']) }}>
    <x-icons.ion-filter />
    Filtra
</flux:button>
