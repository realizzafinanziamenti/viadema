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

<svg xmlns="http://www.w3.org/2000/svg" width="12.898" height="15.898" viewBox="0 0 12.898 15.898" {{ $attributes->class($classes) }} data-flux-icon aria-hidden="true">
<g id="Icon_akar-paper" data-name="Icon akar-paper" transform="translate(0.449 0.449)"><path id="Tracciato_39" data-name="Tracciato 39" d="M6,4.5v12A1.5,1.5,0,0,0,7.5,18h9A1.5,1.5,0,0,0,18,16.5V7.756a1.5,1.5,0,0,0-.451-1.072l-3.33-3.257A1.5,1.5,0,0,0,13.17,3H7.5A1.5,1.5,0,0,0,6,4.5Z" transform="translate(-6 -3)" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="0.9"/><path id="Tracciato_40" data-name="Tracciato 40" d="M21,3V6.616a1.835,1.835,0,0,0,1.86,1.808h3.721" transform="translate(-14.582 -3)" fill="none" stroke="currentColor" stroke-linejoin="round" stroke-width="0.9"/></g>
</svg>
