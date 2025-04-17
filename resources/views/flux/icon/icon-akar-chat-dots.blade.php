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

<svg xmlns="http://www.w3.org/2000/svg" width="20.627" height="18.754" viewBox="0 0 20.627 18.754" {{ $attributes->class($classes) }} data-flux-icon aria-hidden="true">
<g id="Icon_akar-chat-dots" data-name="Icon akar-chat-dots" transform="translate(0.65 0.65)"><path id="Tracciato_28" data-name="Tracciato 28" d="M14.6,19.161c3.644,0,5.466,0,6.6-1.074s1.133-2.8,1.133-6.257,0-5.184-1.133-6.257S18.24,4.5,14.6,4.5H10.73c-3.644,0-5.466,0-6.6,1.074S3,8.375,3,11.83s0,5.184,1.133,6.257a4.3,4.3,0,0,0,2.733.98" transform="translate(-3 -4.5)" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.3"/><path id="Tracciato_29" data-name="Tracciato 29" d="M16.1,18.808h0m-4.665,0h0m9.33,0h0M18.433,25.83a10.259,10.259,0,0,0-4.48,1.335c-2.33,1.209-3.5,1.815-4.069,1.429s-.512-.879-.294-3.269l-.143.726" transform="translate(-6.109 -11.17)" fill="none" stroke="currentColor" stroke-linecap="round" stroke-width="1.3"/></g>
</svg>
