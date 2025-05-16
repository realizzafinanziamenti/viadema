@props(['event'])

<div wire:click='openDetailEventModal({{ $event->id }})'
    class="flex flex-col cursor-pointer gap-3 p-1.5 rounded min-h-24 bg-gray-custom-1 hover:bg-gray-custom-2">
    <div>
        <div class="text-xs font-semibold">{{ $event->formatted_start_time }}
            -
            {{ $event->formatted_end_time }}</div>
    </div>

    <div class="text-xs">
        <span class="font-semibold">Creatore Evento</span>:
        {{ $event->user->full_name }}
    </div>
</div>
