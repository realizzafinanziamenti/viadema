@props([
    'route' => '#',
    'routeIs' => null,
    'label' => null,
    'bullet' => false,
    'activeWhenSlug' => null,
])

@php
    $currentSlug = request()->route('type');

    $isActive = $activeWhenSlug === $currentSlug || ($activeWhenSlug === null && $currentSlug === null);

    $isActive = request()->routeIs($routeIs . '.*') || request()->routeIs($routeIs);

    $activeClass =
        'flex items-center bg-white text-azure-custom text-sm my-0.5 truncate h-9 rounded-sm ' .
        ($bullet ? 'gap-x-1' : 'px-1 gap-x-2');
    $inactiveClass =
        'flex items-center text-black-custom text-sm truncate my-0.5 hover:text-azure-custom h-9 rounded-sm ' .
        ($bullet ? 'gap-x-1' : 'px-1 gap-x-2');
@endphp

<a href="{{ is_array($route) ? route($route[0], $route[1]) : route($route) }}" wire:navigate
    class="{{ $isActive ? $activeClass : $inactiveClass }}">

    @if ($bullet)
        <span>•</span>
    @endif

    {{ $slot }}

    <span class="inline-block align-bottom">
        {{ $label }}
    </span>
</a>
