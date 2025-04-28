@props(['header' => null])

<div {{ $attributes->merge(['class' => 'bg-white p-5 rounded-lg']) }}>
    {{-- Card header --}}
    <x-card-header label="{{ $header }}" />

    {{ $slot }}
</div>
