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

<svg xmlns="http://www.w3.org/2000/svg" width="16.086" height="15.898" viewBox="0 0 16.086 15.898" {{ $attributes->class($classes) }} data-flux-icon aria-hidden="true">
<g id="Icon_akar-statistic-up" data-name="Icon akar-statistic-up" transform="translate(0.449 0.449)"><path id="Tracciato_82" data-name="Tracciato 82" d="M4.5,4.5V17.833A1.667,1.667,0,0,0,6.167,19.5H19.5" transform="translate(-4.5 -4.5)" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="5.759" stroke-width="0.9"/><path id="Tracciato_83" data-name="Tracciato 83" d="M10.5,17l3.333-3.333L17.167,17l5-5" transform="translate(-7.166 -7.834)" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="5.759" stroke-width="0.9"/><path id="Tracciato_84" data-name="Tracciato 84" d="M27,12h2.5v2.5" transform="translate(-14.5 -7.834)" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="0.9"/></g>
</svg>
