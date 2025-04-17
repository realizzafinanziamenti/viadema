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

<svg xmlns="http://www.w3.org/2000/svg" width="16.623" height="3.324" viewBox="0 0 16.623 3.324" {{ $attributes->class($classes) }} data-flux-icon aria-hidden="true">
<path id="Icon_akar-dot-grid-fill-2" data-name="Icon akar-dot-grid-fill" d="M6.325,21.666a1.662,1.662,0,1,0-1.662,1.662A1.662,1.662,0,0,0,6.325,21.666ZM11.312,20a1.662,1.662,0,1,1-1.662,1.662A1.662,1.662,0,0,1,11.312,20Zm8.312,1.662a1.662,1.662,0,1,0-1.662,1.662A1.662,1.662,0,0,0,19.624,21.666Z" transform="translate(-3 -20.004)" fill="currentColor"/>
</svg>
