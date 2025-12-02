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
    default => 'bg-gray-custom-1 hover:bg-gray-custom-2',
    };
@endphp

<div wire:click='openDetailEventModal({{ $event->id }})'
    class="cursor-pointer px-1.5 py-2.5 rounded {{ $bgClass }}">
    <div>
        <div class="text-xs font-semibold">{{ $event->formatted_start_time }}
            -
            {{ $event->formatted_end_time }}</div>
    </div>

    @if (auth()->user()->can('view all events'))
        <div class="text-xs font-semibold">
            {{ $event->user->full_name }}
        </div>
    @endif

    <div class="text-xs mt-1">
        {{ $event->title }}
    </div>
</div>
