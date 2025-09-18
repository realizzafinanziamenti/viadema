@props([
    'selected' => false, // Default a false (No)
    'align' => 'right',
    'width' => 'w-full',
    'size' => '',
    'yesLabel' => null,
    'noLabel' => null,
    'yesAction' => '',
    'noAction' => '',
    'hasError' => false,
    'border' => false,
])

@php
    // Usa le traduzioni se non sono fornite label personalizzate
    $yesText = $yesLabel ?? 'Si';
    $noText = $noLabel ?? 'No';

    $selectableItems = [
        '1' => $yesText,
        '0' => $noText,
    ];

    // Converti il valore booleano in stringa per la select
    $selectedValue = $selected ? '1' : '0';

    // La label da mostrare (sempre presente)
    $label = $selectableItems[$selectedValue];
@endphp

<x-dropdown align="{{ $align }}" width="{{ $width }}">
    <x-slot name="trigger">
        <x-dropdown-trigger-button size="{{ $size }}"
            class="{{ $width }} {{ $hasError ? 'border-red-600! focus:border-red-600! focus:ring-red-500!' : '' }} {{ $border ? 'border!' : 'border-none!' }}">
            <span class="truncate">
                {{ $label }}
            </span>

            <flux:icon.chevron-down class="size-3" />
        </x-dropdown-trigger-button>
    </x-slot>

    <x-slot name="content">
        <div class="border border-gray-200 rounded-sm">
            <x-dropdown-button size="{{ $size }}" wire:click="{{ $yesAction }}">
                {{ $yesText }}
            </x-dropdown-button>
            <x-dropdown-button size="{{ $size }}" wire:click="{{ $noAction }}">
                {{ $noText }}
            </x-dropdown-button>
        </div>
    </x-slot>
</x-dropdown>
