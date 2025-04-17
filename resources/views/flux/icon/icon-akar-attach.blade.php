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

<svg xmlns="http://www.w3.org/2000/svg" width="12.385" height="19.643" viewBox="0 0 12.385 19.643" {{ $attributes->class($classes) }} data-flux-icon aria-hidden="true">
<g id="Icon_akar-attach" data-name="Icon akar-attach" transform="translate(0.75 0.75)"><path id="Icon_akar-attach-2" data-name="Icon akar-attach" d="M9,8.361V15.7a5.443,5.443,0,0,0,5.443,5.443h0A5.443,5.443,0,0,0,19.886,15.7V6.629A3.629,3.629,0,0,0,16.257,3h0a3.629,3.629,0,0,0-3.629,3.629v8.329a1.814,1.814,0,0,0,1.814,1.814h0a1.814,1.814,0,0,0,1.814-1.814V8.443" transform="translate(19.885 21.143) rotate(180)" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"/></g>
</svg>
