@props(['type' => 'button'])

<button type="{{ $type }}"
    {{ $attributes->merge(['class' => 'block w-full ps-4.5 pe-3 h-8 text-start text-sm leading-[1.125rem] text-zinc-700 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 transition duration-150 ease-in-out']) }}>

    {{ $slot }}
</button>
