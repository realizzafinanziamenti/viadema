<div class="w-full">
    <x-page-title label="Profilo Utente" />

    <x-card class="max-w-3xl mx-auto">
        <x-card-header class="mb-6" label="Dati utente" />

        <div class="mb-2.5">
            <img src="{{ auth()->user()->getProfilePhotoUrl() }}" alt="{{ auth()->user()->full_name }}"
                class="w-auto h-50 rounded-lg border bg-white" />
        </div>

        <div class="text-sm mb-2.5">
            <span class="text-gray-custom-4">Nome: </span>
            <span>{{ auth()->user()->full_name }}</span>
        </div>

        <div class="text-sm mb-2.5">
            <span class="text-gray-custom-4">Email: </span>
            <span>{{ auth()->user()->email }}</span>
        </div>

        @if (auth()->user()->profile)
            <div class="text-sm mb-2.5">
                <span class="text-gray-custom-4">Cellulare: </span>
                <span>{{ auth()->user()->phone ?? 'N/D' }}</span>
            </div>

            <div class="text-sm mb-2.5">
                <span class="text-gray-custom-4">Codice fiscale: </span>
                <span>{{ auth()->user()->tax_id ?? 'N/D' }}</span>
            </div>

            <div class="text-sm mb-2.5">
                <span class="text-gray-custom-4">Città: </span>
                <span>{{ auth()->user()->city ?? 'N/D' }}</span>
            </div>
        @endif

        {{-- Buttons --}}
        @can('update profile')
            <div class="flex items-center justify-end gap-x-3 mt-18 mb-5">
                <a href="{{ route('profile.edit.password') }}" wire:navigate>
                    <flux:button variant="primary" type="button" size="sm"
                        x-on:click="dispatch('open-modal', 'change-password-modal')"
                        class="px-10 bg-orange-custom border-orange-custom text-white hover:bg-orange-custom-hover hover:border-orange-custom-hover">
                        Cambia password
                    </flux:button>
                </a>

                <a href="{{ route('profile.edit') }}" wire:navigate>
                    <flux:button variant="primary" type="submit" size="sm"
                        class="px-10 bg-azure-custom border-azure-custom hover:bg-azure-custom-hover hover:border-azure-custom-hover">
                        Modifica
                    </flux:button>
                </a>
            </div>
        @endcan
    </x-card>
</div>
