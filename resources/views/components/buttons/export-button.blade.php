@props(['size' => 'base', 'disabled' => false])

<flux:button size="{{ $size }}" type="button" :disabled="$disabled"
    {{ $attributes->merge(['class' => 'bg-orange-custom! hover:bg-orange! hover:border-orange! border-orange-custom! text-white!']) }}>
    <div class="flex items-center gap-2">
        <x-icons.icona-esporta />
        Esporta
    </div>
</flux:button>
