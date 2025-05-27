@use('Namu\WireChat\Facades\WireChat')


@php

    $isSameAsNext =
        $message?->sendable_id === $nextMessage?->sendable_id &&
        $message?->sendable_type === $nextMessage?->sendable_type;
    $isNotSameAsNext = !$isSameAsNext;
    $isSameAsPrevious =
        $message?->sendable_id === $previousMessage?->sendable_id &&
        $message?->sendable_type === $previousMessage?->sendable_type;
    $isNotSameAsPrevious = !$isSameAsPrevious;
@endphp

<div {{-- We use style here to make it easy for dynamic and safe injection --}} @style([
    'background-color:var(--wc-brand-primary)' => $belongsToAuth == true,
]) @class([
    'flex  max-w-fit text-sm shadow-sm rounded-xl px-3 break-words whitespace-normal py-1.5 flex flex-col text-black bg-gray-1-custom',
    'text-gray-5-custom' => $belongsToAuth, // Background color for messages sent by the authenticated user
    'bg-gray-1-custom dark:text-white' => !$belongsToAuth,

    // Message styles based on position and ownership

    // RIGHT
    // First message on RIGHT
    'rounded-br-md rounded-tr-2xl' =>
        $isSameAsNext && $isNotSameAsPrevious && $belongsToAuth,

    // Middle message on RIGHT
    'rounded-r-md' => $isSameAsPrevious && $belongsToAuth,

    // Standalone message RIGHT
    'rounded-br-xl rounded-r-xl' =>
        $isNotSameAsPrevious && $isNotSameAsNext && $belongsToAuth,

    // Last Message on RIGHT
    'rounded-br-2xl' => $isNotSameAsNext && $belongsToAuth,

    // LEFT
    // First message on LEFT
    'rounded-bl-md rounded-tl-2xl' =>
        $isSameAsNext && $isNotSameAsPrevious && !$belongsToAuth,

    // Middle message on LEFT
    'rounded-l-md' => $isSameAsPrevious && !$belongsToAuth,

    // Standalone message LEFT
    'rounded-bl-xl rounded-l-xl' =>
        $isNotSameAsPrevious && $isNotSameAsNext && !$belongsToAuth,

    // Last message on LEFT
    'rounded-bl-2xl' => $isNotSameAsNext && !$belongsToAuth,
])>
    @if (!$belongsToAuth && $isGroup)
        <div @class([
            'shrink-0 font-medium text-purple-500',
            // Hide avatar if the next message is from the same user
            'hidden' => $isSameAsPrevious,
        ])>
            {{ $message?->sendable?->display_name }}
        </div>
    @endif

    <pre class="whitespace-pre-line tracking-normal text-sm  dark:text-white lg:tracking-normal"
        style="font-family: inherit;">
    {{ $message?->body }}
</pre>

    {{-- Display the created time based on different conditions --}}
    <span @class([
        'text-[10px] ml-auto ',
        'text-gray-5-custom' => !$belongsToAuth,
        'text-gray-5-custom' => $belongsToAuth,
    ])>
        @php
            // If the message was created today, show only the time (e.g., 1:00 AM)
            echo $message?->created_at->locale('it')->translatedFormat('H:i');
        @endphp
    </span>

</div>
