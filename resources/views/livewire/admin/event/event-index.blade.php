<div x-data="{ section: 'section1' }">
    <x-page-title label="Elenco Eventi" class="mt-1" />

    <x-card>
        {{-- Filters and Create Button --}}
        <div class="flex items-center justify-between mb-5">
            <flux:input class="w-sm! xl:w-lg!" wire:model.live.debounce.500ms='search' icon:trailing="magnifying-glass"
                placeholder="Cerca per titolo evento..." />

            {{-- Create Event --}}
            <flux:button icon="plus" class="bg-blue-custom! hover:bg-blue-custom-hover! text-white! px-10">
                Aggiungi Evento</flux:button>
        </div>

        {{-- Toggle Buttons --}}
        <div class="flex items-center px-2 my-4 gap-x-8">
            <x-buttons.toggle-button label="Prossimi" section="section1" />
            <x-buttons.toggle-button label="Passati" section="section2" />
        </div>

        {{-- Upcoming Events --}}
        <div x-show="section === 'section1'" class="flex flex-col gap-2 bg-gray-custom-1 p-2 rounded-lg">
            @foreach ($upcomingEvents as $event)
                <x-calendar.event-element-for-list :event="$event" wire:key='event-{{ $event->id }}' />
            @endforeach

            {{-- Pagination buttons --}}
            {{ $upcomingEvents->links() }}
        </div>

        {{-- Past Events --}}
        <div x-show="section === 'section2'" class="flex flex-col gap-2">
            @foreach ($pastEvents as $event)
                <x-calendar.event-element-for-list :event="$event" wire:key='event-{{ $event->id }}' />
            @endforeach

            {{-- Pagination buttons --}}
            {{ $pastEvents->links() }}
        </div>
    </x-card>
</div>
