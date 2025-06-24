@props([
    'practiceCount' => 0,
    'countLabel' => 'Totale pratiche',
    'buttonLabel' => 'Vedi pratiche',
    'color' => '',
    'href' => '#',
])

@php
    $cssColorContainer = match ($color) {
        'green' => 'border-green-custom text-green-custom',
        'oil' => 'border-oil-custom text-oil-custom',
        'blue' => 'border-blue-custom text-blue-custom',
        default => 'border-black-custom text-black-custom',
    };

    $cssColorButton = match ($color) {
        'green'
            => 'bg-green-custom hover:bg-green-custom-light text-white hover:text-green-custom border border-green-custom',
        'oil' => 'bg-oil-custom hover:bg-oil-custom-light text-white hover:text-oil-custom border border-oil-custom',
        'blue'
            => 'bg-blue-custom hover:bg-blue-custom-light text-white hover:text-blue-custom border border-blue-custom',
        default
            => 'bg-white hover:bg-gray-custom-2 text-black-custom hover:text-white border border-black-custom hover:border-gray-custom-2',
    };
@endphp

<div class="border {{ $cssColorContainer }} h-1/3 rounded-lg flex items-center justify-between gap-4 px-3">
    {{-- Right --}}
    <div class="flex flex-col truncate">
        <span class="text-xl font-semibold">{{ $practiceCount }}</span>
        <span class="text-xs truncate">{{ $countLabel }}</span>
    </div>

    {{-- Left --}}
    <div class="w-[120px] shrink-0">
        <a href="{{ $href }}" wire:navigate class="w-full">
            <x-dashboard.dashboard-button label="{{ $buttonLabel }}" class="{{ $cssColorButton }}" />
        </a>
    </div>
</div>
