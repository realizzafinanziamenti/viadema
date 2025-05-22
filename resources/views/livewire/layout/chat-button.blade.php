<button class="p-1 mx-2.5 relative">
    <a href="/admin/chats" wire:navigate>
        <x-icons.icon-akar-chat-dots />
    </a>

    @if ($showNotificationBadge)
        <a href="/admin/chats" wire:navigate
            class="absolute right-1 bottom-[26px] flex items-center justify-center w-2 h-2 text-[10px] rounded-full bg-white">
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
