<div class="w-full">
    <div class="flex items-center justify-between mb-2.5">
        <x-button-back class="mb-2.5" route="lead.index" />

        @can('update', $lead)
            <a href="{{ route('lead.edit', ['id' => $lead->id]) }}" wire:navigate>
                <flux:button variant="primary" type="submit" size="sm"
                    class="px-10 bg-azure-custom border-azure-custom hover:bg-azure-custom-hover hover:border-azure-custom-hover">
                    Modifica
                </flux:button>
            </a>
        @endcan
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
                @if (Gate::allows('updateLeadStatus', $lead))
                    <x-clickable-badge :property="$lead->lead_status?->getLabelText()" :css="$lead->lead_status?->getLabelColor()" wire:click="openUpdateLeadStatusModal" />
                @else
                    <x-badge :property="$lead->lead_status?->getLabelText()" :css="$lead->lead_status?->getLabelColor()" />
                @endif
            </div>
        @endif

        <div class="text-sm mb-2.5">
            <span class="text-gray-custom-4">Data ricontatto: </span>
            <span>{{ $lead->recontact_date?->format('d/m/Y') ?? 'N/D' }}</span>
        </div>


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

        @if ($lead->notes)
            <div class="text-sm mb-2.5">
                <span class="text-gray-custom-4">Note: </span>
                <span>{{ $lead->notes }}</span>
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
    @php
    $opportunity = $lead->practiceOpportunities->sortByDesc('created_at')->first();
@endphp

@if ($opportunity)
    <x-card class="max-w-3xl mx-auto mt-4">
        <x-card-header class="mb-6" label="Dati pratica" />

        <div class="text-sm mb-2.5">
            <span class="text-gray-custom-4">Ente erogante: </span>
            <span>{{ $opportunity->disbursing_institution ?? 'N/D' }}</span>
        </div>

        <div class="text-sm mb-2.5">
            <span class="text-gray-custom-4">Prodotto: </span>
            <span class="font-bold">{{ $opportunity->productType?->name ?? 'N/D' }}</span>
        </div>

        <div class="text-sm mb-2.5">
            <span class="text-gray-custom-4">Tipo prodotto: </span>
            <span>{{ $opportunity->productSubtype?->name ?? 'N/D' }}</span>
        </div>

        <div class="text-sm mb-2.5">
            <span class="text-gray-custom-4">Rinnovo: </span>
            <span>{{ $opportunity->is_renewal ? 'Sì' : 'No' }}</span>
        </div>

        <div class="text-sm mb-2.5">
            <span class="text-gray-custom-4">Produzione: </span>
            <span>{{ $opportunity->production_type?->getLabelText() ?? 'N/D' }}</span>
        </div>

        <div class="text-sm mb-2.5">
            <span class="text-gray-custom-4">Rate: </span>
            <span>{{ $opportunity->installment?->value ?? 'N/D' }}</span>
        </div>

        <div class="text-sm mb-2.5">
            <span class="text-gray-custom-4">Data di inizio: </span>
            <span>{{ $opportunity->first_installment_date?->format('d/m/y') ?? 'N/D' }}</span>
        </div>

        <div class="text-sm mb-2.5">
            <span class="text-gray-custom-4">Data di fine: </span>
            <span>{{ $opportunity->last_installment_date?->format('d/m/y') ?? 'N/D' }}</span>
        </div>

        <div class="text-sm mb-2.5">
            <span class="text-gray-custom-4">Importo: </span>
            <span>
                {{ $opportunity->amount_disbursed !== null
                    ? number_format($opportunity->amount_disbursed, 2, ',', '.') . '€'
                    : 'N/D' }}
            </span>
        </div>

        <div class="text-sm mb-2.5">
            <span class="text-gray-custom-4">Rata mensile: </span>
            <span>
                {{ $opportunity->rate_amount !== null
                    ? number_format($opportunity->rate_amount, 2, ',', '.') . '€'
                    : 'N/D' }}
            </span>
        </div>

        <div class="text-sm mb-2.5">
            <span class="text-gray-custom-4">Taeg fisso: </span>
            <span>
                {{ $opportunity->taeg !== null
                    ? number_format($opportunity->taeg, 2, ',', '.') . '%'
                    : 'N/D' }}
            </span>
        </div>

        <div class="text-sm mb-2.5">
            <span class="text-gray-custom-4">Tan fisso: </span>
            <span>
                {{ $opportunity->tan !== null
                    ? number_format($opportunity->tan, 3, ',', '.') . '%'
                    : 'N/D' }}
            </span>
        </div>

        <div class="text-sm mb-2.5">
            <span class="text-gray-custom-4">Teg fisso: </span>
            <span>
                {{ $opportunity->teg !== null
                    ? number_format($opportunity->teg, 2, ',', '.') . '%'
                    : 'N/D' }}
            </span>
        </div>

        <div class="text-sm mb-2.5">
            <span class="text-gray-custom-4">Totale dovuto: </span>
            <span>
                {{ $opportunity->total_amount !== null
                    ? number_format($opportunity->total_amount, 2, ',', '.') . '€'
                    : 'N/D' }}
            </span>
        </div>

        <div class="text-sm mb-2.5">
            <span class="text-gray-custom-4">Percentuale rinnovabilità: </span>
            <span>
                {{ $opportunity->renewability_percentage !== null
                    ? number_format($opportunity->renewability_percentage, 2, ',', '.') . '%'
                    : 'N/D' }}
            </span>
        </div>

        <div class="text-sm mb-2.5">
            <span class="text-gray-custom-4">Percentuale alert: </span>
            <span>
                {{ $opportunity->percentage_alert !== null
                    ? number_format($opportunity->percentage_alert, 2, ',', '.') . '%'
                    : 'N/D' }}
            </span>
        </div>

        <div class="text-sm mb-2.5">
            <span class="text-gray-custom-4">Tipologia cliente pratica: </span>
            <span>{{ $opportunity->customerType?->name ?? 'N/D' }}</span>
        </div>

        <div class="text-sm mb-2.5">
            <span class="text-gray-custom-4">Assicurazione: </span>
            <span>{{ $opportunity->insurance?->name ?? 'N/D' }}</span>
        </div>

        <div class="text-sm mb-2.5">
            <span class="text-gray-custom-4">Tabella provvigionale: </span>
            <span>
                {{ $opportunity->financialTable?->percentage !== null
                    ? number_format($opportunity->financialTable->percentage, 2, ',', '.') . '%'
                    : 'N/D' }}
            </span>
        </div>

        <div class="text-sm mb-2.5">
            <span class="text-gray-custom-4">Istituto finanziario: </span>
            <span>{{ $opportunity->financial_institution ?? 'N/D' }}</span>
        </div>

        <div class="text-sm mb-2.5">
            <span class="text-gray-custom-4">Finanziaria estinta: </span>
            <span>{{ $opportunity->previous_finance ?? 'N/D' }}</span>
        </div>

        @if ($opportunity->notes)
            <div class="text-sm mb-2.5">
                <span class="text-gray-custom-4">Note pratica: </span>
                <span>{{ $opportunity->notes }}</span>
            </div>
        @endif
    </x-card>
@endif

    {{-- Update Lead Status Modal --}}
    @include('partials.customer.update-lead-status-modal')
</div>
