@props(['event' => null])

<x-modal name="event-detail" maxWidth="2xl">
    <x-modal-header label="Dettaglio evento" />

    <div class="w-full mt-10 mb-5 text-sm">
        {{-- Title --}}
        <div class="mb-2.5">
            <span class="text-gray-custom-4">Titolo: </span>
            <span>{{ $event?->title }}</span>

            {{-- Link to practice --}}
            @if ($event?->practice)
                <div class="mb-2.5">
                    <a href="{{ route('practice.show', $event?->practice?->id) }}" wire:navigate
                        class="text-xs underline text-blue-custom hover:text-blue-custom-hover">Link pratica</a>
                </div>
            @endif
        </div>
        {{-- Event Owner --}}
        <div class="mb-2.5">
            <span class="text-gray-custom-4">Creatore evento: </span>
            <span>{{ $event?->user?->fullName }}</span>
        </div>
        {{-- Start Date --}}
        <div class="mb-2.5">
            <span class="text-gray-custom-4">Data evento: </span>
            <span>{{ $event?->formatted_start_date }}</span>
        </div>
        {{-- Start Time --}}
        <div class="mb-2.5">
            <span class="text-gray-custom-4">Orario inizio: </span>
            <span>{{ $event?->formatted_start_time }}</span>
        </div>
        {{-- End Time --}}
        <div class="mb-2.5">
            <span class="text-gray-custom-4">Orario fine: </span>
            <span>{{ $event?->formatted_end_time }}</span>
        </div>
        {{-- Description --}}
        @if ($event?->description)
            <div class="mb-2.5">
                <span class="text-gray-custom-4">Descrizione: </span>
                <span>{{ $event?->description }}</span>
            </div>
        @endif
        {{-- Participants --}}
        @if ($event?->participants && $event?->participants->isNotEmpty())
            <div class="mb-2.5">
                <span class="text-gray-custom-4">Partecipanti: </span>
                <span>{{ $event?->participants->pluck('fullName')->join(', ') }}</span>
            </div>
        @endif
    </div>

    {{-- Submit Buttons --}}
    <div class="flex items-center justify-end gap-x-3 mt-18">
        <flux:button variant="primary" type="button" size="sm"
            x-on:click="$dispatch('close-modal', 'event-detail')"
            class="px-10 bg-gray-custom-2 border-gray-custom-2 text-gray-custom-5 hover:bg-gray-custom-3-hover hover:border-gray-custom-3-hover hover:text-white">
            Annulla
        </flux:button>

        <flux:button variant="primary" type="button" size="sm"
            wire:click="openEditEventModal({{ $event?->id }})"
            class="px-10 bg-azure-custom border-azure-custom hover:bg-azure-custom-hover hover:border-azure-custom-hover">
            Modifica
        </flux:button>

        <flux:button variant="primary" type="button" size="sm"
            x-on:click="$dispatch('open-modal', 'event-delete')"
            class="px-10 bg-red-600 border-red-600 hover:bg-red-800 hover:border-red-800">
            Elimina
        </flux:button>
    </div>
</x-modal>
