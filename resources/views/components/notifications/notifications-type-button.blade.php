@props([
    'unreadCount' => 0,
    'tabKey' => 'renewability',
    'label' => 'Avvisi di Rinnovabilità',
])

<button class="text-[13px] font-semibold h-14 px-3 text-gray-custom-5 flex items-center gap-2 relative"
    :class="activeTab !== @js($tabKey) ? 'cursor-pointer' : ''"
    x-on:click="activeTab = @js($tabKey) ">
    {{ $label }}

    <div x-show="activeTab === @js($tabKey)"
        class="absolute start-0 end-0 bottom-0 bg-gray-custom-5 h-1 rounded-lg" x-cloak>
    </div>

    {{-- Unread count badge --}}
    @if ($unreadCount > 0)
        <div
            class="w-5 h-5 flex items-center justify-center bg-orange-custom font-semibold text-white rounded-full text-xs">
            {{ $unreadCount }}</div>
    @endif
</button>
