<button class="p-1 mx-2.5 relative cursor-pointer" title="Chat">
    <a href="/chats" wire:navigate>
        <x-icons.icon-akar-chat-dots />
    </a>

    @if ($showNotificationBadge)
        <a href="/chats" wire:navigate
            class="absolute right-0 bottom-[19px] flex items-center justify-center w-3 h-3 text-[10px] rounded-full bg-orange-custom">
        </a>
    @endif

    <audio id="notificationSound">
        <source src="{{ asset('sounds/notification-sound.mp3') }}" type="audio/mpeg">
    </audio>

    @script
        <script>
            $wire.on('play-notification-sound', () => {
                let sound = document.getElementById('notificationSound');
                if (sound) {
                    sound.play().catch(error => console.error("Errore nel riprodurre l'audio:", error));
                }
            });
        </script>
    @endscript
</button>
