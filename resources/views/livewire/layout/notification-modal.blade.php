<div x-data="{
    show: @js($show),
    focusables() {
        // All focusable element types...
        let selector = 'a, button, input:not([type=\'hidden\']), textarea, select, details, [tabindex]:not([tabindex=\'-1\'])'
        return [...$el.querySelectorAll(selector)]
            // All non-disabled elements...
            .filter(el => !el.hasAttribute('disabled'))
    },
    firstFocusable() { return this.focusables()[0] },
    lastFocusable() { return this.focusables().slice(-1)[0] },
    nextFocusable() { return this.focusables()[this.nextFocusableIndex()] || this.firstFocusable() },
    prevFocusable() { return this.focusables()[this.prevFocusableIndex()] || this.lastFocusable() },
    nextFocusableIndex() { return (this.focusables().indexOf(document.activeElement) + 1) % (this.focusables().length + 1) },
    prevFocusableIndex() { return Math.max(0, this.focusables().indexOf(document.activeElement)) - 1 },
}" x-init="$watch('show', value => {
    if (value) {
        document.body.classList.add('overflow-y-hidden');
    } else {
        document.body.classList.remove('overflow-y-hidden');
    }
})"
    x-on:open-modal.window="$event.detail == 'notification-modal' ? show = true : null "
    x-on:close-modal.window="$event.detail == 'notification-modal' ? show = false : null" x-on:close.stop="show = false"
    x-on:keydown.escape.window="show = false" x-on:keydown.tab.prevent="$event.shiftKey || nextFocusable().focus()"
    x-on:keydown.shift.tab.prevent="prevFocusable().focus()" x-show="show"
    class="fixed inset-0 overflow-y-auto flex items-start justify-end pt-8 pe-40 z-50"
    style="display: {{ $show ? 'block' : 'none' }};">
    <div x-show="show" class="fixed inset-0 transform transition-all" x-on:click="show = false"
        x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
    </div>

    <div x-show="show" class="mb-6 bg-white rounded-3xl shadow-lg transform transition-all w-full max-w-lg"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">

        {{-- CONTENT --}}
        <div class="flex flex-col">

            {{-- HEADER --}}
            <div class="flex items-center justify-between border-b px-5 pt-8 pb-4">
                <div class="flex items-center gap-2">
                    <div class="text-gray-custom-5 font-semibold text-xl">Notifiche</div>

                    @if ($unreadNotificationsCount > 0)
                        <div
                            class="w-5 h-5 flex items-center justify-center bg-orange-custom font-semibold text-white rounded-full text-xs">
                            {{ $unreadNotificationsCount }}</div>
                    @endif
                </div>

                {{-- Close modal button --}}
                <button x-on:click="show = false"
                    class="text-gray-custom-5 hover:text-gray-custom-3 focus:outline-none cursor-pointer">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- BODY --}}
            {{-- Mark all notifications as read button --}}
            <div class="p-6">
                @if ($notifications->count() > 0)
                    <div class="flex items-center justify-end mb-3">
                        <button wire:click="deleteAllNotifications"
                            class="text-[13px] font-semibold text-gray-custom-5 underline hover:text-gray-custom-3 cursor-pointer">Cancella
                            tutte</button>
                    </div>
                @endif

                <div class="max-h-[390px] overflow-y-auto scrollbar-none hover:scrollbar-thin">
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
                    @if ($notificationsCount > $notifications->count())
                        <div class="flex justify-center items-center mb-1 mt-3">
                            <button wire:click="increaseLimit"
                                class="text-sm font-semibold text-gray-custom-5 underline hover:text-gray-custom-3 cursor-pointer">Carica
                                altro</button>

                            <div wire:loading wire:target="increaseLimit" class="ml-2 text-gray-custom-5">
                                <svg class="animate-spin h-5 w-5 text-gray-custom-5" xmlns="http://www.w3.org/2000/svg"
                                    fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                        stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2.93 6.364A8.003 8.003 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3.93-1.574z">
                                    </path>
                                </svg>
                            </div>
                        </div>
                    @endif
                </div>

            </div>
        </div>
    </div>
</div>
