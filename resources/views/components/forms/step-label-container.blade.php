{{-- @props() --}}

<div {{ $attributes->merge(['class' => 'flex items-center justify-between border-b']) }}>
    {{ $slot }}
</div>
