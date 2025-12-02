@props(['type' => 'button', 'size' => ''])

@php
    $sizeClasses = match ($size) {
        'sm' => 'text-sm py-1.5 h-8 leading-[1.125rem]',
        'xs' => 'text-xs py-1.5 h-6 leading-[1.125rem]',
        'lg' => 'text-sm py-2 h-12 leading-[1.25rem]',
        default => 'text-base sm:text-sm py-2 h-10 leading-[1.375rem]',
    };
@endphp

<button type="{{ $type }}"
    {{ $attributes->merge(['class' => 'block w-full ps-4.5 pe-3 text-start truncate text-gray-custom-5 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 transition duration-150 ease-in-out ' . $sizeClasses]) }}>

    {{ $slot }}
</button>
