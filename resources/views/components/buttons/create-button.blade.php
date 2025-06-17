@props(['label', 'size' => 'base', 'px' => 'px-10'])

<flux:button icon="plus" size="{{ $size }}" type="button"
    {{ $attributes->merge(['class' => 'bg-blue-custom! hover:bg-blue-custom-hover! border-blue-custom! text-white! ' . $px]) }}>
    {{ $label }}
</flux:button>
