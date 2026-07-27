<div class="w-full">
    <div class="flex items-center justify-between mb-2.5">
        <x-button-back route="practice.index" />

        @can('update', $practice)
            <a href="{{ route('practice.edit', ['id' => $practice->id]) }}" wire:navigate>
                <flux:button variant="primary" type="submit" size="sm"
                    class="px-10 bg-azure-custom border-azure-custom hover:bg-azure-custom-hover hover:border-azure-custom-hover">
                    Modifica
                </flux:button>
            </a>
        @endcan
    </div>

    <x-page-title label="Dettaglio Pratica" />

    <div class="grid grid-cols-2 gap-4">
        <div class="col-span-1 flex flex-col gap-4">

            {{-- Generic Information --}}
            <x-card>
                <x-card-header class="mb-6" label="Informazioni generali" />

                <div class="text-sm mb-2.5">
                    <span class="text-gray-custom-4">Id pratica: </span>
                    <span>{{ $practice->practice_code }}</span>
                </div>

                <div class="text-sm mb-2.5">
                    <span class="text-gray-custom-4">Canale di acquisizione: </span>
                    <span>
                        {{ $practice->opportunity?->acquisition_channel?->getLabelText() ?? 'N/D' }}
                    </span>
                </div>

                <div class="text-sm mb-2.5">
                    <span class="text-gray-custom-4">Prodotto: </span>
                    <span class="font-bold">{{ $practice->opportunity?->productType?->name ?? 'N/D' }}</span>
                </div>

                <div class="text-sm mb-2.5">
                    <span class="text-gray-custom-4">Ente erogante: </span>
                    <span>{{ $practice->opportunity?->disbursing_institution ?? 'N/D' }}</span>
                </div>

                <div class="text-sm mb-2.5">
                    <span class="text-gray-custom-4">Istituto finanziario: </span>
                    <span>{{ $practice->opportunity?->financial_institution ?? 'N/D' }}</span>
                </div>

                <div class="text-sm mb-2.5">
                    <span class="text-gray-custom-4">Assicurazione: </span>
                    <span>{{ $practice->opportunity?->insurance?->name ?? 'N/D' }}</span>
                </div>

                <div class="text-sm mb-2.5">
                    <span class="text-gray-custom-4">Data di inizio: </span>
                    <span>{{ $practice->opportunity?->first_installment_date?->format('d/m/y') ?? 'N/D' }}</span>
                </div>

                <div class="text-sm mb-2.5">
                    <span class="text-gray-custom-4">Data di fine: </span>
                    <span>{{ $practice->opportunity?->last_installment_date?->format('d/m/y') ?? 'N/D' }}</span>
                </div>
                @if ($practice->renewability_date)
                <div class="text-sm mb-2.5">
                    <span class="text-gray-custom-4">
                        Data rinnovabilità:
                    </span>

                    <span>
                        {{ $practice->renewability_date->format('d/m/Y') }}
                    </span>
                </div>
            @endif


                <div class="text-sm mb-2.5 flex items-center gap-2">
                    <span class="text-gray-custom-4">Stato pratica: </span>

                    @if (Gate::allows('updateStatus', $practice))
                        <x-clickable-badge :property="$practice->practice_status?->getLabelText()" :css="$practice->practice_status?->getLabelColor()"
                            wire:click="openUpdatePracticeStatusModal" />
                    @else
                        <x-badge :property="$practice->practice_status?->getLabelText()" :css="$practice->practice_status?->getLabelColor()" />
                    @endif
                </div>

                @if ($practice->practice_status === App\Enums\PracticeStatus::DISBURSED)
                    <div class="text-sm mb-2.5">
                        <span class="text-gray-custom-4">Data di liquidazione: </span>
                        <span>{{ $practice->formatted_disbursement_date }}</span>
                    </div>
                @endif

                <div class="text-sm mb-2.5">
                    <span class="text-gray-custom-4">Operatore: </span>
                    <span>{{ $practice->user?->full_name }}</span>
                </div>

                <div class="text-sm mb-2.5">
                    <span class="text-gray-custom-4">Produzione: </span>
                    <span>{{ $practice->opportunity?->production_type?->getLabelText() ?? 'N/D' }}</span>
                </div>

                <div class="text-sm mb-2.5">
                    <span class="text-gray-custom-4">Rinnovo: </span>
                    <span>{{ $practice->opportunity?->is_renewal ? 'Sì' : 'No' }}</span>
                </div>

                @if ($practice->opportunity?->notes)
                    <div class="text-sm mb-2.5">
                        <span class="text-gray-custom-4">Note: </span>
                        <span>{{ $practice->notes }}</span>
                    </div>
                @endif
            </x-card>

            {{-- Product Information --}}
{{-- Product Information --}}
<x-card>
    <x-card-header class="mb-6" label="Totale dovuto" />

    <div class="text-sm mb-2.5">
        <span class="text-gray-custom-4">Finanziato: </span>
        <span>
            {{ $practice->opportunity?->amount_disbursed !== null
                ? number_format($practice->opportunity->amount_disbursed, 2, ',', '.') . '€'
                : 'N/D' }}
        </span>
    </div>

    <div class="text-sm mb-2.5">
        <span class="text-gray-custom-4">Rate: </span>
        <span>{{ $practice->opportunity?->installment?->value ?? 'N/D' }}</span>
    </div>

    <div class="text-sm mb-2.5">
        <span class="text-gray-custom-4">Rata mensile: </span>
        <span>
            {{ $practice->opportunity?->rate_amount !== null
                ? number_format($practice->opportunity->rate_amount, 2, ',', '.') . '€'
                : 'N/D' }}
        </span>
    </div>

    <div class="text-sm mb-2.5">
        <span class="text-gray-custom-4">Taeg fisso: </span>
        <span>
            {{ $practice->opportunity?->taeg !== null
                ? number_format($practice->opportunity->taeg, 2, ',', '.') . '%'
                : 'N/D' }}
        </span>
    </div>

    <div class="text-sm mb-2.5">
        <span class="text-gray-custom-4">Tan fisso: </span>
        <span>
            {{ $practice->opportunity?->tan !== null
                ? number_format($practice->opportunity->tan, 2, ',', '.') . '%'
                : 'N/D' }}
        </span>
    </div>

    <div class="text-sm mb-2.5">
        <span class="text-gray-custom-4">Totale dovuto: </span>
        <span>
            {{ $practice->opportunity?->total_amount !== null
                ? number_format($practice->opportunity->total_amount, 2, ',', '.') . '€'
                : 'N/D' }}
        </span>
    </div>
