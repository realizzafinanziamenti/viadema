@use('Namu\WireChat\Facades\WireChat')

<header class="z-10 sticky top-0 w-full " dusk="header">

    {{-- Search input --}}
    {{-- @if ($allowChatsSearch) --}}
    <section>
        <flux:input type="search" id="chats-search-field" name="chats_search"
            class="col-span-11 border-0  bg-inherit dark:text-white outline-hidden w-full focus:outline-hidden  focus:ring-0 hover:ring-0"
            wire:model.live.debounce='search' icon:trailing="magnifying-glass" placeholder="Cerca..." autocomplete="off"
            maxlength="100" />
    </section>
    {{-- @endif --}}

    {{-- Title/name and Icon --}}
    <section class=" justify-between flex items-center mt-6">
        {{-- @if ($showNewChatModalButton) --}}
        <x-wirechat::actions.new-chat widget="{{ $this->isWidget() }}">
            <flux:button icon="plus" size="sm"
                class="bg-azure-custom! hover:bg-azure-custom-hover! border-azure-custom! text-white! px-8">
                Nuova chat
            </flux:button>
        </x-wirechat::actions.new-chat>
        {{-- @endif --}}
    </section>

</header>
