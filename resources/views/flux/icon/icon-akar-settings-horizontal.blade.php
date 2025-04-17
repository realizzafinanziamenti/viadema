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

<svg xmlns="http://www.w3.org/2000/svg" width="15.898" height="15.898" viewBox="0 0 15.898 15.898" {{ $attributes->class($classes) }} data-flux-icon aria-hidden="true">
<g id="Icon_akar-settings-horizontal" data-name="Icon akar-settings-horizontal" transform="translate(0.449 0.449)"><path id="Tracciato_78" data-name="Tracciato 78" d="M4.5,7.5H7.833M19.5,7.5H11.167M4.5,13.333h10m5,0H17.833M4.5,19.167H6.167m13.333,0H9.5" transform="translate(-4.5 -5.834)" fill="none" stroke="currentColor" stroke-linecap="round" stroke-width="0.9"/><path id="Tracciato_79" data-name="Tracciato 79" d="M13.833,6.167A1.667,1.667,0,1,1,12.167,4.5,1.667,1.667,0,0,1,13.833,6.167Z" transform="translate(-7.166 -4.5)" fill="none" stroke="currentColor" stroke-linecap="round" stroke-width="0.9"/><path id="Tracciato_80" data-name="Tracciato 80" d="M25.833,16.667A1.667,1.667,0,1,1,24.167,15,1.667,1.667,0,0,1,25.833,16.667Z" transform="translate(-12.5 -9.166)" fill="none" stroke="currentColor" stroke-linecap="round" stroke-width="0.9"/><path id="Tracciato_81" data-name="Tracciato 81" d="M10.833,27.167A1.667,1.667,0,1,1,9.167,25.5,1.667,1.667,0,0,1,10.833,27.167Z" transform="translate(-5.834 -13.834)" fill="none" stroke="currentColor" stroke-linecap="round" stroke-width="0.9"/></g>
</svg>
