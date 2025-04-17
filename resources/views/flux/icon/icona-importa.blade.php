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

<svg xmlns="http://www.w3.org/2000/svg" width="14.934" height="12.844" viewBox="0 0 14.934 12.844" {{ $attributes->class($classes) }} data-flux-icon aria-hidden="true">
<g id="Icona_importa" data-name="Icona importa" transform="translate(0.5 0.5)"><path id="Tracciato_205" data-name="Tracciato 205" d="M2,7.18V4.393A1.393,1.393,0,0,1,3.393,3H6.11a1.393,1.393,0,0,1,1.177.627l.564.836a1.393,1.393,0,0,0,1.163.627H14.54a1.393,1.393,0,0,1,1.393,1.393V13.45a1.393,1.393,0,0,1-1.393,1.393H3.393A1.393,1.393,0,0,1,2,13.45v-.7" transform="translate(-2 -3)" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1"/><path id="Tracciato_206" data-name="Tracciato 206" d="M2,13H8.967" transform="translate(-2 -6.033)" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1"/><path id="Tracciato_207" data-name="Tracciato 207" d="M9,14.18l2.09-2.09L9,10" transform="translate(-4.123 -5.123)" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1"/></g>
</svg>
