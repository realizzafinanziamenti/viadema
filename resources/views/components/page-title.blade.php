@props(['label'])

<h3 {{ $attributes->merge(['class' => 'font-bold text-gray-custom-4 text-xl mb-5']) }}>
    {{ $label }}
</h3>
