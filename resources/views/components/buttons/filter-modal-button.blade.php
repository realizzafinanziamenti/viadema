@props(['name' => 'filter-modal'])

<flux:button
    {{ $attributes->merge(['class' => 'flex items-center justify-center w-full bg-orange-custom! hover:bg-orange-custom-hover! text-white! border-orange-custom!']) }}
    x-on:click="$dispatch('open-modal', '{{ $name }}')">
    <x-icons.ion-filter />
    Filtra
</flux:button>
