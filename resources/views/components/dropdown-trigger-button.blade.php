@props(['type' => 'button', 'size' => '', 'bgColor' => 'bg-white'])

@php
    $sizeClasses = match ($size) {
        'sm' => 'text-sm py-1.5 h-8 leading-[1.125rem] rounded-md',
        'xs' => 'text-xs py-1.5 h-6 leading-[1.125rem] rounded-lg',
        'lg' => 'text-sm py-2 h-12 leading-[1.25rem] rounded-md',
        default => 'text-base sm:text-sm py-2 h-10 leading-[1.375rem] rounded-md',
    };
@endphp

<button type="{{ $type }}"
    {{ $attributes->merge(['class' => $bgColor . ' border inline-flex justify-between gap-1 items-center ps-4.5 pe-3 text-zinc-500 transition duration-150 ease-in-out rounded-sm hover:text-zinc-700 focus:outline-none ' . $sizeClasses]) }}>

    {{ $slot }}
</button>
