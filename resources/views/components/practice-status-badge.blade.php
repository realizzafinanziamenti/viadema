@props(['practice'])

<button type="button"
    {{ $attributes->merge(['class' => 'flex items-center justify-between cursor-pointer w-full max-w-[105px] py-1 px-2 border rounded-lg text-[13px] gap-x-1 font-bold ' . $practice->practice_status?->getLabelColor()]) }}>
    <div class="truncate">
        {{ $practice->practice_status?->getLabelText() }}</div>
    <x-icons.icon-akar-chevron-left-small default="size-3" class="shrink-0" />
</button>
