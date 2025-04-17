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

<svg xmlns="http://www.w3.org/2000/svg" width="16.324" height="9.25" viewBox="0 0 16.324 9.25" {{ $attributes->class($classes) }} data-flux-icon aria-hidden="true">
<path id="Icon_ion-filter-2" data-name="Icon ion-filter" d="M16.632,10.07H1.941a.816.816,0,1,1,0-1.632H16.632a.816.816,0,1,1,0,1.632Zm-2.721,3.809H4.662a.816.816,0,1,1,0-1.632h9.25a.816.816,0,1,1,0,1.632Zm-3.265,3.809H7.926a.816.816,0,1,1,0-1.632h2.721a.816.816,0,0,1,0,1.632Z" transform="translate(-1.125 -8.438)" fill="currentColor"/>
</svg>
