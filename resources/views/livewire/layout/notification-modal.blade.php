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
    x-on:open-modal.window="
    if ($event.detail === 'notification-modal') {
            show = true;
            $wire.refreshNotifications();
        }
    "
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

    <div x-show="show" class="mb-6 bg-white rounded-xl shadow-lg transform transition-all w-full max-w-lg"
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
                            class="w-4 h-4 flex items-center justify-center bg-orange-custom text-white rounded-full text-sm">
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
            <div class="p-5">
                @if ($notifications->count() > 0)
                    <div class="flex items-center justify-end mb-3">
                        <button wire:click="markAllNotificationsAsRead"
                            class="text-sm font-semibold text-gray-custom-5 underline hover:text-gray-custom-3 cursor-pointer">Cancella
                            tutte</button>
                    </div>
                @endif

                <div class="max-h-[520px] overflow-y-auto scrollbar-none hover:scrollbar-thin">
                    @if ($notifications->isEmpty())
                        <div class="text-center text-gray-500">
                            Nessuna notifica
                        </div>
                    @else
                        @foreach ($notifications as $index => $notification)
                            {{-- Delimita le notifiche per data --}}
                            @if (
                                $index === 0 || // Mostra la prima intestazione sempre
                                    ($index > 0 &&
                                        $notification->created_at->toDateString() !== $notifications[$index - 1]->created_at->toDateString()))
                                <div class="mb-1.5 font-bold text-sm text-gray-custom-4">
                                    @if ($notification->created_at->isToday())
                                        Oggi
                                    @elseif ($notification->created_at->isYesterday())
                                        Ieri
                                    @elseif ($notification->created_at->greaterThanOrEqualTo(\Carbon\Carbon::now()->subWeek()))
                                        Ultima settimana
                                    @elseif ($notification->created_at->greaterThanOrEqualTo(\Carbon\Carbon::now()->subMonth()))
                                        Ultimo mese
                                    @elseif ($notification->created_at->greaterThanOrEqualTo(\Carbon\Carbon::now()->subMonths(3)))
                                        Ultimi 3 mesi
                                    @elseif ($notification->created_at->greaterThanOrEqualTo(\Carbon\Carbon::now()->subYear()))
                                        Ultimo anno
                                    @else
                                        Tutti
                                    @endif
                                </div>
                            @endif

                            {{-- Calcolo se la prossima notifica è di un giorno diverso --}}
                            @php
                                $isLastOfDay =
                                    !isset($notifications[$index + 1]) ||
                                    $notification->created_at->toDateString() !==
                                        $notifications[$index + 1]->created_at->toDateString();
                            @endphp

                            {{-- Notifica --}}
                            <div wire:click="redirectTo('{{ $notification->id }}')"
                                class="{{ $notification->read_at ? 'bg-white hover:bg-gray-custom-1' : 'bg-azure-custom-25 hover:bg-azure-custom-20' }}
                            text-gray-custom-5 text-[13px] px-3.5 py-2.5 flex justify-between gap-4 cursor-pointer
                            {{ $isLastOfDay ? 'mb-4' : 'mb-2' }}">

                                {{-- Title custom css based on notification type --}}
                                @php
                                    $titleCss = match ($notification->data['type']) {
                                        'practice-renewability-alert' => 'text-red-600',
                                        default => 'text-azure-custom',
                                    };
                                @endphp

                                <div class="flex-1 flex flex-col gap-1">
                                    <div class="font-bold text-sm {{ $titleCss }} truncate">
                                        {{ $notification->data['title'] }}
                                    </div>

                                    <div class="truncate">{{ $notification->data['message'] }}</div>
                                </div>

                                <div class="shrink-0">
                                    <span class="text-[11px] text-gray-custom-4">
                                        {{ $notification->created_at->diffForHumans() }}
                                    </span>
                                </div>

                            </div>
                        @endforeach
                    @endif

                </div>

                {{-- Footer --}}
                @if ($notificationsCount > $notifications->count())
                    <div class="flex justify-center mt-2">
                        <button wire:click="increaseLimit"
                            class="text-sm font-semibold text-gray-custom-5 underline hover:text-gray-custom-3 cursor-pointer">Carica
                            altro</button>
                    </div>
                @endif
            </div>

        </div>
    </div>
</div>
