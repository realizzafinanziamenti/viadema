@props([
    'previousMonthEvents' => [],
    'currentMonthEvents' => [],
    'nextMonthEvents' => [],
    'viewMode' => 'month',
    'firstDayOfMonth',
    'daysInCurrentMonth',
    'prevMonthStart',
    'currentDate',
    'currentYear',
    'currentMonth',
    'daysInCurrentMonth',
    'daysInNextMonth',
    'totalWeeks',
    'currentWeekStart',
    'currentWeekEnd',
])

@php $cellIndex = 0; @endphp {{-- indice globale celle, per sapere dove ci troviamo --}}

<div>
    {{-- Monthly View --}}
    @if ($viewMode === 'month')
        <div x-data="{ animate: false }" x-init="setTimeout(() => animate = true, 100)" x-bind:class="animate ? 'opacity-100' : 'opacity-0'"
            class="transition-opacity duration-200 ease-in-out opacity-0 border rounded-lg">

            {{-- Header --}}
            <div class="grid grid-cols-7 text-sm h-10 text-azure-custom bg-azure-custom-light rounded-t-lg">
                <div class="flex items-center justify-center">Lunedì</div>
                <div class="flex items-center justify-center">Martedì</div>
                <div class="flex items-center justify-center">Mercoledì</div>
                <div class="flex items-center justify-center">Giovedì</div>
                <div class="flex items-center justify-center">Venerdì</div>
                <div class="flex items-center justify-center">Sabato</div>
                <div class="flex items-center justify-center">Domenica</div>
            </div>

            <div class="grid grid-cols-7">
                {{-- Previous Month Days --}}
                @for ($i = 0; $i < $firstDayOfMonth; $i++)
                    @php
                        // Aggiunge uno zero iniziale se il giorno è minore di 10 (es. 3 → 03), per ottenere una stringa data valida.
                        $dayFormatted = str_pad($prevMonthStart + $i, 2, '0', STR_PAD_LEFT);
                        // Crea la data corrispondente al giorno del mese precedente, necessaria per visualizzare correttamente la griglia (serve anche per evidenziare il giorno corrente o mostrare eventi).
                        $currentDate = Carbon\Carbon::create(
                            $currentYear,
                            $currentMonth - 1,
                            $dayFormatted,
                        )->toDateString();
                        // Incrementa l’indice globale della cella
                        $cellIndex++;
                    @endphp

                    <div class="p-1 border ">
                        <div class="flex justify-start p-0.5">
                            <div
                                class="flex items-center justify-center text-sm w-6 h-6 {{ $currentDate === now()->toDateString() ? 'bg-azure-custom rounded-full' : '' }} text-gray-custom-3">
                                {{ $prevMonthStart + $i }}</div>
                        </div>

                        <div class="flex flex-col mt-1 min-h-24 gap-y-1">
                            @foreach ($previousMonthEvents as $event)
                                @if ($event->starts_at->toDateString() <= $currentDate && $event->ends_at->toDateString() >= $currentDate)
                                    <x-calendar.calendar-monthly-event :event="$event" />
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endfor

                {{-- Current Month Days --}}
                @for ($day = 1; $day <= $daysInCurrentMonth; $day++)
                    @php
                        // get the current date in string format
                        $dayFormatted = str_pad($day, 2, '0', STR_PAD_LEFT); // Add leading zero
                        // costruisci la data completa nel mese corrente
                        $currentDate = Carbon\Carbon::create(
                            $currentYear,
                            $currentMonth,
                            $dayFormatted,
                        )->toDateString();

                        // Calcoli se sei nella prima o ultima colonna dell’ultima riga visibile del calendario
                        $isStartOfLastRow = $cellIndex === ($totalWeeks - 1) * 7; // posizione della prima cella dell’ultima settimana.
                        $isEndOfLastRow = $cellIndex === $totalWeeks * 7 - 1; // posizione dell’ultima cella del calendario
                        // In base alla posizione (cellIndex), scegli la classe CSS di bordo arrotondato
                        $roundedClass = match (true) {
                            $isStartOfLastRow => 'rounded-bl-lg',
                            $isEndOfLastRow => 'rounded-br-lg',
                            default => '',
                        };
                        // Incrementa l’indice globale della cella
                        $cellIndex++;
                    @endphp

                    <div class="p-1 border {{ $roundedClass }}">
                        <div class="flex justify-start p-0.5">
                            <div
                                class="h-6 w-6 flex items-center justify-center text-sm text-black-custom
                                {{ $currentDate === now()->toDateString() ? 'bg-azure-custom rounded-full text-white' : '' }}">
                                {{ $day }}</div>
                        </div>

                        <!-- Verifica la presenza di eventi -->
                        <div class="flex flex-col mt-1 min-h-24 gap-y-1">
                            @foreach ($currentMonthEvents as $event)
                                @if ($event->starts_at->toDateString() <= $currentDate && $event->ends_at->toDateString() >= $currentDate)
                                    <x-calendar.calendar-monthly-event :event="$event" />
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endfor

                {{-- Next Month Days --}}
                @for ($i = 1; $i <= $daysInNextMonth; $i++)
                    @php
                        // Se siamo a dicembre, il mese successivo è gennaio e l’anno va incrementato. Altrimenti, il mese è semplicemente quello successivo
                        $nextMonthYear = $currentMonth == 12 ? $currentYear + 1 : $currentYear;
                        $nextMonth = $currentMonth == 12 ? 1 : $currentMonth + 1;
                        // Formatta il giorno e crea una data per i giorni del mese successivo (che completano l’ultima riga).
                        $dayFormatted = str_pad($i, 2, '0', STR_PAD_LEFT);
                        $currentDate = Carbon\Carbon::create($nextMonthYear, $nextMonth, $dayFormatted)->toDateString();

                        // Calcoli se sei nella prima o ultima colonna dell’ultima riga visibile del calendario
                        $isStartOfLastRow = $cellIndex === ($totalWeeks - 1) * 7; // posizione della prima cella dell’ultima settimana.
                        $isEndOfLastRow = $cellIndex === $totalWeeks * 7 - 1; // posizione dell’ultima cella del calendario
                        // In base alla posizione (cellIndex), scegli la classe CSS di bordo arrotondato
                        $roundedClass = match (true) {
                            $isStartOfLastRow => 'rounded-bl-lg',
                            $isEndOfLastRow => 'rounded-br-lg',
                            default => '',
                        };
                        // Incrementa l’indice globale della cella
                        $cellIndex++;
                    @endphp

                    <div class="p-1 border border-gray-2 {{ $roundedClass }}">
                        <div class="flex justify-start p-0.5">
                            <div class="flex items-center justify-center w-6 h-6 text-sm
                                {{ $currentDate === now()->toDateString() ? 'bg-azure-custom rounded-full' : '' }} text-gray-custom-3"
                                {{ $roundedClass }}>
                                {{ $i }}</div>
                        </div>

                        <div class="flex flex-col mt-1 min-h-24 gap-y-1">
                            @foreach ($nextMonthEvents as $event)
                                @if ($event->starts_at->toDateString() <= $currentDate && $event->ends_at->toDateString() >= $currentDate)
                                    <x-calendar.calendar-monthly-event :event="$event" />
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endfor
            </div>
        </div>
    @endif

    {{-- Weekly View --}}
    @if ($viewMode === 'week')
        <div class="flex flex-col transition-opacity duration-200 ease-in-out opacity-0" x-data="{ animate: false }"
            x-init="setTimeout(() => animate = true, 100)" x-bind:class="animate ? 'opacity-100' : 'opacity-0'">
            {{-- Header Row --}}
            <div class="flex h-10">
                <div class="w-20">{{-- Empty row  --}}</div>

                <div class="grid flex-1 grid-cols-7  rounded-t-lg">
                    @foreach (range(0, 6) as $i)
                        @php
                            $date = $currentWeekStart->copy()->addDays($i);
                            $currentDate = $date->toDateString();
                        @endphp

                        <div wire:key='week-header-{{ $i }}'
                            class="flex items-center justify-center text-sm border
                            {{ $currentDate === now()->toDateString() ? 'font-extrabold bg-azure-custom text-azure-custom-light border-azure-custom' : 'text-azure-custom bg-azure-custom-light border-azure-custom-light' }}
                            {{ $currentDate === $currentWeekStart->toDateString() ? 'rounded-tl-lg' : '' }}
                            {{ $currentDate === $currentWeekEnd->toDateString() ? 'rounded-tr-lg' : '' }}">
                            {{ $date->translatedFormat('l d') }}
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Hour Rows --}}
            @foreach (range(8, 21) as $hour)
                <div class="flex min-h-24" wire:key='week-hour-{{ $hour }}'>
                    <!-- Colonna delle Ore -->
                    <div class="flex items-center justify-center w-20 text-xs border-b text-gray-custom-4">
                        {{ str_pad($hour, 2, '0', STR_PAD_LEFT) }}:00
                    </div>

                    <!-- Corpo della colonna per gli eventi -->
                    <div class="grid flex-1 grid-cols-7">
                        @foreach (range(0, 6) as $i)
                            @php
                                $date = $currentWeekStart->copy()->addDays($i);
                            @endphp

                            <div class="flex flex-col p-1 border-b border-e {{ $i === 0 ? 'border-s' : '' }} gap-y-1 {{ $i === 6 && $hour === 21 ? 'rounded-br-lg' : '' }}"
                                wire:key='week-hour-{{ $hour }}-{{ $i }}'>
                                {{-- @foreach ($currentWeekEvents as $event)
                                    @if ($event->start_date->toDateString() === $date->toDateString() && $event->start_time->format('H') == $hour)
                                        <x-calendar-daily-weekly-event :event="$event" />
                                    @endif
                                @endforeach --}}
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Daily View --}}
    @if ($viewMode === 'day')
        <div class="flex flex-col transition-opacity duration-200 ease-in-out opacity-0" x-data="{ animate: false }"
            x-init="setTimeout(() => animate = true, 100)" x-bind:class="animate ? 'opacity-100' : 'opacity-0'">
            {{-- Header Row --}}
            <div class="flex h-10">
                <div class="w-20">{{-- Empty row  --}}</div>

                <div
                    class="flex items-center justify-center flex-1 rounded-t-lg text-sm
                    {{ $currentDate->toDateString() === now()->toDateString() ? 'font-extrabold bg-azure-custom text-azure-custom-light' : 'text-azure-custom bg-azure-custom-light' }}">
                    {{ ucfirst($currentDate->translatedFormat('l d')) }}
                </div>
            </div>

            {{-- Hour Rows --}}
            @foreach (range(8, 21) as $hour)
                <div class="flex flex-1 border-b min-h-24 {{ $hour === 21 ? 'rounded-br-lg' : '' }}"
                    wire:key='day-hour-{{ $hour }}'>
                    <!-- Colonna delle Ore -->
                    <div class="flex items-center justify-center w-20 text-xs text-gray-4">
                        {{ str_pad($hour, 2, '0', STR_PAD_LEFT) }}:00
                    </div>

                    <!-- Corpo della colonna per gli eventi -->
                    <div
                        class="grid flex-1 grid-cols-4 gap-1 p-1 border-s border-e {{ $hour === 21 ? 'rounded-br-lg' : '' }}">
                        {{-- @foreach ($currentDayEvents as $event)
                            @if ($event->start_time->format('H') == $hour)
                                <x-calendar-daily-weekly-event :event="$event" />
                            @endif
                        @endforeach --}}
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
