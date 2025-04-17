<a href="{{ route($route) }}" wire:navigate
    class="{{ request()->routeIs($routeIs . '.*') || request()->routeIs($routeIs)
        ? 'my-1 flex items-center px-4 bg-aqua-0 text-aqua-2 text-sm h-sidebar-button rounded-sm'
        : 'my-1 flex items-center px-4 hover:bg-aqua-0 text-black-custom text-sm hover:text-aqua-2 h-sidebar-button rounded-sm' }}">

    {{ $slot }}
    <span class="inline-block align-bottom ms-3">
        {{ $label }}
    </span>
</a>
