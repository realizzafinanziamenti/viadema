<div class="w-full">

    <div class="flex items-center justify-between mb-2.5">
        <x-button-back route="practice.index" />

        @can('update', $practice)
            <a
                href="{{ route('practice.edit', ['id' => $practice->id]) }}"
                wire:navigate
            >
                <flux:button
                    variant="primary"
                    type="submit"
                    size="sm"
                    class="px-10 bg-azure-custom border-azure-custom hover:bg-azure-custom-hover hover:border-azure-custom-hover"
                >
                    Modifica
                </flux:button>
            </a>
        @endcan
    </div>

    <x-page-title label="Dettaglio Pratica" />

    <div class="grid grid-cols-2 gap-4">

        {{-- LEFT COLUMN --}}
        <div class="col-span-1 flex flex-col gap-4">

            {{-- Generic Information --}}
            <x-card>
                <x-card-header
                    class="mb-6"
                    label="Informazioni generali"
                />

                <div class="text-sm mb-2.5">
                    <span class="text-gray-custom-4">
                        Id pratica:
                    </span>

                    <span>
                        {{ $practice->practice_code }}
                    </span>
                </div>

                <div class="text-sm mb-2.5">
                    <span class="text-gray-custom-4">
                        Canale di acquisizione:
                    </span>

                    <span>
                        {{ $practice->opportunity?->acquisition_channel?->getLabelText() ?? 'N/D' }}
                    </span>
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
                    <span class="text-gray-custom-4">
                        Stato pratica:
                    </span>

                    @if (Gate::allows('updateStatus', $practice))
                        <x-clickable-badge
                            :property="$practice->practice_status?->getLabelText()"
                            :css="$practice->practice_status?->getLabelColor()"
                            wire:click="openUpdatePracticeStatusModal"
                        />
                    @else
                        <x-badge
                            :property="$practice->practice_status?->getLabelText()"
                            :css="$practice->practice_status?->getLabelColor()"
                        />
                    @endif
                </div>

                @if ($practice->practice_status === \App\Enums\PracticeStatus::DISBURSED)
                    <div class="text-sm mb-2.5">
                        <span class="text-gray-custom-4">
                            Data di liquidazione:
                        </span>

                        <span>
                            {{ $practice->formatted_disbursement_date }}
                        </span>
                    </div>
                @endif

                <div class="text-sm mb-2.5">
                    <span class="text-gray-custom-4">
                        Operatore:
                    </span>

                    <span>
                        {{ $practice->user?->full_name ?? 'N/D' }}
                    </span>
                </div>
            </x-card>

            {{-- Practice Opportunity Information --}}
            @if ($practice->opportunity)
                <x-card>
                    <x-card-header
                        class="mb-6"
                        label="Dati pratica"
                    />

                    @include(
                        'partials.practice.opportunity-show-fields',
                        ['opportunity' => $practice->opportunity]
                    )
                </x-card>
            @endif

        </div>

        {{-- RIGHT COLUMN --}}
        <div class="col-span-1 flex flex-col gap-4">

            {{-- Customer Information --}}
            <x-card>
                <x-card-header
                    class="mb-6"
                    label="Dati Cliente"
                />

                <div class="text-sm mb-2.5">
                    <span class="text-gray-custom-4">
                        Nome:
                    </span>

                    <span>
                        {{ $practice->customer?->full_name ?? 'N/D' }}
                    </span>
                </div>

                <div class="text-sm mb-2.5">
                    <span class="text-gray-custom-4">
                        Email:
                    </span>

                    <span>
                        {{ $practice->customer?->email ?? 'N/D' }}
                    </span>
                </div>

                <div class="text-sm mb-2.5">
                    <span class="text-gray-custom-4">
                        Cellulare:
                    </span>

                    <span>
                        {{ $practice->customer?->phone ?? 'N/D' }}
                    </span>
                </div>

                <div class="text-sm mb-2.5">
                    <span class="text-gray-custom-4">
                        Indirizzo:
                    </span>

                    <span>
                        {{ $practice->customer?->address ?? 'N/D' }}
                    </span>
                </div>

                <div class="text-sm mb-2.5">
                    <span class="text-gray-custom-4">
                        Cap:
                    </span>

                    <span>
                        {{ $practice->customer?->postal_code ?? 'N/D' }}
                    </span>
                </div>

                <div class="text-sm mb-2.5">
                    <span class="text-gray-custom-4">
                        Città:
                    </span>

                    <span>
                        {{ $practice->customer?->city ?? 'N/D' }}
                    </span>
                </div>

                <div class="text-sm mb-2.5">
                    <span class="text-gray-custom-4">
                        Provincia:
                    </span>

                    <span>
                        {{ $practice->customer?->state ?? 'N/D' }}
                    </span>
                </div>

                <div class="text-sm mb-2.5">
                    <span class="text-gray-custom-4">
                        Codice fiscale:
                    </span>

                    <span>
                        {{ $practice->customer?->tax_id ?? 'N/D' }}
                    </span>
                </div>
            </x-card>

            {{-- Documentation --}}
            <x-card>
                <x-card-header
                    class="mb-6"
                    label="Documenti"
                />

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

    {{-- Delete Practice Attachment Modal --}}
    <x-delete-modal
        name="delete-attachment"
        header="Conferma Eliminazione Allegato"
        function="deleteAttachment"
        message="Sei sicuro di voler eliminare l'allegato <strong>{{ $selectedAttachment?->file_name }}</strong>?"
    />

</div>
