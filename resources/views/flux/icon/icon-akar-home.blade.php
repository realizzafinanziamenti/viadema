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

<svg xmlns="http://www.w3.org/2000/svg" width="16.568" height="16.891" viewBox="0 0 16.568 16.891" {{ $attributes->class($classes) }} data-flux-icon aria-hidden="true">
<g id="Icon_akar-home" data-name="Icon akar-home" transform="translate(0.449 0.564)"><path id="Icon_akar-home-2" data-name="Icon akar-home" d="M20.169,18.277V12.416a3.482,3.482,0,0,0-1.084-2.524L13.534,4.619a1.741,1.741,0,0,0-2.4,0L5.584,9.891A3.482,3.482,0,0,0,4.5,12.416v5.861a1.741,1.741,0,0,0,1.741,1.741H18.428A1.741,1.741,0,0,0,20.169,18.277Z" transform="translate(-4.5 -4.141)" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="0.9"/></g>
</svg>
