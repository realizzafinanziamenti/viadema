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

<svg xmlns="http://www.w3.org/2000/svg" width="17.342" height="17.334" viewBox="0 0 17.342 17.334" {{ $attributes->class($classes) }} data-flux-icon aria-hidden="true">
<g id="Icon_akar-star" data-name="Icon akar-star" transform="translate(0.467 0.449)"><path id="Icon_akar-star-2" data-name="Icon akar-star" d="M10.445,3.519a.815.815,0,0,1,1.52,0l1.7,4.7a.812.812,0,0,0,.76.519H18.6a.8.8,0,0,1,.5,1.43l-2.97,2.672a.794.794,0,0,0-.264.9l1.085,4.6a.809.809,0,0,1-1.237.918L11.676,16.7a.82.82,0,0,0-.944,0L6.7,19.257a.809.809,0,0,1-1.237-.918l1.085-4.6a.794.794,0,0,0-.264-.9l-2.97-2.672a.8.8,0,0,1,.5-1.43H7.986a.811.811,0,0,0,.76-.519l1.7-4.7Z" transform="translate(-3 -3)" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="0.9"/></g>
</svg>
