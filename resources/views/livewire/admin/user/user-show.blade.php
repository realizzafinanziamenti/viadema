<div class="w-full">
    <div class="flex items-center justify-between mb-2.5">
        <x-button-back class="mb-2.5" route="user.index" />

        @can('update', $user)
            <a href="{{ route('user.edit', ['id' => $user->id]) }}" wire:navigate>
                <flux:button variant="primary" type="submit" size="sm"
                    class="px-10 bg-azure-custom border-azure-custom hover:bg-azure-custom-hover hover:border-azure-custom-hover">
                    Modifica
                </flux:button>
            </a>
        @endcan
    </div>

    <x-page-title label="Dettaglio Collaboratore" />

    <x-card class="w-3xl mx-auto">
        <x-card-header class="mb-6" label="Informazioni generali" />

        <div class="mb-2.5">
            <img src="{{ $user->getProfilePhotoUrl() }}" alt="{{ $user->full_name }}"
                class="w-auto h-50 rounded-lg border bg-white" />
        </div>

        <div class="text-sm mb-2.5">
            <span class="text-gray-custom-4">Nome: </span>
            <span>{{ $user->full_name }}</span>
        </div>

        <div class="text-sm mb-2.5">
            <span class="text-gray-custom-4">Email: </span>
            <span>{{ $user->email }}</span>
        </div>

        <div class="text-sm mb-2.5">
            <span class="text-gray-custom-4">Cellulare: </span>
            <span>{{ $user->profile?->phone }}</span>
        </div>

        <div class="text-sm mb-2.5">
            <span class="text-gray-custom-4">Codice Fiscale: </span>
            <span>{{ $user->profile?->tax_id }}</span>
        </div>

        <div class="text-sm mb-2.5">
            <span class="text-gray-custom-4">Città: </span>
            <span>{{ $user->profile?->city }}</span>
        </div>
    </x-card>
</div>
