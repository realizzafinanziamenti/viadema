@props(['type' => 'button'])

<button type="{{ $type }}"
    {{ $attributes->merge(['class' => 'flex items-center justify-center rounded-full text-gray-custom-4 bg-gray-custom-1 h-8 w-8']) }}>

    {{ $slot }}
</button>
