@props(['size' => 'base', 'label' => 'Aggiungi'])

<flux:button icon="plus" size="{{ $size }}" type="button"
    {{ $attributes->merge(['class' => 'bg-blue-custom! hover:bg-blue-custom-hover! border-blue-custom! text-white! ']) }}>
    {{ $label }}
</flux:button>
