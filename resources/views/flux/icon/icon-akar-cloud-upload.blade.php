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

<svg xmlns="http://www.w3.org/2000/svg" width="23.521" height="21.273" viewBox="0 0 23.521 21.273" {{ $attributes->class($classes) }} data-flux-icon aria-hidden="true">
<g id="Icon_akar-cloud-upload" data-name="Icon akar-cloud-upload" transform="translate(0.764 0.76)"><path id="Tracciato_121" data-name="Tracciato 121" d="M13.973,16.838V26.71m0-9.872L11.23,19.032m2.742-2.194,2.742,2.194M6.331,13.675a4.39,4.39,0,0,0,1.06,8.647h1.1" transform="translate(-3.012 -6.947)" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"/><path id="Tracciato_122" data-name="Tracciato 122" d="M19.379,9.033A6.033,6.033,0,0,0,7.536,11.2a5.223,5.223,0,0,0,.511,1.516" transform="translate(-4.217 -4.477)" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"/><path id="Tracciato_123" data-name="Tracciato 123" d="M24.492,21.469a5.485,5.485,0,1,0-1.283-10.818l-1.459.4" transform="translate(-8.047 -6.094)" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"/></g>
</svg>
