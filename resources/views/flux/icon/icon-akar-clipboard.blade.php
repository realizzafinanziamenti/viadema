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

<svg xmlns="http://www.w3.org/2000/svg" width="14.041" height="16.506" viewBox="0 0 14.041 16.506" {{ $attributes->class($classes) }} data-flux-icon aria-hidden="true">
<g id="Icon_akar-clipboard" data-name="Icon akar-clipboard" transform="translate(0.449 0.449)"><path id="Tracciato_87" data-name="Tracciato 87" d="M15.446,6H17.5a1.643,1.643,0,0,1,1.643,1.643V18.321A1.643,1.643,0,0,1,17.5,19.964H7.643A1.643,1.643,0,0,1,6,18.321V7.643A1.643,1.643,0,0,1,7.643,6H9.7" transform="translate(-6 -4.357)" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="0.9"/><path id="Tracciato_88" data-name="Tracciato 88" d="M12.51,4.244A1.643,1.643,0,0,1,14.1,3h2.363A1.643,1.643,0,0,1,18.06,4.244l.511,2.041H12Z" transform="translate(-8.715 -3)" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="0.9"/><path id="Tracciato_89" data-name="Tracciato 89" d="M13.5,18h4.928M13.5,21.286h4.928" transform="translate(-9.393 -9.785)" fill="none" stroke="currentColor" stroke-linecap="round" stroke-width="0.9"/></g>
</svg>
