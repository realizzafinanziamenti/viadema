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
            ($isActive ? 'text-pink-600 font-bold border-b-2 border-pink-500' : 'text-gray-500') .
            ($hasError ? ' border-red-600 focus:border-red-600 focus:ring-red-500 text-red-600' : ''),
    ]) }}>

    {{ $label }}
</span>
