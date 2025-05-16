@props(['event'])

<div wire:click='openDetailEventModal({{ $event->id }})'
    class="px-1 py-0.5 text-xs cursor-pointer truncate hover:bg-gray-custom-1"
    wire:key='previous-month-{{ $event->id }}'>
    <span class="font-extrabold">•</span>
    <span>{{ $event->starts_at->format('H:i') }}</span>
    {{ $event->title }}
</div>
