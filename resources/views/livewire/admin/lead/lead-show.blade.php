<div class="w-full">
    <x-button-back class="mb-2.5" route="lead.index" />
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

        @if ($lead->lead_communication)
            <div class="text-sm mb-2.5">
                <span class="text-gray-custom-4">Comunicazioni: </span>
                <span>{{ $lead->lead_communication?->getLabelText() }}</span>
            </div>
        @endif

        @if ($lead->lead_status)
            <div class="text-sm mb-2.5">
                <span class="text-gray-custom-4 uppercase {{ $lead->lead_status?->getLabelColor() }}">Stato:
                </span>
                <span>{{ $lead->lead_status?->getLabelText() }}</span>
            </div>
        @endif

        @if ($lead->lead_source)
            <div class="text-sm mb-2.5">
                <span class="text-gray-custom-4">Canale di acquisizione: </span>
                <span>{{ $lead->lead_communication?->getLabelText() }}</span>
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
    </x-card>
</div>
