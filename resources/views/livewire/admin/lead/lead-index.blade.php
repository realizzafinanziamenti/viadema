<div wire:init="initializeImportReportState">
    {{-- Import status polling --}}
    @if ($pollImportReport)
        <div
            wire:poll.2s="checkImportStatus"
            class="hidden"
            aria-hidden="true"
        ></div>
    @endif

    @php
        $filteredSummary = $this->filteredSummary;
    @endphp

    <x-card>
        {{-- Search, summary and actions --}}
        <div
            class="mb-5 grid grid-cols-1 gap-4 2xl:grid-cols-[minmax(320px,1fr)_auto] 2xl:items-center"
        >
            {{-- Search --}}
            <div class="min-w-0">
                <div class="w-full max-w-lg">
                    <flux:input
                        class="w-full"
                        wire:model.live.debounce.500ms="search"
                        icon:trailing="magnifying-glass"
                        placeholder="Cerca per nome, cognome..."
                    />
                </div>
            </div>

            {{-- Summary and actions --}}
            <div
                class="flex flex-wrap items-center gap-3 2xl:flex-nowrap 2xl:justify-end"
            >
                {{-- Quantity --}}
                <div
                    class="flex h-10 min-w-[112px] shrink-0 items-center justify-between gap-3 rounded-md border border-zinc-200 bg-transparent px-3"
                    title="Numero complessivo di lead trovati"
                >
                    <span class="whitespace-nowrap text-xs font-medium text-gray-custom-4">
                        Quantità
                    </span>

                    <span class="whitespace-nowrap text-sm font-bold text-black-custom">
                        {{ number_format(
                            $filteredSummary['count'],
                            0,
                            ',',
                            '.'
                        ) }}
                    </span>
                </div>

                {{-- Total financed amount --}}
                <div
                    class="flex h-10 min-w-[176px] shrink-0 items-center justify-between gap-3 rounded-md border border-zinc-200 bg-transparent px-3"
                    title="Somma degli importi finanziati dei lead trovati"
                >
                    <span class="whitespace-nowrap text-xs font-medium text-gray-custom-4">
                        Totale
                    </span>

                    <span class="whitespace-nowrap text-sm font-bold text-black-custom">
                        € {{ number_format(
                            $filteredSummary['total'],
                            2,
                            ',',
                            '.'
                        ) }}
                    </span>
                </div>

                {{-- Filters --}}
                <flux:button
                    type="button"
                    size="base"
                    variant="ghost"
                    icon="funnel"
                    wire:click="openFilterModal"
                    class="h-10 min-w-[108px] shrink-0 border border-zinc-200! bg-transparent! text-black-custom! hover:border-zinc-300! hover:bg-zinc-50!"
                >
                    Filtra
                </flux:button>

                {{-- Export --}}
                @can('export leads')
                    <x-buttons.export-button
                        class="h-10 shrink-0"
                        :disabled="$this->selectedCount === 0"
                        wire:click="exportSelectedLeads"
                    />
                @endcan

                {{-- Import --}}
                @can('import leads')
                    <flux:dropdown
                        position="bottom"
                        align="end"
                    >
                        <x-buttons.import-button
                            class="h-10 shrink-0"
                            icon:trailing="chevron-down"
                        />

                        <flux:menu class="min-w-48">
                            <flux:menu.item
                                icon="arrow-up-tray"
                                wire:click="openImportModal"
                                :disabled="$this->isLeadImportRunning"
                            >
                                @if ($this->isLeadImportRunning)
                                    Import in corso...
                                @else
                                    Importa
                                @endif
                            </flux:menu.item>

                            <flux:menu.separator />

                            <flux:menu.item
                                icon="document-text"
                                wire:click="openLatestImportReport"
                                :disabled="$this->latestLeadImportReport === null || $this->isLeadImportRunning"
                            >
                                Ultimo report
                            </flux:menu.item>
                        </flux:menu>
                    </flux:dropdown>
                @endcan

                {{-- Create --}}
                @can('create leads')
                    <a
                        href="{{ route('lead.create') }}"
                        wire:navigate
                        class="shrink-0"
                    >
                        <x-buttons.create-button class="h-10" />
                    </a>
                @endcan
            </div>
        </div>

        {{-- Bulk selections --}}
        @if ($this->hasSelection)
            <div class="mb-1.5 ml-3.5 text-sm">
                Elementi selezionati: {{ $this->selectedCount }}

                <button
                    type="button"
                    class="ml-2 cursor-pointer underline hover:text-azure-custom"
                    wire:click="selectAllResults"
                >
                    Seleziona tutti
                </button>

                <button
                    type="button"
                    class="ml-2 cursor-pointer underline hover:text-azure-custom"
                    wire:click="clearSelection"
                >
                    Deseleziona tutti
                </button>
            </div>
        @endif

        @if (count($this->rows) > 0)
            <x-table
                class="mb-5"
                minWidth="min-w-[1740px]"
            >
                {{-- Table header --}}
                <x-slot
                    name="header"
                    class="border-b"
                >
                    {{-- Checkbox --}}
                    <x-table-header class="w-[40px]">
                        <div
                            class="inline-flex h-full w-full items-center justify-start ps-1"
                        >
                            <x-checkbox
                                wire:model="pageSelected"
                                wire:click="toggleSelectPage"
                            />
                        </div>
                    </x-table-header>

                    <x-table-header
                        label="N. Trattativa"
                        class="w-[100px]"
                    />

                    <x-table-header
                        label="Tipologia"
                        class="w-[160px]"
                    />

                    <x-table-header
                        label="Nominativo"
                        class="w-1/2"
                    />

                    <x-table-header
                        label="Telefono"
                        class="w-[160px]"
                    />

                    <x-table-header
                        label="Stato"
                        class="w-[160px]"
                    />

                    <x-table-header
                        label="Provenienza"
                        class="w-[160px]"
                    />

                    <x-table-header
                        label="Assegnato a"
                        class="w-1/2"
                    />

                    <x-table-header
                        label="Data creazione"
                        class="w-[120px]"
                    />

                    <x-table-header
                        label="Data ricontatto"
                        class="w-[140px]"
                    />

                    <x-table-header
                        label="Note"
                        class="w-[60px]"
                    />

                    <x-table-header class="w-[150px]">
                        {{-- Actions --}}
                    </x-table-header>
                </x-slot>

                {{-- Table body --}}
                @foreach ($this->rows as $lead)
                    <tr
                        wire:key="lead-row-{{ $lead->id }}"
                        class="border-y border-collapse"
                    >
                        {{-- Checkbox --}}
                        <x-table-data class="w-[40px]">
                            <div
                                class="inline-flex h-full w-full items-center justify-start ps-1"
                            >
                                <x-checkbox
                                    wire:click="toggleSelection({{ $lead->id }})"
                                    :checked="$this->isSelected($lead->id)"
                                    wire:key="row-checkbox-{{ $lead->id }}-{{ (int) $this->isSelected($lead->id) }}"
                                />
                            </div>
                        </x-table-data>

                        <x-table-data
                            truncate
                            label="{{ $lead->formatted_id }}"
                        />

                        <x-table-data
                            truncate
                            label="{{ $lead->customerType?->name ?? 'N/D' }}"
                        />

                        <x-table-data
                            truncate
                            label="{{ $lead->full_name }}"
                        />

                        <x-table-data
                            truncate
                            label="{{ $lead->phone ?? 'N/D' }}"
                        />

                        {{-- Status --}}
                        <x-table-data>
                            @if (Gate::allows('updateLeadStatus', $lead))
                                <x-clickable-badge
                                    :property="$lead->lead_status?->getLabelText()"
                                    :css="$lead->lead_status?->getLabelColor()"
                                    wire:click="selectLeadForStatus({{ $lead->id }})"
                                    title="Cambia stato lead"
                                />
                            @else
                                <x-badge
                                    :property="$lead->lead_status?->getLabelText()"
                                    :css="$lead->lead_status?->getLabelColor()"
                                />
                            @endif
                        </x-table-data>

                        <x-table-data
                            truncate
                            label="{{ $lead->latestPracticeOpportunity?->acquisition_channel?->getLabelText() ?? 'N/D' }}"
                        />

                        <x-table-data
                            truncate
                            class="flex items-center"
                        >
                            <x-user-table-data :user="$lead->user" />
                        </x-table-data>

                        <x-table-data
                            truncate
                            label="{{ $lead->formatted_created_at }}"
                        />

                        <x-table-data
                            truncate
                            label="{{ $lead->recontact_date?->format('d/m/Y') ?? 'N/D' }}"
                        />

                        {{-- Notes --}}
                        <x-table-data>
                            @if ($lead->notes)
                                <div
                                    class="relative flex w-full items-center justify-center"
                                >
                                    <button
                                        type="button"
                                        class="relative cursor-pointer"
                                        title="Visualizza note"
                                        wire:click="selectLeadForNotes({{ $lead->id }})"
                                    >
                                        <x-icons.icon-akar-chat-bubble
                                            class="text-gray-custom-3"
                                        />

                                        <div
                                            class="absolute right-0 bottom-[2px] flex size-3 items-center justify-center rounded-full bg-orange-custom text-[10px]"
                                        ></div>
                                    </button>
                                </div>
                            @else
                                <div
                                    class="flex w-full items-center justify-center"
                                    title="Nessuna nota disponibile"
                                >
                                    <x-icons.icon-akar-chat-bubble
                                        class="text-gray-custom-3"
                                    />
                                </div>
                            @endif
                        </x-table-data>

                        {{-- Actions --}}
                        <x-table-data>
                            <div
                                class="flex w-full items-center justify-end gap-3"
                            >
                                @can('view', $lead)
                                    <a
                                        href="{{ route('lead.show', ['id' => $lead->id]) }}"
                                        wire:navigate
                                    >
                                        <x-table-action-button-view />
                                    </a>
                                @endcan

                                @can('update', $lead)
                                    <a
                                        href="{{ route('lead.edit', ['id' => $lead->id]) }}"
                                        wire:navigate
                                    >
                                        <x-table-action-button-edit />
                                    </a>
                                @endcan

                                @can('delete', $lead)
                                    <x-table-action-button-delete
                                        wire:click="selectLeadForDelete({{ $lead->id }})"
                                    />
                                @endcan
                            </div>
                        </x-table-data>
                    </tr>
                @endforeach
            </x-table>

            {{-- Pagination --}}
            {{ $this->rows->links() }}
        @else
            <div class="py-4 text-base">
                Nessun lead trovato.
            </div>
        @endif
    </x-card>

    {{-- Update Lead Status Modal --}}
    @include('partials.customer.update-lead-status-modal')

    {{-- Delete Lead Modal --}}
    <x-delete-modal
        name="delete-lead"
        header="Conferma Eliminazione Lead"
        function="deleteLead"
        message="Sei sicuro di voler eliminare il lead <strong>{{ $selectedLead?->full_name }}</strong>?"
    />

    {{-- Notes Modal --}}
    @include('partials.lead.lead-notes-modal')

    {{-- Filters Modal --}}
    @include('partials.lead.lead-filters-modal')

    {{-- Import Modal --}}
    <x-modals.import-modal
        name="import-leads-modal"
        header="Importa leads da Excel"
        submitFunction="importLeads"
        :importFile="$importFile"
        :temporaryImportFile="$temporaryImportFile"
        :users="$users"
        :userId="$userId"
        :userSearch="$userSearch"
        :canAssignUser="auth()->user()->can('assign lead to user')"
    />

    {{-- Import Report Modal --}}
    @include('partials.import.import-report-modal', [
        'modalName' => 'lead-import-report-modal',
        'title' => 'Report importazione lead',
        'entityIdLabel' => 'ID lead',
        'successMessage' => 'Lead importato correttamente.',
    ])
</div>
