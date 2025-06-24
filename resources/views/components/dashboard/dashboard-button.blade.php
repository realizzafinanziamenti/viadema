@props([
    'label' => 'Vedi pratiche',
])

<button {{ $attributes->merge(['class' => 'cursor-pointer px-2 py-1 rounded-md w-full text-xs font-semibold']) }}>
    {{ $label }}
</button>
