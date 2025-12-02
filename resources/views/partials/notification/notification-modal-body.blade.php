<div class="w-full">
    @if ($renewabilityNotifications->isEmpty())
        <div class="mb-1.5 font-semibold text-gray-custom-4">
            Nessuna notifica
        </div>
    @else
        @php
            $lastTimeRange = null;
        @endphp

        @foreach ($renewabilityNotifications as $index => $notification)
            @php
                $currentLabel = $this->getTimeRangeLabel($notification->created_at);
            @endphp

            {{-- Delimitatori per range temporale --}}
            @if ($currentLabel !== $lastTimeRange)
                <div class="mb-1.5 font-bold text-sm text-gray-custom-4">
                    {{ $currentLabel }}
                </div>
                @php $lastTimeRange = $currentLabel; @endphp
            @endif

            {{-- Notifica --}}
            <div wire:click="redirectTo('{{ $notification->id }}')"
                class="{{ $notification->read_at ? 'bg-white hover:bg-gray-custom-1' : 'bg-azure-custom-25 hover:bg-azure-custom-20' }}
                            text-gray-custom-5 text-[13px] px-3.5 py-2.5 flex justify-between gap-4 cursor-pointer
                            {{ $this->isLastOfRange($index) ? 'mb-4' : 'mb-2' }}">

                {{-- Title custom css based on notification type --}}
                @php
                    $titleCss = match ($notification->data['type']) {
                        'practice-renewability-alert' => 'text-red-600',
                        default => 'text-azure-custom',
                    };
                @endphp

                {{-- Left --}}
                <div class="flex-1 flex flex-col gap-1">
                    <div class="font-bold text-sm {{ $titleCss }} truncate">
                        {{ $notification->data['title'] }}
                    </div>

                    <div class="truncate">{{ $notification->data['message'] }}</div>
                </div>

                {{-- Right --}}
                <div class="shrink-0">
                    <span class="text-[11px] text-gray-custom-4">
                        {{ $notification->created_at->diffForHumans() }}
                    </span>
                </div>

            </div>
        @endforeach
    @endif

    {{-- Footer --}}
    @if ($renewabilityNotificationsCount > $renewabilityNotifications->count())
        <div class="flex justify-center items-center mb-1 mt-3">
            <button wire:click="increaseLimit"
                class="text-sm font-semibold text-gray-custom-5 underline hover:text-gray-custom-3 cursor-pointer">Carica
                altro</button>

            <div wire:loading wire:target="increaseLimit" class="ml-2 text-gray-custom-5">
                <svg class="animate-spin h-5 w-5 text-gray-custom-5" xmlns="http://www.w3.org/2000/svg" fill="none"
                    viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                        stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2.93 6.364A8.003 8.003 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3.93-1.574z">
                    </path>
                </svg>
            </div>
        </div>
    @endif
</div>
