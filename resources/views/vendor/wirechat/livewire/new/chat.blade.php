@use('Namu\WireChat\Facades\WireChat')
<div id="new-chat-modal ">

    <div
        class="relative w-full h-96  border mx-auto border-[var(--wc-light-secondary)]   overflow-auto bg-white  p-10 sm:max-w-lg sm:rounded-lg">

        <header class=" sticky top-0 bg-white z-10">
            <div class="flex items-center justify-between pb-2">

                <x-modal-header label="{{ __('wirechat::new.chat.labels.heading') }}" class="text-lg font-semibold" />

                <x-wirechat::actions.close-modal>
                    <button dusk="close_modal_button"
                        class="p-2  text-gray-600 hover:bg-[var(--wc-light-secondary)] dark:hover:bg-[var(--wc-dark-secondary)] dark:hover:text-white rounded-full hover:text-gray-800 ">
                        <svg class="w-5 h-5 cursor-pointer" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24" stroke-width="1.2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </x-wirechat::actions.close-modal>

            </div>

            <section class="flex flex-wrap items-center px-0 ">

                <flux:input type="search" id="users-search-field" dusk="search_users_field"
                    class="col-span-11 border-0  bg-inherit dark:text-white outline-hidden w-full focus:outline-hidden  focus:ring-0 hover:ring-0"
                    wire:model.live.debounce='search' icon:trailing="magnifying-glass"
                    placeholder="{{ __('wirechat::new.chat.inputs.search.placeholder') }}" autocomplete="off"
                    maxlength="100" />

            </section>
        </header>

        <div class="relative w-full">

            {{-- New Group button --}}
            @if (WireChat::showNewGroupModalButton() && auth()->user()->canCreateGroups())
                {{-- Buton to trigger opening of new grop modal --}}
                <x-wirechat::actions.new-group widget="{{ $this->isWidget() }}">
                    <button @dusk="open_new_group_modal_button"
                        class="flex items-center gap-3 my-4  rounded-lg p-2 w-full border bg-white hover:bg-[var(--wc-light-secondary)] cursor-pointer transition-colors border-[var(--wc-light-border)]   hover:border-[var(--wc-light-secondary)] ">
                        <span style=" color: var(--wc-brand-primary); " class="p-1 bg-gray-100  rounded-full ">

                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"
                                class="   w-5 h-5">
                                <path fill-rule="evenodd"
                                    d="M8.25 6.75a3.75 3.75 0 1 1 7.5 0 3.75 3.75 0 0 1-7.5 0ZM15.75 9.75a3 3 0 1 1 6 0 3 3 0 0 1-6 0ZM2.25 9.75a3 3 0 1 1 6 0 3 3 0 0 1-6 0ZM6.31 15.117A6.745 6.745 0 0 1 12 12a6.745 6.745 0 0 1 6.709 7.498.75.75 0 0 1-.372.568A12.696 12.696 0 0 1 12 21.75c-2.305 0-4.47-.612-6.337-1.684a.75.75 0 0 1-.372-.568 6.787 6.787 0 0 1 1.019-4.38Z"
                                    clip-rule="evenodd" />
                                <path
                                    d="M5.082 14.254a8.287 8.287 0 0 0-1.308 5.135 9.687 9.687 0 0 1-1.764-.44l-.115-.04a.563.563 0 0 1-.373-.487l-.01-.121a3.75 3.75 0 0 1 3.57-4.047ZM20.226 19.389a8.287 8.287 0 0 0-1.308-5.135 3.75 3.75 0 0 1 3.57 4.047l-.01.121a.563.563 0 0 1-.373.486l-.115.04c-.567.2-1.156.349-1.764.441Z" />
                            </svg>
                        </span>

                        <p class="text-sm">@lang('wirechat::new.chat.actions.new_group.label')</p>
                    </button>
                </x-wirechat::actions.new-group>
            @endif
            {{-- <h5 class="text font-semibold text-gray-800 dark:text-gray-100">Recent Chats</h5> --}}
            <section class="my-4 grid">
                @if (count($users) != 0)

                    <ul class="overflow-auto flex flex-col">

                        @foreach ($users as $key => $user)
                            <li wire:key="user-{{ $key }}"
                                wire:click="createConversation('{{ $user->id }}',{{ json_encode(get_class($user)) }})"
                                class="flex cursor-pointer group gap-2 text-sm items-center p-2">

                                <x-wirechat::avatar :src="$user->cover_url" class="w-10 h-10" />

                                <p class="group-hover:underline transition-all">
                                    {{ $user->display_name }}</p>

                            </li>
                        @endforeach


                    </ul>
                @else
                    @if (!empty($search))
                        <span class="m-auto">@lang('wirechat::new.chat.messages.empty_search_result')</span>
                    @endif
                @endif

            </section>
        </div>
    </div>
</div>
