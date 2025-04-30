@props(['type' => 'button'])

<button type="{{ $type }}"
    {{ $attributes->merge(['class' => 'inline-flex justify-between items-center h-8 ps-4.5 pe-3 text-sm leading-[1.125rem] text-zinc-500 transition duration-150 ease-in-out bg-white border rounded-sm hover:text-zinc-700 focus:outline-none']) }}>

    {{ $slot }}
</button>
