@props([
    'events' => [],
    'viewMode' => 'month',
    'firstDayOfMonth',
    'daysInCurrentMonth',
    'prevMonthStart',
    'currentYear',
    'currentMonth',
    'daysInCurrentMonth',
    'daysInNextMonth',
    'totalWeeks',
])

@php $cellIndex = 0; @endphp {{-- indice globale celle, per sapere dove ci troviamo --}}

<div class="border rounded-lg">
    {{ dump($viewMode) }}
    {{-- Monthly View --}}
    @if ($viewMode === 'month')
        <div x-data="{ animate: false }" x-init="setTimeout(() => animate = true, 100)" x-bind:class="animate ? 'opacity-100' : 'opacity-0'"
            class="transition-opacity duration-200 ease-in-out opacity-0">

            {{-- Header --}}
            <div class="grid grid-cols-7 text-sm h-11 text-azure-custom bg-azure-custom-light rounded-t-lg">
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
                            {{-- @foreach ($previousMonthEvents as $event)
                                @if ($event->start_date->toDateString() === $currentDate)
                                    <x-calendar-monthly-event :event="$event" />
                                @endif
                            @endforeach --}}
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
                        <div class="flex flex-col mt-1 min-h-32 gap-y-1">
                            {{-- @foreach ($currentMonthEvents as $event)
                                @if ($event->start_date->toDateString() === $currentDate)
                                    <x-calendar-monthly-event :event="$event" />
                                @endif
                            @endforeach --}}
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

                        <div class="flex flex-col mt-1 min-h-32 gap-y-1">
                            {{-- @foreach ($nextMonthEvents as $event)
                                @if ($event->start_date->toDateString() === $currentDate)
                                    <x-calendar-monthly-event :event="$event" />
                                @endif
                            @endforeach --}}
                        </div>
                    </div>
                @endfor
            </div>
        </div>
    @endif

    {{-- Weekly View --}}

</div>
