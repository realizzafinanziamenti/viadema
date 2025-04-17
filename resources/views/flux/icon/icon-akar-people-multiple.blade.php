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

<svg xmlns="http://www.w3.org/2000/svg" width="17.012" height="15.9" viewBox="0 0 17.012 15.9" {{ $attributes->class($classes) }} data-flux-icon aria-hidden="true">
<g id="Icon_akar-people-multiple" data-name="Icon akar-people-multiple" transform="translate(0.453 0.449)"><path id="Tracciato_61" data-name="Tracciato 61" d="M11,7A2.5,2.5,0,1,1,8.5,4.5,2.5,2.5,0,0,1,11,7Z" transform="translate(-4.613 -4.5)" fill="none" stroke="currentColor" stroke-width="0.9"/><path id="Tracciato_62" data-name="Tracciato 62" d="M9.891,19.5H6.406a2.5,2.5,0,0,0-2.47,2.114l-.412,2.628a1.667,1.667,0,0,0,1.648,1.925H9.057m10.533-1.924-.412-2.628a2.5,2.5,0,0,0-2.47-2.114h-1.97a2.5,2.5,0,0,0-2.47,2.114l-.411,2.628A1.667,1.667,0,0,0,13.5,26.167h4.438a1.667,1.667,0,0,0,1.647-1.924Z" transform="translate(-3.504 -11.166)" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="0.9"/><path id="Tracciato_63" data-name="Tracciato 63" d="M26,7a2.5,2.5,0,1,1-2.5-2.5A2.5,2.5,0,0,1,26,7Z" transform="translate(-11.279 -4.5)" fill="none" stroke="currentColor" stroke-width="0.9"/></g>
</svg>