</x-card>

        </div>

        <div class="col-span-1 flex flex-col gap-4">

            {{-- Customer Information --}}
            <x-card>
                <x-card-header class="mb-6" label="Dati Cliente" />

                <div class="text-sm mb-2.5">
                    <span class="text-gray-custom-4">Nome: </span>
                    <span>{{ $practice->customer?->full_name }}</span>
                </div>

                <div class="text-sm mb-2.5">
                    <span class="text-gray-custom-4">Email: </span>
                    <span>{{ $practice->customer?->email }}</span>
                </div>

                <div class="text-sm mb-2.5">
                    <span class="text-gray-custom-4">Cellulare: </span>
                    <span>{{ $practice->customer?->phone }}</span>
                </div>

                <div class="text-sm mb-2.5">
                    <span class="text-gray-custom-4">Indirizzo: </span>
                    <span>{{ $practice->customer?->address }}</span>
                </div>

                <div class="text-sm mb-2.5">
                    <span class="text-gray-custom-4">Cap: </span>
                    <span>{{ $practice->customer?->postal_code }}</span>
                </div>

                <div class="text-sm mb-2.5">
                    <span class="text-gray-custom-4">Città: </span>
                    <span>{{ $practice->customer?->city }}</span>
                </div>

                <div class="text-sm mb-2.5">
                    <span class="text-gray-custom-4">Provincia: </span>
                    <span>{{ $practice->customer?->state }}</span>
                </div>

                <div class="text-sm mb-2.5">
                    <span class="text-gray-custom-4">Codice fiscale: </span>
                    <span>{{ $practice->customer?->tax_id }}</span>
                </div>
            </x-card>

            {{-- Documentation --}}
            <x-card>
                <x-card-header class="mb-6" label="Documenti" />

                <div class="flex flex-col gap-2.5">
                    @foreach ($practice->attachments as $attachment)
                        <x-display-file :attachment="$attachment" />
                    @endforeach
                </div>
            </x-card>

        </div>
    </div>

    {{-- Update Practice Status Modal --}}
    @include('partials.practice.update-practice-status-modal')

    {{-- Delete Practice Modal --}}
    <x-delete-modal name="delete-attachment" header="Conferma Eliminazione Allegato" function="deleteAttachment"
        message="Sei sicuro di voler eliminare l'allegato' <strong>{{ $selectedAttachment?->file_name }}</strong>?" />
</div>
