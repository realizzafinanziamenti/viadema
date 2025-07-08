<div class="w-full">
    <x-page-title label="Profilo Utente" />

    <x-card class="max-w-3xl mx-auto">
        <x-card-header class="mb-5" label="Dettaglio utente" />

        <div class="grid grid-cols-4 gap-5">
            <div class="col-span-1">
                <img src="{{ auth()->user()->getProfilePhotoUrl() }}" alt="{{ auth()->user()->full_name }}"
                    class="w-40 h-40" />
            </div>

            <div class="col-span-3">
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
            </div>
        </div>

        {{-- Buttons --}}
        @can('update profile')
            <div class="flex items-center justify-end gap-x-3 mt-18">
                <flux:button variant="primary" type="button" size="sm"
                    x-on:click="dispatch('open-modal', 'change-password-modal')"
                    class="px-10 bg-orange-custom border-orange-custom text-white hover:bg-orange-custom-hover hover:border-orange-custom-hover">
                    Cambia password
                </flux:button>

                <flux:button variant="primary" type="submit" size="sm"
                    class="px-10 bg-azure-custom border-azure-custom hover:bg-azure-custom-hover hover:border-azure-custom-hover">
                    Modifica
                </flux:button>
            </div>
        @endcan
    </x-card>
</div>
