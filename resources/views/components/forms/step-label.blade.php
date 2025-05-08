@props([
    'step' => null,
    'currentStep' => null,
    'label' => '',
    'hasError' => false,
])

@php
    $isActive = $step === $currentStep;
@endphp

<span
    {{ $attributes->merge([
        'class' =>
            'text-sm ' . ($isActive ? 'text-pink-custom font-bold border-b-2 border-pink-custom' : 'text-gray-custom-3'),
    ]) }}>

    {{ $label }}
</span>
