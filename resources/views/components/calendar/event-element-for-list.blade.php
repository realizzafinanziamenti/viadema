@props(['event'])

<div
    class="flex items-center justify-between px-4 h-20 bg-white text-sm transition duration-300 ease-in-out border rounded-lg cursor-pointer hover:shadow-lg">

    <div class="flex items-center gap-x-8">
        {{-- Title --}}
        <div class="w-[300px]">
            <h3 class="font-semibold text-black-custom truncate" title="{{ $event->title }}">
                {{ $event->title }}
            </h3>
        </div>

        {{-- Date --}}
        <div class="flex flex-col items-center text-gray-custom-3">
            <span class="text-sm font-bold">
                {{ $event->formatted_start_date }}
            </span>
            <span class="text-sm">
                {{ $event->formatted_start_time }} - {{ $event->formatted_end_time }}
            </span>
        </div>
    </div>

    <div class="flex items-center gap-x-8">
        {{-- User Owner --}}
        <div class="w-[200px]">
            <x-user-table-data :user="$event->user" />
        </div>

        {{-- Actions --}}
        <div class="flex items-center gap-3">
            @can('view', $event)
                <x-table-action-button-view />
            @endcan

            @can('update', $event)
                <x-table-action-button-edit />
            @endcan

            @can('delete', $event)
                <x-table-action-button-delete wire:click='selectCustomerForDelete({{ $event->id }})' />
            @endcan
        </div>
    </div>

</div>
