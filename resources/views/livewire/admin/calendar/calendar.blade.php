<div>
    <x-page-title label="Calendario" class="mt-1" />

    <x-card>

        {{-- Calendar Nav --}}
        <div class="flex items-center gap-8 mb-6">

            {{-- Monthly Nav --}}
            <div class="flex items-center gap-x-4 text-gray-custom-4">
                {{-- Prev Month Button --}}
                <button type="button" wire:click="prev"
                    class="relative inline-flex items-center cursor-pointer px-1 py-1 transition duration-150 ease-in-out hover:text-gray-custom-3">
                    <flux:icon.chevron-left />
                </button>

                {{-- Current Month --}}
                <div class="font-bold text-xl">
                    {{ ucfirst($currentDate->translatedFormat('F Y')) }}
                </div>

                {{-- Next Button --}}
                <button type="button" wire:click="next"
                    class="relative inline-flex items-center cursor-pointer px-1 py-1 transition duration-150 ease-in-out hover:text-gray-custom-3">
                    <flux:icon.chevron-right />
                </button>
            </div>

            {{-- View Mode Buttons --}}
            <div class="flex items-center text-sm">
                <x-calendar.view-mode-button label="Mese" view="month" :viewMode="$viewMode"
                    class="rounded-l-md border-y border-l
                    {{ $viewMode === 'month' ? 'bg-blue-custom text-white' : 'text-blue-custom' }}" />

                <x-calendar.view-mode-button label="Settimana" view="week" :viewMode="$viewMode"
                    class="border
                {{ $viewMode === 'week' ? 'bg-blue-custom text-white' : 'text-blue-custom' }}" />

                <x-calendar.view-mode-button label="Giorno" view="day" :viewMode="$viewMode"
                    class="rounded-r-md border-y border-r
                {{ $viewMode === 'day' ? 'bg-blue-custom text-white' : 'text-blue-custom' }}" />
            </div>

            {{-- Today Button --}}
            <button wire:click="setToday"
                class='px-3 py-1.5 text-sm border border-azure-custom cursor-pointer bg-azure-custom
            text-white hover:bg-azure-custom-hover rounded-md'>
                Oggi
            </button>

        </div>

        {{-- SearchBar --}}
        <div class="flex items-center justify-between mb-6">
            <flux:input class="w-sm! xl:w-lg!" wire:model.live.debounce.500ms='search' icon:trailing="magnifying-glass"
                placeholder="Cerca per nome, cognome..." />

            {{-- Event List --}}
            <flux:button icon="plus" class="bg-blue-custom! hover:bg-blue-custom-hover! text-white! px-10">
                Aggiungi Evento</flux:button>
        </div>

        {{-- Calendar --}}
        <x-calendar.calendar :previousMonthEvents="$previousMonthEvents" :currentMonthEvents="$currentMonthEvents" :nextMonthEvents="$nextMonthEvents" :viewMode="$viewMode" :currentDate="$currentDate"
            :currentYear="$currentYear" :currentMonth="$currentMonth" :firstDayOfMonth="$firstDayOfMonth" :daysInCurrentMonth="$daysInCurrentMonth" :daysInNextMonth="$daysInNextMonth"
            :prevMonthStart="$prevMonthStart" :totalWeeks="$totalWeeks" :currentWeekStart="$currentWeekStart" :currentWeekEnd="$currentWeekEnd" />

    </x-card>
</div>
