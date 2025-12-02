@props(['label' => null])

<div {{ $attributes->merge(['class' => 'text-black-custom font-bold text-base leading-5']) }}>
    {{ $label }}
</div>
