@props([
    'view' => 'month',
    'label' => 'Mese',
    'viewMode' => 'month',
])

<button wire:click="setViewMode('{{ $view }}')"
    {{ $attributes->merge([
        'type' => 'button',
        'class' =>
            'px-3 py-1.5 border-blue-custom cursor-pointer ' .
            ($viewMode === $view
                ? 'bg-blue-custom text-white hover:bg-blue-custom-hover'
                : 'text-blue-custom bg-white hover:bg-blue-custom hover:text-white'),
    ]) }}>
    {{ $label }}
</button>
