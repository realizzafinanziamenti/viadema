@props(['type' => 'button'])

<button type="{{ $type }}"
    {{ $attributes->merge(['class' => 'inline-flex justify-between items-center h-10 px-3 text-sm leading-4 text-gray-500 transition duration-150 ease-in-out bg-white border rounded-sm hover:text-gray-700 focus:outline-none']) }}>

    {{ $slot }}
</button>
