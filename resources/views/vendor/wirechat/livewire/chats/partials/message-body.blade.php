<div class="flex gap-x-2 items-center">

    {{-- Only show if AUTH is onwer of message --}}
    @if ($belongsToAuth)
        <span class="font-semibold text-[13px] text-black-custom">
            @lang('wirechat::chats.labels.you'):
        </span>
    @elseif(!$belongsToAuth && $group !== null)
        <span class="font-semibold text-[13px] text-black-custom">
            {{ $lastMessage->sendable?->display_name }}:
        </span>
    @endif

    <p @class([
        'truncate text-[13px] dark:text-white  gap-2 items-center',
        'font-bold text-black-custom' =>
            !$isReadByAuth && !$lastMessage?->ownedBy($this->auth),
        'font-normal text-gray-custom-5' =>
            $isReadByAuth && !$lastMessage?->ownedBy($this->auth),
        'font-normal text-gray-custom-5' =>
            $isReadByAuth && $lastMessage?->ownedBy($this->auth),
    ])>
        {{ $lastMessage->body != '' ? $lastMessage->body : ($lastMessage->isAttachment() ? '📎 ' . __('wirechat::chats.labels.attachment') : '') }}
    </p>

</div>
