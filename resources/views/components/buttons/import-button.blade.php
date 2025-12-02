@props(['size' => 'base'])

<flux:button size="{{ $size }}" type="button"
    {{ $attributes->merge(['class' => 'bg-black-custom! hover:bg-zinc-700! hover:border-zinc-700! border-black-custom! text-white!']) }}>
    <div class="flex items-center gap-2">
        <x-icons.icona-importa />
        Importa
    </div>
</flux:button>
