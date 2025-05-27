@use('Namu\WireChat\Facades\WireChat')

<ul wire:loading.delay.long.remove wire:target="search" class="grid w-full overflow-x-hidden spacey-y-2">
    @foreach ($conversations as $key => $conversation)
        @php
            //$receiver =$conversation->getReceiver();
            $group = $conversation->isGroup() ? $conversation->group : null;
            $receiver = $conversation->isGroup()
                ? null
                : ($conversation->isPrivate()
                    ? $conversation->peer_participant?->participantable
                    : $this->auth);
            //$receiver = $conversation->isGroup() ? null : ($conversation->isPrivate() ? $conversation->peerParticipant()?->participantable : $this->auth);
            $lastMessage = $conversation->lastMessage;
            //mark isReadByAuth true if user has chat opened
            $isReadByAuth =
                $conversation?->readBy($conversation->auth_participant ?? $this->auth) ||
                $selectedConversationId == $conversation->id;
            $belongsToAuth = $lastMessage?->belongsToAuth();

        @endphp

        <li x-data="{
            conversationID: @js($conversation->id),
            showUnreadStatus: @js(!$isReadByAuth),
            handleChatOpened(event) {
                // Hide unread dot
                if (event.detail.conversation == this.conversationID) {
                    this.showUnreadStatus = false;
                }
                //update this so that the the selected conversation highlighter can be updated
                $wire.selectedConversationId = event.detail.conversation;
            },
            handleChatClosed(event) {
                // Clear the globally selected conversation.
                $wire.selectedConversationId = null;
                selectedConversationId = null;
            },
            handleOpenChat(event) {
                // Clear the globally selected conversation.
                if (this.showUnreadStatus == event.detail.conversation == this.conversationID) {
                    this.showUnreadStatus = false;
                }
            }
        }" id="conversation-{{ $conversation->id }}" class="w-full truncate"
            wire:key="conversation-em-{{ $conversation->id }}-{{ $conversation->updated_at->timestamp }}"
            x-on:chat-opened.window="handleChatOpened($event)" x-on:chat-closed.window="handleChatClosed($event)">
            <a @if ($widget) tabindex="0"
        role="button"
        dusk="openChatWidgetButton"
        @click="$dispatch('open-chat',{conversation:@js($conversation->id)})"
        @keydown.enter="$dispatch('open-chat',{conversation:@js($conversation->id)})"
        @else
        wire:navigate href="{{ route(WireChat::viewRouteName(), $conversation->id) }}" @endif
                @style(['border-orange-custom' => $selectedConversationId == $conversation?->id])
                class="py-2 flex gap-2 hover:bg-gray-custom-1  rounded-xs transition-colors duration-150  relative w-full cursor-pointer "
                :class="$wire.selectedConversationId == conversationID &&
                    ' bg-gray-custom-1 border-r-4  border-opacity-20 border-orange-custom'">

                <div class="shrink-0 flex items-center">
                    <x-wirechat::avatar disappearing="{{ $conversation->hasDisappearingTurnedOn() }}"
                        group="{{ $conversation->isGroup() }}" :src="$group ? $group?->cover_url : $receiver?->cover_url ?? null" class="w-12 h-12" />
                </div>

                <aside class="flex gap-x-3 flex-1 truncate">
                    <div class=" relative overflow-hidden truncate flex-1  p-1">

                        {{-- name --}}
                        <div class="flex gap-1 mb-0.5 w-full truncate items-center">
                            <h6 class="truncate font-medium text-black-custom text-sm dark:text-white">
                                {{ $group ? $group?->name : $receiver?->display_name }}
                            </h6>

                            @if ($conversation->isSelfConversation())
                                <span
                                    class="font-medium dark:text-white">({{ __('wirechat::chats.labels.you') }})</span>
                            @endif

                        </div>

                        {{-- Message body --}}
                        @if ($lastMessage != null)
                            @include('wirechat::livewire.chats.partials.message-body')
                        @endif

                    </div>

                    <div class="shrink-0 p-1 pe-2 flex flex-col items-end">
                        {{--  --}}
                        <span class="font-medium  text-sm shrink-0 text-gray-custom-5 dark:text-gray-50">
                            @if ($lastMessage?->created_at?->diffInMinutes(now()) < 1)
                                @lang('wirechat::chats.labels.now')
                            @else
                                {{ $lastMessage?->created_at?->shortAbsoluteDiffForHumans() }}
                            @endif
                        </span>

                        {{-- Read status --}}
                        {{-- Only show if AUTH is NOT onwer of message --}}
                        @if ($lastMessage != null && !$lastMessage?->ownedBy($this->auth) && !$isReadByAuth)
                            <div x-show="showUnreadStatus" dusk="unreadMessagesDot" class=" shrink-0">
                                {{-- Unread dot --}}
                                <div
                                    class="bg-orange-custom text-white mt-2 w-2 h-2 rounded-full text-[11px] flex items-center justify-center font-semibold">

                                </div>
                            </div>
                        @endif
                    </div>


                </aside>
            </a>

        </li>
    @endforeach

</ul>
