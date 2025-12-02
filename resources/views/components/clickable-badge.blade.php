@props(['property', 'css'])

<button type="button"
    {{ $attributes->merge(['class' => 'flex items-center justify-between cursor-pointer w-full max-w-[150px] py-1 px-2 border rounded-lg text-[13px] gap-x-1 font-bold ' . $css]) }}>
    <div class="truncate">
        {{ $property }}</div>
    <x-icons.icon-akar-chevron-left-small default="size-3" class="shrink-0" />
</button>
