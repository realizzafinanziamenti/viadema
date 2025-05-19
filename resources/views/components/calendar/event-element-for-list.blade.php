@props(['event'])

<div class="grid grid-cols-12 px-4 py-3 gap-x-3 bg-white text-sm transition duration-300 ease-in-out border rounded-lg">

    {{-- Title --}}
    <div class="col-span-4 flex items-center">
        <h3 class="font-semibold text-black-custom truncate" title="{{ $event->title }}">
            {{ $event->title }}
        </h3>
    </div>

    {{-- Date --}}
    <div class="col-span-2 text-sm flex flex-col items-center justify-start text-gray-custom-3">
        <span class="font-bold flex items-center gap-x-1">
            @if ($event->start_date->isToday())
                <div
                    class="bg-azure-custom text-white inline-flex items-center text-xs font-semibold rounded-sm px-1 py-0.5">
                    Oggi
                </div>
            @endif

            {{ $event->formatted_start_date }}
        </span>
        <span>
            {{ $event->formatted_start_time }} - {{ $event->formatted_end_time }}
        </span>
    </div>

    <div class="col-span-2"></div>

    {{-- User Owner --}}
    <div class="col-span-2 flex items-center">
        <x-user-table-data :user="$event->user" />
    </div>

    {{-- Actions --}}
    <div class="col-span-2 flex items-center justify-end gap-3">
        @can('view', $event)
            <x-table-action-button-view wire:click='openDetailEventModal({{ $event->id }})' />
        @endcan

        @can('update', $event)
            <x-table-action-button-edit wire:click='openEditEventModal({{ $event->id }})' />
        @endcan

        @can('delete', $event)
            <x-table-action-button-delete x-on:click="$dispatch('open-modal', 'event-delete')" />
        @endcan
    </div>

</div>
