@props(['property', 'css'])

<button type="button"
    {{ $attributes->merge(['class' => 'flex items-center justify-center w-full max-w-[150px] py-1 px-2 border rounded-lg text-[13px] gap-x-1 font-bold ' . $css]) }}>
    <div class="truncate">
        {{ $property }}</div>
</button>
