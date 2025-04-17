<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @include('partials.head')
</head>

<body class="min-h-screen bg-white dark:bg-zinc-800">
    <flux:sidebar sticky stashable class="border-e border-zinc-200 bg-white">
        <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

        <div class="flex items-end justify-center py-2">
            <img src="{{ asset('images/viadema-logo.png') }}" alt="Logo" class="h-[105px]">
        </div>

        <flux:navlist>
            <flux:navlist.group heading="Dashboard" class="grid">
                <x-sidebar-item route="dashboard" routeIs="dashboard" label="Dashboard">
                    <x-icons.icon-akar-home />
                </x-sidebar-item>
            </flux:navlist.group>
        </flux:navlist>
    </flux:sidebar>

    <flux:header class="flex justify-end px-4 text-white bg-azure-custom h-[78px] sm:px-6 lg:px-10 xl:px-20">
        {{-- Settings and notify buttons --}}
        <div class="flex">
            {{-- Chat Button --}}
            <button class="p-1 mx-2.5">
                <x-icons.icon-akar-circle-plus />
            </button>

            {{-- Chat Button --}}
            <button class="p-1 mx-2.5">
                <x-icons.icon-akar-chat-dots />
            </button>

            {{-- Notification Button and Drawer --}}
            <button class="p-1 mx-2.5">
                <x-icons.icon-akar-bell />
            </button>
        </div>

        <!-- Profile button -->
        <div class="ms-4">
            <a href="#" wire:navigate
                class="flex items-center h-full gap-5 p-2 text-sm leading-4 transition duration-150 ease-in-out rounded-full">
                <div class="flex items-center">
                    <img class="object-cover w-10 h-10 bg-white rounded-full" src="" alt="Profile Photo">
                </div>

                <div class="flex flex-col items-start gap-1">
                    <div x-data="{{ json_encode(['name' => auth()->user()->name]) }}" x-text="name" x-on:profile-updated.window="name = $event.detail.name">
                    </div>

                    {{-- <div x-data="{{ json_encode(['role' => auth()->user()->getRoleDescription()]) }}" x-text="role" x-on:profile-updated.window="role = $event.detail.role"
                        class="text-xs font-extralight"></div> --}}
                </div>
            </a>
        </div>
    </flux:header>

    {{ $slot }}

    @fluxScripts
</body>

</html>
