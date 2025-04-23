<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @include('partials.head')
</head>

<body class="min-h-screen bg-gray-custom-1">
    <flux:sidebar sticky stashable class="border-e border-zinc-200 bg-white w-[264px] gap-3.5! px-0">
        {{-- <flux:sidebar.toggle class="lg:hidden" icon="x-mark" /> --}}

        {{-- Sidebar Logo --}}
        <div class="flex items-end justify-center py-2">
            <img src="{{ asset('images/viadema-logo.png') }}" alt="Logo" class="h-[105px] w-auto">
        </div>

        <flux:navlist class="overflow-y-auto ps-6 pe-4 scrollbar-none hover:scrollbar-thin pb-6">
            {{-- Sidebar Search --}}
            {{-- HOME --}}
            <flux:navlist.group heading="Home" class="grid">
                <x-sidebar-item route="dashboard" routeIs="dashboard" label="Dashboard">
                    <x-icons.icon-akar-home />
                </x-sidebar-item>
            </flux:navlist.group>

            {{-- CRM --}}
            <flux:navlist.group heading="Crm" class="grid">
                {{-- Products --}}
                <flux:navlist.group heading="Prodotti" class="grid mb-0!" expandable customIcon="icon-akar-folder">
                    <x-sidebar-item route="dashboard" routeIs="#" label="Cessione del Quinto" bullet />

                    <x-sidebar-item route="dashboard" routeIs="#" label="Delegazione di Pagamento" bullet />

                    <x-sidebar-item route="dashboard" routeIs="#" label="Mutui" bullet />

                    <x-sidebar-item route="dashboard" routeIs="#" label="Prestiti" bullet />
                </flux:navlist.group>

                <x-sidebar-item route="dashboard" routeIs="#" label="Simulatore">
                    <x-icons.icon-akar-star />
                </x-sidebar-item>

                <x-sidebar-item route="dashboard" routeIs="#" label="Gestione Pratiche">
                    <x-icons.icon-akar-paper />
                </x-sidebar-item>

                <x-sidebar-item route="dashboard" routeIs="#" label="Archivio Pratiche">
                    <x-icons.icon-akar-inbox />
                </x-sidebar-item>

                <x-sidebar-item route="dashboard" routeIs="#" label="Anagrafica Clienti">
                    <x-icons.icon-akar-people-group />
                </x-sidebar-item>

                <x-sidebar-item route="dashboard" routeIs="#" label="Leads">
                    <x-icons.icon-akar-draft />
                </x-sidebar-item>

                <x-sidebar-item route="dashboard" routeIs="#" label="Modulistica">
                    <x-icons.icon-akar-clipboard />
                </x-sidebar-item>
            </flux:navlist.group>

            {{-- CALENDAR --}}
            <flux:navlist.group heading="Calendario" class="grid">
                <x-sidebar-item route="dashboard" routeIs="#" label="Calendario">
                    <x-icons.icon-akar-calendar />
                </x-sidebar-item>

                <x-sidebar-item route="dashboard" routeIs="#" label="Elenco Eventi">
                    <x-icons.icon-akar-grid />
                </x-sidebar-item>
            </flux:navlist.group>

            {{-- MANAGEMENT --}}
            <flux:navlist.group heading="Gestione" class="grid">
                @can('access team members')
                    <x-sidebar-item route="user.team.index" routeIs="user.team" label="Gestione Collaboratori">
                        <x-icons.icon-akar-people-multiple />
                    </x-sidebar-item>
                @endcan

                <x-sidebar-item route="dashboard" routeIs="#" label="Obiettivi & Report">
                    <x-icons.icon-akar-statistic-up />
                </x-sidebar-item>

                <x-sidebar-item route="dashboard" routeIs="#" label="Impostazioni">
                    <x-icons.icon-akar-settings-horizontal />
                </x-sidebar-item>

                <x-sidebar-item route="dashboard" routeIs="#" label="Chat Assistenza">
                    <x-icons.icon-akar-settings-horizontal />
                </x-sidebar-item>
            </flux:navlist.group>
        </flux:navlist>

        {{-- <div class="py-3"></div> --}}
    </flux:sidebar>

    <flux:header class="flex justify-end px-4 text-white bg-azure-custom h-[78px] sm:px-6 lg:px-10 xl:px-20">
        <div class="flex">
            {{-- Circle Plus Button --}}
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
        <div class="ms-3.5">
            <a href="#" wire:navigate
                class="flex items-center h-full gap-5 p-2 text-sm leading-4 transition duration-150 ease-in-out rounded-full">
                <div class="flex items-center">
                    <img class="object-cover w-10 h-10 bg-white rounded-full"
                        src="{{ auth()->user()->getProfilePhotoUrl() }}" alt="Profile Photo">
                </div>

                <div class="flex flex-col items-start gap-1">
                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf

                        <div x-data="{{ json_encode(['name' => auth()->user()->full_name]) }}" x-text="name" class="font-semibold"
                            x-on:profile-updated.window="name = $event.detail.name">
                        </div>
                    </form>


                    <div x-data="{{ json_encode(['role' => auth()->user()->getRoleDescription()]) }}" x-text="role" x-on:profile-updated.window="role = $event.detail.role"
                        class="font-extralight"></div>
                </div>
            </a>
        </div>
    </flux:header>

    {{ $slot }}

    @fluxScripts
</body>

</html>
