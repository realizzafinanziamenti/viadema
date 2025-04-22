<x-layouts.app.sidebar :title="$title ?? null">
    <flux:main class="ps-4! py-6! pe-8!">
        {{ $slot }}
    </flux:main>
</x-layouts.app.sidebar>
