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

<svg xmlns="http://www.w3.org/2000/svg" width="17.193" height="9.348" viewBox="0 0 17.193 9.348" {{ $attributes->class($classes) }} data-flux-icon aria-hidden="true">
<g id="Icon_akar-chevron-left-small" data-name="Icon akar-chevron-left-small" transform="translate(1.061 1.061)"><path id="Icon_akar-chevron-left-small-2" data-name="Icon akar-chevron-left-small" d="M19.536,9,12,16.536l7.536,7.536" transform="translate(-9 19.537) rotate(-90)" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"/></g>
</svg>
