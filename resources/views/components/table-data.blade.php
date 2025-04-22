@props(['truncate' => false, 'label' => ''])

@php
    $class = $truncate ? 'truncate' : '';
    $title = $truncate ? ['title' => trim($label)] : [];
@endphp

<td {{ $attributes->merge(array_merge(['class' => 'px-2 h-14 ' . $class], $title)) }}>
    {{ $label ?? '' }}
    {{ $slot }}
</td>
