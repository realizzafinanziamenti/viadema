@props(['notifications' => collect(), 'notificationsCount' => 0, 'tabKey' => ''])

<div x-show="activeTab === @js($tabKey)" x-cloak class="max-h-[350px] overflow-y-auto scrollbar-thin">
    @if ($notifications->isEmpty())
        <div class="mb-1.5 font-semibold text-gray-custom-4">
            Nessuna notifica
        </div>
    @else
        @php
            $lastTimeRange = null;
        @endphp

        @foreach ($notifications as $index => $notification)
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

            <x-notifications.notification-element :notification="$notification" :index="$index" />
        @endforeach
    @endif

    {{-- Footer --}}
    @if ($notificationsCount > $notifications->count())
        <div class="flex justify-center items-center mb-1 mt-3">
            <button wire:click="increaseLimit('{{ $tabKey }}')"
                class="text-sm font-semibold text-gray-custom-5 underline hover:text-gray-custom-3 cursor-pointer">Carica
                altro</button>

            <div wire:loading wire:target="increaseLimit('{{ $tabKey }}')" class="ml-2 text-gray-custom-5">
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
