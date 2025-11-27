@props(['truncate' => false, 'label' => '', 'height' => 'h-14'])

@php
    $class = $truncate ? 'truncate' : '';
    $title = $truncate ? ['title' => trim($label)] : [];
@endphp

<td {{ $attributes->merge(array_merge(['class' => $height . ' px-3 font-medium ' . $class], $title)) }}>
    {{ $label ?? '' }}
    {{ $slot }}
</td>
