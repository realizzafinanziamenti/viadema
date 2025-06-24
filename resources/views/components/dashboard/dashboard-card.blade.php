@props(['header' => null])

<div {{ $attributes->merge(['class' => 'bg-white py-3 px-4 h-[300px] rounded-lg']) }}>
    @if ($header)
        {{-- Card header --}}
        <x-dashboard.card-header-dashboard label="{{ $header }}" />
    @endif

    {{ $slot }}
</div>
