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

<svg xmlns="http://www.w3.org/2000/svg" width="25.127" height="27.285" viewBox="0 0 25.127 27.285" {{ $attributes->class($classes) }} data-flux-icon aria-hidden="true">
<g id="Icona_notifiche" data-name="Icona notifiche" transform="translate(0.77)"><g id="Icon_akar-bell" data-name="Icon akar-bell" transform="translate(0 5.465)"><path id="Tracciato_26" data-name="Tracciato 26" d="M15.188,7.5l-1.544,0a6.576,6.576,0,0,0-6.606,6.321v3.993a4.147,4.147,0,0,1-.559,2.337l-.3.461a1.054,1.054,0,0,0,.862,1.637H21.819a1.054,1.054,0,0,0,.862-1.637l-.3-.461a4.159,4.159,0,0,1-.559-2.338V13.822A6.62,6.62,0,0,0,15.188,7.5Zm2.4,14.746a3.161,3.161,0,0,1-6.321,0" transform="translate(-5.999 -4.339)" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"/><path id="Tracciato_27" data-name="Tracciato 27" d="M17.107,3a2.107,2.107,0,0,1,2.107,2.107V6.161H15V5.107A2.107,2.107,0,0,1,17.107,3Z" transform="translate(-8.678 -3)" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"/></g><g id="Tracciato_208" data-name="Tracciato 208" transform="translate(9.357)" fill="currentColor"><path d="M 7.5 14.25 C 3.778039932250977 14.25 0.75 11.22196006774902 0.75 7.5 C 0.75 3.778039932250977 3.778039932250977 0.75 7.5 0.75 C 11.22196006774902 0.75 14.25 3.778039932250977 14.25 7.5 C 14.25 11.22196006774902 11.22196006774902 14.25 7.5 14.25 Z" stroke="none"/><path d="M 7.5 1.5 C 4.191590309143066 1.5 1.5 4.191590309143066 1.5 7.5 C 1.5 10.80841064453125 4.191590309143066 13.5 7.5 13.5 C 10.80841064453125 13.5 13.5 10.80841064453125 13.5 7.5 C 13.5 4.191590309143066 10.80841064453125 1.5 7.5 1.5 M 7.5 0 C 11.64213943481445 0 15 3.35785961151123 15 7.5 C 15 11.64213943481445 11.64213943481445 15 7.5 15 C 3.35785961151123 15 0 11.64213943481445 0 7.5 C 0 3.35785961151123 3.35785961151123 0 7.5 0 Z" stroke="none" fill="currentColor"/></g></g>
</svg>
