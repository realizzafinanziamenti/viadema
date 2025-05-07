{{-- @props() --}}

<div {{ $attributes->merge(['class' => 'flex items-center justify-between border-b-2 border-collapse']) }}>
    {{ $slot }}
</div>
