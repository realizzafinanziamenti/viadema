@props(['label' => ''])

<div {{ $attributes->merge(['class' => 'text-gray-custom-5 font-bold text-lg leading-5']) }}>
    {{ $label }}
</div>
