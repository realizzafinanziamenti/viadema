@props(['label' => '', 'clickable' => false, 'icon' => null, 'iconClass' => ''])

<th scope="col" {{ $attributes->merge(['class' => 'h-10 px-2 truncate font-medium']) }} title="{{ $label }}">
    @if ($icon)
        <div class="flex items-center gap-1">
    @endif
    <span class="{{ $clickable ? 'cursor-pointer underline' : '' }}">{{ $label }}</span>

    <span class="{{ $iconClass }}">
        {{ $icon }}
    </span>
    @if ($icon)
        </div>
    @endif

    {{ $slot }}
</th>
