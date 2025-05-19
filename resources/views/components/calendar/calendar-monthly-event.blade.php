@props(['event'])

@php
    // Check if the event is today and if the current time is within the event's start and end time
$isToday = $event->start_date->isToday();
$isCurrentHour =
    $isToday &&
    now()->format('H:i') >= $event->start_time->format('H:i') &&
    now()->format('H:i') <= $event->end_time->format('H:i');

$bgClass = match (true) {
    $isCurrentHour => 'bg-orange-custom text-white',
    $isToday => 'bg-azure-custom text-white',
    default => 'hover:bg-gray-custom-1',
    };
@endphp


<div wire:click='openDetailEventModal({{ $event->id }})'
    class="px-1 py-0.5 text-xs cursor-pointer truncate {{ $bgClass }}" wire:key='previous-month-{{ $event->id }}'>
    <span class="font-extrabold">•</span>
    <span>{{ $event->formatted_start_time }}</span>
    {{ $event->title }}
</div>
