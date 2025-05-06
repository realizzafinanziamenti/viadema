@props(['practice'])

<div
    class="flex items-center justify-between w-full max-w-[105px] py-1 px-2 border rounded-lg text-[13px] gap-x-1 font-bold {{ $practice->practice_status->getLabelColor() }}">
    <div class="truncate" title="{{ $practice->practice_status->getLabelText() }}">
        {{ $practice->practice_status->getLabelText() }}</div>
    <x-icons.icon-akar-chevron-left-small default="size-3" class="shrink-0" />
</div>
