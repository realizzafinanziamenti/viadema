<td {{ $attributes->merge(['class' => 'px-2 h-14']) }}>
    {{ $label ?? '' }}
    {{ $slot }}
</td>
