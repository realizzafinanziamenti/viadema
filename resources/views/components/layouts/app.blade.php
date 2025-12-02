<x-layouts.app.sidebar :title="$title ?? null">
    <flux:main class="ps-4! py-4! pe-6! flex-1 overflow-y-auto">
        {{ $slot }}
    </flux:main>
</x-layouts.app.sidebar>
