{{-- Ente erogante --}}
<div class="text-sm mb-2.5">
    <span class="text-gray-custom-4">Ente erogante: </span>
    <span>{{ $opportunity?->disbursing_institution ?? 'N/D' }}</span>
</div>

{{-- Prodotto --}}
<div class="text-sm mb-2.5">
    <span class="text-gray-custom-4">Prodotto: </span>
    <span class="font-bold">
        {{ $opportunity?->productType?->name ?? 'N/D' }}
    </span>
</div>

{{-- Tipo prodotto --}}
<div class="text-sm mb-2.5">
    <span class="text-gray-custom-4">Tipo prodotto: </span>
    <span>{{ $opportunity?->productSubtype?->name ?? 'N/D' }}</span>
</div>

{{-- Rinnovo --}}
<div class="text-sm mb-2.5">
    <span class="text-gray-custom-4">Rinnovo: </span>
    <span>
        {{ $opportunity?->is_renewal === null
            ? 'N/D'
            : ($opportunity->is_renewal ? 'Sì' : 'No') }}
    </span>
</div>

{{-- Produzione --}}
<div class="text-sm mb-2.5">
    <span class="text-gray-custom-4">Produzione: </span>
    <span>
        {{ $opportunity?->production_type?->getLabelText() ?? 'N/D' }}
    </span>
</div>

{{-- Rate --}}
<div class="text-sm mb-2.5">
    <span class="text-gray-custom-4">Rate: </span>
    <span>{{ $opportunity?->installment?->value ?? 'N/D' }}</span>
</div>

{{-- Tipologia cliente --}}
<div class="text-sm mb-2.5">
    <span class="text-gray-custom-4">Tipologia cliente: </span>
    <span>{{ $opportunity?->customerType?->name ?? 'N/D' }}</span>
</div>

{{-- Data di assunzione --}}
<div class="text-sm mb-2.5">
    <span class="text-gray-custom-4">Data di assunzione: </span>
    <span>
        {{ $opportunity?->employment_start_date?->format('d/m/Y') ?? 'N/D' }}
    </span>
</div>

{{-- Data di inizio --}}
<div class="text-sm mb-2.5">
    <span class="text-gray-custom-4">Data di inizio: </span>
    <span>
        {{ $opportunity?->first_installment_date?->format('d/m/Y') ?? 'N/D' }}
    </span>
</div>

{{-- Data di fine --}}
<div class="text-sm mb-2.5">
    <span class="text-gray-custom-4">Data di fine: </span>
    <span>
        {{ $opportunity?->last_installment_date?->format('d/m/Y') ?? 'N/D' }}
    </span>
</div>

{{-- Importo --}}
<div class="text-sm mb-2.5">
    <span class="text-gray-custom-4">Importo: </span>
    <span>
        {{ $opportunity?->amount_disbursed !== null
            ? number_format($opportunity->amount_disbursed, 2, ',', '.') . '€'
            : 'N/D' }}
    </span>
</div>

{{-- Rata mensile --}}
<div class="text-sm mb-2.5">
    <span class="text-gray-custom-4">Rata mensile: </span>
    <span>
        {{ $opportunity?->rate_amount !== null
            ? number_format($opportunity->rate_amount, 2, ',', '.') . '€'
            : 'N/D' }}
    </span>
</div>

{{-- TAEG --}}
<div class="text-sm mb-2.5">
    <span class="text-gray-custom-4">Taeg fisso: </span>
    <span>
        {{ $opportunity?->taeg !== null
            ? number_format($opportunity->taeg, 2, ',', '.') . '%'
            : 'N/D' }}
    </span>
</div>

{{-- TAN --}}
<div class="text-sm mb-2.5">
    <span class="text-gray-custom-4">Tan fisso: </span>
    <span>
        {{ $opportunity?->tan !== null
            ? number_format($opportunity->tan, 3, ',', '.') . '%'
            : 'N/D' }}
    </span>
</div>

{{-- TEG --}}
<div class="text-sm mb-2.5">
    <span class="text-gray-custom-4">Teg fisso: </span>
    <span>
        {{ $opportunity?->teg !== null
            ? number_format($opportunity->teg, 2, ',', '.') . '%'
            : 'N/D' }}
    </span>
</div>

{{-- Totale dovuto --}}
<div class="text-sm mb-2.5">
    <span class="text-gray-custom-4">Totale dovuto: </span>
    <span>
        {{ $opportunity?->total_amount !== null
            ? number_format($opportunity->total_amount, 2, ',', '.') . '€'
            : 'N/D' }}
    </span>
</div>

{{-- Percentuale rinnovabilità --}}
<div class="text-sm mb-2.5">
    <span class="text-gray-custom-4">Percentuale rinnovabilità: </span>
    <span>
        {{ $opportunity?->renewability_percentage !== null
            ? number_format($opportunity->renewability_percentage, 2, ',', '.') . '%'
            : 'N/D' }}
    </span>
</div>

{{-- Percentuale alert --}}
<div class="text-sm mb-2.5">
    <span class="text-gray-custom-4">Percentuale alert: </span>
    <span>
        {{ $opportunity?->percentage_alert !== null
            ? number_format($opportunity->percentage_alert, 2, ',', '.') . '%'
            : 'N/D' }}
    </span>
</div>

{{-- Assicurazione --}}
<div class="text-sm mb-2.5">
    <span class="text-gray-custom-4">Assicurazione: </span>
    <span>{{ $opportunity?->insurance?->name ?? 'N/D' }}</span>
</div>

{{-- Tabella provvigionale --}}
<div class="text-sm mb-2.5">
    <span class="text-gray-custom-4">Tabella provvigionale: </span>
    <span>
        {{ $opportunity?->financialTable?->percentage !== null
            ? number_format($opportunity->financialTable->percentage, 2, ',', '.') . '%'
            : 'N/D' }}
    </span>
</div>

{{-- Istituto finanziario --}}
<div class="text-sm mb-2.5">
    <span class="text-gray-custom-4">Istituto finanziario: </span>
    <span>{{ $opportunity?->financial_institution ?? 'N/D' }}</span>
</div>

{{-- Finanziaria estinta --}}
<div class="text-sm mb-2.5">
    <span class="text-gray-custom-4">Finanziaria estinta: </span>
    <span>{{ $opportunity?->previous_finance ?? 'N/D' }}</span>
</div>

{{-- Note --}}
<div class="text-sm mb-2.5">
    <span class="text-gray-custom-4">Note pratica: </span>
    <span>{{ $opportunity?->notes ?? 'N/D' }}</span>
</div>
