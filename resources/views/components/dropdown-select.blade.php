@props([
    'model' => '', // wire:model for select
    'selectableItems' => [], // array [value => label]
    'placeholder' => 'Seleziona',
    'emptySelectableItems' => 'Niente da selezionare',
    'width' => 'w-full',
    'searchable' => false,
    'searchModel' => '', // wire:model for search
    'hasError' => false,
])

<input type="hidden" wire:model="{{ $model }}" x-model="value" />

@php
    $selectedValue = old($model) ?? data_get($this, $model);
    $initialLabel =
        $selectedValue !== null && array_key_exists($selectedValue, $selectableItems)
            ? $selectableItems[$selectedValue]
            : null;
@endphp

<div x-data="{ value: '{{ $selectedValue }}', label: '{{ $initialLabel ?? $placeholder }}' }">
    <x-dropdown align="right" width="{{ $width }}">
        <x-slot name="trigger">
            <x-dropdown-trigger-button
                class="{{ $width }} {{ $hasError ? 'border-red-600 focus:border-red-600 focus:ring-red-500' : '' }}">
                <span x-text="label"></span>

                <template x-if="value">
                    <flux:icon.x-circle
                        @click.stop="value = null; label = '{{ $placeholder }}'; $wire.set('{{ $model }}', null);"
                        class="cursor-pointer hover:text-red-600" />
                </template>
                <template x-if="!value">
                    <flux:icon.chevron-down />
                </template>
            </x-dropdown-trigger-button>
        </x-slot>

        <x-slot name="content">
            @if ($searchable)
                <flux:input class="{{ $width }}!" wire:model.live.debounce.500ms="{{ $searchModel }}"
                    icon:trailing="magnifying-glass" x-on:click.stop />
            @endif

            <div class="overflow-y-auto max-h-56">
                @forelse ($selectableItems as $key => $value)
                    <x-dropdown-button
                        @click="
                            value = '{{ $key }}';
                            label = '{{ $value }}';
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
