<div>
    <x-page-title label="Leads" class="mt-1" />

    <x-card>
        {{-- Filters and Create Button --}}
        <div class="flex items-center justify-between mb-5">
            <flux:input class="w-sm! xl:w-lg!" wire:model.live.debounce.500ms='search' icon:trailing="magnifying-glass"
                placeholder="Cerca per nome, cognome..." />

            @can('create leads')
                <a href="{{ route('lead.create') }}" wire:navigate>
                    <x-buttons.create-button label="Crea nuovo profilo" />
                </a>
            @endcan
        </div>

    </x-card>
</div>
