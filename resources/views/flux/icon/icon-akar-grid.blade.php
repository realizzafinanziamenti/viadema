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

<svg xmlns="http://www.w3.org/2000/svg" width="15.668" height="15.668" viewBox="0 0 15.668 15.668" {{ $attributes->class($classes) }} data-flux-icon aria-hidden="true">
<g id="Icon_akar-grid" data-name="Icon akar-grid" transform="translate(0.449 0.449)"><path id="Tracciato_149" data-name="Tracciato 149" d="M5.32,4.5h4.1a.82.82,0,0,1,.82.82v4.1a.82.82,0,0,1-.82.82H5.32a.82.82,0,0,1-.82-.82V5.32a.82.82,0,0,1,.82-.82Z" transform="translate(-4.5 -4.5)" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="0.9"/><path id="Tracciato_150" data-name="Tracciato 150" d="M5.32,21h4.1a.82.82,0,0,1,.82.82v4.1a.82.82,0,0,1-.82.82H5.32a.82.82,0,0,1-.82-.82v-4.1A.82.82,0,0,1,5.32,21Z" transform="translate(-4.5 -11.975)" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="0.9"/><path id="Tracciato_151" data-name="Tracciato 151" d="M21.82,4.5h4.1a.82.82,0,0,1,.82.82v4.1a.82.82,0,0,1-.82.82h-4.1a.82.82,0,0,1-.82-.82V5.32A.82.82,0,0,1,21.82,4.5Z" transform="translate(-11.975 -4.5)" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="0.9"/><path id="Tracciato_152" data-name="Tracciato 152" d="M21.82,21h4.1a.82.82,0,0,1,.82.82v4.1a.82.82,0,0,1-.82.82h-4.1a.82.82,0,0,1-.82-.82v-4.1A.82.82,0,0,1,21.82,21Z" transform="translate(-11.975 -11.975)" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="0.9"/></g>
</svg>
