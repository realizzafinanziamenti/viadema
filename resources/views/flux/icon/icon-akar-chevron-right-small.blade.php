@php $attributes = $unescapedForwardedAttributes ?? $attributes; @endphp

@props([
	'variant' => 'outline',
])

@php
$classes = Flux::classes('shrink-0')
->add(match($variant) {
	'outline' => '[:where(&)]:size-6',
	'solid' => '[:where(&)]:size-6',
	'mini' => '[:where(&)]:size-5',
	'micro' => '[:where(&)]:size-4',
});
@endphp

<svg xmlns="http://www.w3.org/2000/svg" width="4.678" height="7.854" viewBox="0 0 4.678 7.854" {{ $attributes->class($classes) }} data-flux-icon aria-hidden="true">
<g id="Icon_akar-chevron-right-small" data-name="Icon akar-chevron-right-small" transform="translate(1.061 1.061)"><path id="Icon_akar-chevron-right-small-2" data-name="Icon akar-chevron-right-small" d="M13.5,9l2.867,2.867L13.5,14.733" transform="translate(-13.5 -9)" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"/></g>
</svg>
