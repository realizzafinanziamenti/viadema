{{-- Bell Icon Button --}}
<button class="p-1 mx-2.5 relative cursor-pointer" x-on:click="$dispatch('open-modal', 'notification-modal')"
    title="Notifiche">
    <x-icons.icon-akar-bell />

    @if ($unreadNotificationsCount > 0)
        <div
            class="absolute right-[3px] bottom-[19px] flex items-center justify-center w-3 h-3 text-[10px] rounded-full bg-orange-custom">
        </div>
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
