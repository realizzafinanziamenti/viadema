@props(['label' => '', 'clickable' => false, 'icon' => null, 'iconClass' => '', 'height' => 'h-12'])

<th scope="col" {{ $attributes->merge(['class' => $height . ' px-2 truncate font-medium text-[13px]']) }}
    title="{{ $label }}">
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
