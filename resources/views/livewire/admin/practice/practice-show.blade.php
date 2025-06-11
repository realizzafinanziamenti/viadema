<div class="w-full">
    <x-button-back class="mb-2.5" route="practice.index" />
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
                    <span class="text-gray-custom-4">Prodotto: </span>
                    <span class="font-bold">{{ $practice->productType?->name }}</span>
                </div>

                <div class="text-sm mb-2.5">
                    <span class="text-gray-custom-4">Data di apertura: </span>
                    <span>{{ $practice->formatted_first_installment_date }}</span>
                </div>

                @if ($practice->early_settlement_date)
                    <div class="text-sm mb-2.5">
                        <span class="text-gray-custom-4">Data di fine: </span>
                        <span>{{ $practice->formatted_early_settlement_date }}</span>
                    </div>
                @endif

                <div class="text-sm mb-2.5 flex items-center gap-2">
                    <span class="text-gray-custom-4">Stato pratica: </span>
                    <x-practice-status-badge :practice="$practice" wire:click='openUpdatePracticeStatusModal' />
                </div>

                <div class="text-sm mb-2.5">
                    <span class="text-gray-custom-4">Operatore: </span>
                    <span>{{ $practice->user?->full_name }}</span>
                </div>

                @if ($practice->note)
                    <div class="text-sm mb-2.5">
                        <span class="text-gray-custom-4">Note: </span>
                        <span>{{ $practice->note }}</span>
                    </div>
                @endif
            </x-card>

            {{-- Product Information --}}
            <x-card>
                <x-card-header class="mb-6" label="Totale dovuto" />

                <div class="text-sm mb-2.5">
                    <span class="text-gray-custom-4">Finanziato: </span>
                    <span>{{ $practice->formatted_amount_disbursed }}</span>
                </div>

                <div class="text-sm mb-2.5">
                    <span class="text-gray-custom-4">Rate: </span>
                    <span>{{ $practice->installment?->value }}</span>
                </div>

                <div class="text-sm mb-2.5">
                    <span class="text-gray-custom-4">Rata mensile: </span>
                    <span>{{ $practice->formatted_rate_amount }}</span>
                </div>

                <div class="text-sm mb-2.5">
                    <span class="text-gray-custom-4">Taeg fisso: </span>
                    <span>{{ $practice->formatted_taeg }}</span>
                </div>

                <div class="text-sm mb-2.5">
                    <span class="text-gray-custom-4">Tan fisso: </span>
                    <span>{{ $practice->formatted_tan }}</span>
                </div>

                <div class="text-sm mb-2.5">
                    <span class="text-gray-custom-4">Totale dovuto: </span>
                    <span>{{ $practice->formatted_total_amount }}</span>
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
            </x-card>

        </div>
    </div>

    {{-- Update Practice Status Modal --}}
    @include('partials.practice.update-practice-status-modal')
</div>
