@props(['label' => null])

<div {{ $attributes->merge(['class' => 'text-black-custom font-extrabold text-lg']) }}>
    {{ $label }}
</div>
