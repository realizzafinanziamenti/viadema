<div x-data="{
    show: @js($show),
    activeTab: 'renewability',
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
            <div class="flex items-center justify-between mb-3 bg-gray-custom-2">
                <div class="px-6">
                    <div class="flex items-center gap-4">
                        <x-notifications.notifications-type-button :unreadCount="$unreadRenewabilityNotificationsCount" tabKey="renewability"
                            label="Rinnovi" />
                        <x-notifications.notifications-type-button :unreadCount="$unreadOtherNotificationsCount" tabKey="others"
                            label="Altre Notifiche" />
                    </div>

                    {{-- @if ($renewabilityNotifications->count() > 0 || $otherNotifications->count() > 0)
                        <button wire:click="deleteAllNotifications"
                            class="text-[13px] font-semibold text-gray-custom-5 underline hover:text-gray-custom-3 cursor-pointer">Cancella
                            tutte</button>
                            @endif --}}
                </div>
            </div>

            <div class="p-6">
                <x-notifications.notifications-modal-body tabKey="renewability" :notifications="$renewabilityNotifications" :notificationsCount="$unreadRenewabilityNotificationsCount" />
                <x-notifications.notifications-modal-body tabKey="others" :notifications="$otherNotifications" :notificationsCount="$unreadOtherNotificationsCount" />
            </div>
        </div>
    </div>
</div>
