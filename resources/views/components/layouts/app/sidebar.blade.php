<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @include('partials.head')

    @wirechatStyles
</head>

<body class="min-h-screen bg-gray-custom-1 flex">
    <flux:sidebar stashable
        class="border-e border-zinc-200 bg-white w-[264px] shrink-0 gap-3.5! px-0 h-screen overflow-y-auto">
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
                {{-- Practices --}}
                <flux:navlist.group heading="Prodotti" class="grid mb-0!" expandable customIcon="icon-akar-folder">
                    @php
                        $productTypes = App\Models\ProductType::all();
                    @endphp

                    @foreach ($productTypes as $type)
                        <x-sidebar-item :route="['practice.index', ['slug' => $type->slug]]" :activeWhenSlug="$type->slug" routeIs="practice"
                            label="{{ $type->name }}" bullet />
                    @endforeach
                </flux:navlist.group>

                <x-sidebar-item route="dashboard" routeIs="#" label="Simulatore">
                    <x-icons.icon-akar-star />
                </x-sidebar-item>

                <x-sidebar-item route="practice.index" routeIs="practice" label="Gestione Pratiche">
                    <x-icons.icon-akar-paper />
                </x-sidebar-item>

                <x-sidebar-item :route="['practice.index', ['expired' => 1]]" :activeWhenExpired="true" routeIs="practice" label="Archivio Pratiche">
                    <x-icons.icon-akar-inbox />
                </x-sidebar-item>

                @can('access customers')
                    <x-sidebar-item route="customer.index" routeIs="customer" label="Anagrafica Clienti">
                        <x-icons.icon-akar-people-group />
                    </x-sidebar-item>
                @endcan

                @can('access leads')
                    <x-sidebar-item route="lead.index" routeIs="lead" label="Leads">
                        <x-icons.icon-akar-draft />
                    </x-sidebar-item>
                @endcan

                <x-sidebar-item route="dashboard" routeIs="#" label="Modulistica">
                    <x-icons.icon-akar-clipboard />
                </x-sidebar-item>
            </flux:navlist.group>

            {{-- CALENDAR --}}
            <flux:navlist.group heading="Calendario" class="grid">
                @can('access calendar')
                    <x-sidebar-item route="calendar" routeIs="calendar" label="Calendario">
                        <x-icons.icon-akar-calendar />
                    </x-sidebar-item>
                @endcan

                @can('access events')
                    <x-sidebar-item route="event.index" routeIs="event" label="Elenco Eventi">
                        <x-icons.icon-akar-grid />
                    </x-sidebar-item>
                @endcan
            </flux:navlist.group>

            {{-- MANAGEMENT --}}
            <flux:navlist.group heading="Gestione" class="grid">
                @can('access users')
                    <x-sidebar-item route="user.index" routeIs="user" label="Gestione Collaboratori">
                        <x-icons.icon-akar-people-multiple />
                    </x-sidebar-item>
                @endcan

                <x-sidebar-item route="dashboard" routeIs="#" label="Obiettivi & Report">
                    <x-icons.icon-akar-statistic-up />
                </x-sidebar-item>

                @can('access settings')
                    <x-sidebar-item route="setting.index" routeIs="setting" label="Impostazioni">
                        <x-icons.icon-akar-settings-horizontal />
                    </x-sidebar-item>
                @endcan

                <x-sidebar-item route="dashboard" routeIs="#" label="Chat Assistenza">
                    <x-icons.icon-akar-settings-horizontal />
                </x-sidebar-item>
            </flux:navlist.group>
        </flux:navlist>

        {{-- <div class="py-3"></div> --}}
    </flux:sidebar>

    <div class="flex flex-col w-full h-screen overflow-hidden">
        {{-- Header --}}
        <flux:header
            class="flex justify-end px-4 text-white bg-azure-custom h-[78px] shrink-0 sm:px-6 lg:px-10 xl:px-20">
            <div class="flex">
                {{-- Circle Plus Button --}}
                <button class="p-1 mx-2.5">
                    <x-icons.icon-akar-circle-plus />
                </button>

                {{-- Chat Button --}}
                <livewire:layout.chat-button />

                {{-- Notification Button and Drawer --}}
                <livewire:layout.notification-button />
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


                        <div x-data="{{ json_encode(['role' => auth()->user()->getRoleDescription()]) }}" x-text="role"
                            x-on:profile-updated.window="role = $event.detail.role" class="font-extralight"></div>
                    </div>
                </a>
            </div>
        </flux:header>

        {{-- Notification Modal --}}
        <livewire:layout.notification-modal />

        {{ $slot }}
    </div>

    @fluxScripts

    {{-- scripts for filepond library --}}
    @filepondScripts

    {{-- TOASTER --}}
    {{-- Needed to livewire toaster library --}}
    @persist('toaster')
        <x-toaster-hub />
    @endpersist

    {{-- Necessary for persisting the toaster --}}
    <style>
        div[x-persist="toaster"] {
            position: fixed;
        }
    </style>
    {{-- END TOASTER --}}

    @wirechatAssets
</body>

</html>
