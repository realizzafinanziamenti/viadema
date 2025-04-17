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

<svg xmlns="http://www.w3.org/2000/svg" width="14.025" height="17.309" viewBox="0 0 14.025 17.309" {{ $attributes->class($classes) }} data-flux-icon aria-hidden="true">
<g id="Icon_akar-draft" data-name="Icon akar-draft" transform="translate(0.449 0.449)"><path id="Tracciato_147" data-name="Tracciato 147" d="M16.513,6.282V9.564h2.051M17.485,3H13.641A1.641,1.641,0,0,0,12,4.641v9.846a1.641,1.641,0,0,0,1.641,1.641H20.2a1.641,1.641,0,0,0,1.641-1.641V7.3a1.641,1.641,0,0,0-.494-1.173l-2.72-2.66A1.641,1.641,0,0,0,17.485,3Z" transform="translate(-8.719 -3)" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="0.9"/><path id="Tracciato_148" data-name="Tracciato 148" d="M15.846,19.525v1.641A1.641,1.641,0,0,1,14.2,22.807H7.641A1.641,1.641,0,0,1,6,21.166V12.141A1.641,1.641,0,0,1,7.641,10.5H9.282" transform="translate(-6 -6.398)" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="0.9"/></g>
</svg>
