@props([
    'selected' => null, // valore effettivamente selezionato
    'placeholder' => '',
    'selectableItems' => [],
    'emptySelectableItems' => 'Niente da selezionare',
    'setFunction' => '',
    'width' => 'w-full',
    'searchable' => false,
    'search' => '',
    'hasError' => false,
    'align' => 'right',
    'size' => '',
])

@php
    $label =
        $selected !== null && array_key_exists($selected, $selectableItems)
            ? $selectableItems[$selected]
            : $placeholder;
@endphp

<x-dropdown align="{{ $align }}" width="{{ $width }}">
    <x-slot name="trigger">
        <x-dropdown-trigger-button size="{{ $size }}"
            class="{{ $width }} {{ $hasError ? ' border-red-600 focus:border-red-600 focus:ring-red-500' : '' }}">
            <span class="truncate">
                {{ $label }}
            </span>

            @if ($selected)
                <flux:icon.x-mark wire:click.stop="{{ $setFunction }}"
                    class="cursor-pointer hover:text-red-600 size-3.5" />
            @else
                <flux:icon.chevron-down class="size-3" />
            @endif
        </x-dropdown-trigger-button>
    </x-slot>

    <x-slot name="content">
        @if ($searchable)
            <flux:input size="sm" placeholder="{{ $placeholder }}"
                wire:model.live.debounce.500ms="{{ $search }}" icon:trailing="magnifying-glass"
                x-on:click.stop.prevent="true" />
        @endif

        <div class="overflow-y-auto max-h-56">
            @if (count($selectableItems) > 0)
                @foreach ($selectableItems as $key => $value)
                    <x-dropdown-button size="{{ $size }}"
                        wire:click="{{ $setFunction }}('{{ $key }}')">
                        {{ $value }}
                    </x-dropdown-button>
                @endforeach
            @else
                <x-dropdown-button>
                    {{ $emptySelectableItems }}
                </x-dropdown-button>
            @endif
        </div>
    </x-slot>
</x-dropdown>
