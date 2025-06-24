@props(['header' => null])

<div {{ $attributes->merge(['class' => 'bg-white p-5 rounded-lg']) }}>
    @if ($header)
        {{-- Card header --}}
        <x-card-header label="{{ $header }}" />
    @endif

    {{ $slot }}
</div>
