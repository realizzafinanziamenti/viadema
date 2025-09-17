<div class="w-full">
    <div class="flex items-center justify-between mb-2.5">
        <x-button-back class="mb-2.5" route="lead.index" />

        <a href="{{ route('lead.edit', ['id' => $lead->id]) }}" wire:navigate>
            <flux:button variant="primary" type="submit" size="sm"
                class="px-10 bg-azure-custom border-azure-custom hover:bg-azure-custom-hover hover:border-azure-custom-hover">
                Modifica
            </flux:button>
        </a>
    </div>

    <x-page-title label="Dettaglio Lead" />

    <x-card class="max-w-3xl mx-auto">
        <x-card-header class="mb-6" label="Informazioni generali" />

        <div class="text-sm mb-2.5">
            <span class="text-gray-custom-4">Nome: </span>
            <span>{{ $lead->full_name }}</span>
        </div>

        @if ($lead->customerType)
            <div class="text-sm mb-2.5">
                <span class="text-gray-custom-4">Tipologia: </span>
                <span>{{ $lead->customerType?->name }}</span>
            </div>
        @endif

        @if ($lead->lead_status)
            <div class="text-sm mb-2.5 flex items-center gap-2">
                <span class="text-gray-custom-4">Stato lead: </span>
                <x-clickable-badge :property="$lead->lead_status?->getLabelText()" :css="$lead->lead_status?->getLabelColor()" wire:click="openUpdateLeadStatusModal" />
            </div>
        @endif

        @if ($lead->lead_source)
            <div class="text-sm mb-2.5">
                <span class="text-gray-custom-4">Canale di acquisizione: </span>
                <span>{{ $lead->lead_source?->getLabelText() }}</span>
            </div>
        @endif

        <div class="text-sm mb-2.5">
            <span class="text-gray-custom-4">Cellulare: </span>
            <span>{{ $lead->phone }}</span>
        </div>

        @if ($lead->email)
            <div class="text-sm mb-2.5">
                <span class="text-gray-custom-4">Email: </span>
                <span>{{ $lead->email }}</span>
            </div>
        @endif

        @if ($lead->date_of_birth)
            <div class="text-sm mb-2.5">
                <span class="text-gray-custom-4">Data di Nascita: </span>
                <span>{{ $lead->formatted_date_of_birth }}</span>
            </div>
        @endif

        @if ($lead->address)
            <div class="text-sm mb-2.5">
                <span class="text-gray-custom-4">Indirizzo: </span>
                <span>{{ $lead->address }}</span>
            </div>
        @endif

        @if ($lead->postal_code)
            <div class="text-sm mb-2.5">
                <span class="text-gray-custom-4">Cap: </span>
                <span>{{ $lead->postal_code }}</span>
            </div>
        @endif

        @if ($lead->city)
            <div class="text-sm mb-2.5">
                <span class="text-gray-custom-4">Città: </span>
                <span>{{ $lead->city }}</span>
            </div>
        @endif

        @if ($lead->state)
            <div class="text-sm mb-2.5">
                <span class="text-gray-custom-4">Provincia: </span>
                <span>{{ $lead->state }}</span>
            </div>
        @endif

        @if ($lead->tax_id)
            <div class="text-sm mb-2.5">
                <span class="text-gray-custom-4">Codice Fiscale: </span>
                <span>{{ $lead->tax_id }}</span>
            </div>
        @endif

        @if ($lead->amount)
            <div class="text-sm mb-2.5">
                <span class="text-gray-custom-4">Importo: </span>
                <span>{{ $lead->formatted_amount }}</span>
            </div>
        @endif

        {{-- Create/Associate Practice Buttons --}}
        @if ($lead->lead_status === \App\Enums\LeadStatus::FEASIBLE)
            <div class="flex gap-3 justify-end mt-16 mb-5">
                @can('create', App\Models\Practice::class)
                    <flux:button variant="primary" type="submit" size="sm" wire:click='createPracticeFromLead'
                        class="px-10 bg-azure-custom border-azure-custom hover:bg-azure-custom-hover hover:border-azure-custom-hover">
                        Crea pratica
                    </flux:button>
                @endcan
            </div>
        @endif
    </x-card>

    {{-- Update Lead Status Modal --}}
    @include('partials.customer.update-lead-status-modal')
</div>
