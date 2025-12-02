@props(['icon', 'label' => null])

<button type="button"
    {{ $attributes->merge(['class' => 'flex items-center gap-x-0.5 text-[10px] font-bold text-gray-custom-4 cursor-pointer']) }}>

    @if ($icon)
        {{ $icon }}
    @endif

    <span>{{ $label }}</span>
</button>
