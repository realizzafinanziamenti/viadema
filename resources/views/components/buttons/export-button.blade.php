@props(['size' => 'base'])

<flux:button size="{{ $size }}" type="button"
    {{ $attributes->merge(['class' => 'bg-orange-custom! hover:bg-orange! hover:border-orange! border-orange-custom! text-white!']) }}>
    <div class="flex items-center gap-2">
        <x-icons.icona-esporta />
        Esporta
    </div>
</flux:button>
