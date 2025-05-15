@props([
    'model' => '', // wire:model for select
    'selectableItems' => [], // array [value => label]
    'placeholder' => 'Seleziona',
    'emptySelectableItems' => 'Niente da selezionare',
    'width' => 'w-full',
    'searchable' => false,
    'searchModel' => '', // wire:model for search
    'hasError' => false,
    'align' => 'right',
    'size' => '',
])

@php
    $selectedValue = old($model) ?? data_get($this, $model);
    $label =
        $selectedValue !== null && array_key_exists($selectedValue, $selectableItems)
            ? $selectableItems[$selectedValue]
            : $placeholder;
@endphp

<div x-data="{ value: @entangle($model).defer }">
    <input type="hidden" wire:model="{{ $model }}" x-model="value" />

    <x-dropdown align="{{ $align }}" width="{{ $width }}">
        <x-slot name="trigger">
            <x-dropdown-trigger-button size="{{ $size }}"
                class="{{ $width }} {{ $hasError ? 'border-red-600 focus:border-red-600 focus:ring-red-500' : '' }}">
                <span>{{ $label }}</span>

                @if ($selectedValue)
                    <flux:icon.x-mark @click.stop="value = null; $wire.set('{{ $model }}', null);"
                        class="cursor-pointer hover:text-red-600 size-3.5" />
                @else
                    <flux:icon.chevron-down class="size-3" />
                @endif
            </x-dropdown-trigger-button>
        </x-slot>

        <x-slot name="content">
            @if ($searchable)
                <flux:input size="sm" placeholder="{{ $placeholder }}"
                    wire:model.live.debounce.500ms="{{ $searchModel }}" icon:trailing="magnifying-glass"
                    x-on:click.stop.prevent="true" />
            @endif

            <div class="overflow-y-auto max-h-56">
                @forelse ($selectableItems as $key => $value)
                    <x-dropdown-button size="{{ $size }}"
                        @click="
                            value = '{{ $key }}';
                            $wire.set('{{ $model }}', {{ $key }});
                        ">
                        {{ $value }}
                    </x-dropdown-button>
                @empty
                    <x-dropdown-button>{{ $emptySelectableItems }}</x-dropdown-button>
                @endforelse
            </div>
        </x-slot>
    </x-dropdown>
</div>
