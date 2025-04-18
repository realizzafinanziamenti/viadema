@props([
    'expandable' => false,
    'expanded' => true,
    'heading' => null,
    'customIcon' => null,
])

<?php if ($expandable && $heading): ?>

<ui-disclosure {{ $attributes->class('group/disclosure mb-3') }} @if ($expanded === true) open @endif
    data-flux-navlist-group>
    <button type="button"
        class="group/disclosure-button mb-[2px] flex gap-x-2 h-9 w-full items-center text-black-custom hover:text-azure-custom px-1">

        @if ($customIcon)
            <x-dynamic-component :component="'icons.' . $customIcon" />
        @endif

        <span class="text-sm">{{ $heading }}</span>

        <div>
            <flux:icon.chevron-down class="hidden size-3! group-data-open/disclosure-button:block" />
            <flux:icon.chevron-right class="block size-3! group-data-open/disclosure-button:hidden" />
        </div>
    </button>

    <div class="relative hidden space-y-[2px] ps-7 data-open:block" @if ($expanded === true) data-open @endif>

        {{ $slot }}
    </div>
</ui-disclosure>

<?php elseif ($heading): ?>

<div {{ $attributes->class('block space-y-[2px] mb-3') }}>
    <div class="h-8 w-full flex items-center">
        <div class="text-xs font-extrabold text-blue-custom uppercase hover:text-azure-custom">{{ $heading }}</div>
    </div>

    <div>
        {{ $slot }}
    </div>
</div>

<?php else: ?>

<div {{ $attributes->class('block space-y-[2px] mb-3') }}>
    {{ $slot }}
</div>

<?php endif; ?>
