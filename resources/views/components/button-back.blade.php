@props([
    'route' => '',
    'label' => 'Indietro',
])

<a wire:navigate
    {{ $attributes->merge([
        'href' => route($route),
        'class' => 'text-sm text-gray-custom-4 flex items-center gap-x-0.5 hover:text-gray-custom-3',
    ]) }}>

    <flux:icon.chevron-left variant="micro" />
    {{ $label }}
</a>
