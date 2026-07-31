<div>
    {{-- Import report polling --}}
    @if (!$expired)
        <div wire:init="initializeImportReportState">
            @if ($pollImportReport)
                <div
                    wire:poll.2s="checkImportStatus"
                    class="hidden"
                    aria-hidden="true"
                ></div>
            @endif
        </div>
    @endif

    @php
        $filteredSummary = $this->filteredSummary;
    @endphp

    {{-- Page title --}}
    @if ($productType)
        <x-page-title
            label="{{ $productType->name }}"
            class="mt-1"
        />
    @elseif ($expired)
        <x-page-title
            label="Archivio pratiche"
            class="mt-1"
        />
    @else
        <x-page-title
            label="Gestione pratiche"
            class="mt-1"
        />
    @endif

    <x-card>
        {{-- Search, sorting, summary and actions --}}
        <div
            class="mb-5 grid grid-cols-1 gap-4 2xl:grid-cols-[minmax(520px,1fr)_auto] 2xl:items-center"
        >
            {{-- Search and sorting --}}
            <div
                class="flex min-w-0 flex-col gap-3 md:flex-row md:items-center"
            >
                <div class="w-full min-w-0 md:max-w-lg md:flex-1">
                    <flux:input
                        class="w-full"
                        wire:model.live.debounce.500ms="search"
                        icon:trailing="magnifying-glass"
                        placeholder="Cerca per nome cliente, codice fiscale o id pratica..."
                    />
                </div>

                <div class="shrink-0">
                    <x-dropdown-select
                        width="w-52"
                        :selectable-items="$orderBySelect"
                        :selected="$selectedOrderBy->value"
                        placeholder="Ordina per"
                        setFunction="setOrderBy"
                        :has-error="$errors->has('selectedOrderBy')"
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
                    title="Numero complessivo di pratiche trovate"
                >
                    <span
                        class="whitespace-nowrap text-xs font-medium text-gray-custom-4"
                    >
                        Quantità
                    </span>

                    <span
                        class="whitespace-nowrap text-sm font-bold text-black-custom"
                    >
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
                    title="Somma degli importi finanziati delle pratiche trovate"
                >
                    <span
                        class="whitespace-nowrap text-xs font-medium text-gray-custom-4"
                    >
                        Totale
                    </span>

                    <span
                        class="whitespace-nowrap text-sm font-bold text-black-custom"
                    >
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
                @can('export practices')
                    <x-buttons.export-button
                        class="h-10 shrink-0"
                        :disabled="$this->selectedCount === 0"
                        wire:click="exportSelectedPractices"
                    />
                @endcan

                @if (!$expired)
                    {{-- Import --}}
                    @can('import practices')
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
                                    :disabled="$this->isPracticeImportRunning"
                                >
                                    @if ($this->isPracticeImportRunning)
                                        Import in corso...
                                    @else
                                        Importa
                                    @endif
                                </flux:menu.item>

                                <flux:menu.separator />

                                <flux:menu.item
                                    icon="document-text"
                                    wire:click="openLatestImportReport"
                                    :disabled="$this->latestPracticeImportReport === null || $this->isPracticeImportRunning"
                                >
                                    Ultimo report
                                </flux:menu.item>
                            </flux:menu>
                        </flux:dropdown>
                    @endcan

                    {{-- Create --}}
                    @can('create practices')
                        <a
                            href="{{ route('practice.create') }}"
                            wire:navigate
                            class="shrink-0"
                        >
                            <x-buttons.create-button class="h-10" />
                        </a>
                    @endcan
                @endif
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
                class="z-10 mb-5"
                minWidth="min-w-[1300px]"
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
                        label="Id pratica"
                        class="w-[110px]"
                    />

                    <x-table-header
                        label="Cliente"
                        class="w-3/7"
                    />

                    @if (!$productType)
                        <x-table-header
                            label="Prodotto"
                            class="w-[160px]"
                        />
                    @endif

                    <x-table-header
                        label="Data inserimento"
                        class="w-[130px]"
                    />

                    <x-table-header
                        label="Codice fiscale"
                        class="w-[170px]"
                    />

                    <x-table-header
                        label="Stato pratica"
                        class="w-[140px]"
                    />

                    <x-table-header
                        label="Collaboratore"
                        class="w-4/7"
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
                @foreach ($this->rows as $practice)
                    <tr
                        wire:key="practice-row-{{ $practice->id }}"
                        class="z-10 border-y border-collapse"
                    >
                        {{-- Checkbox --}}
                        <x-table-data class="w-[40px]">
                            <div
                                class="inline-flex h-full w-full items-center justify-start ps-1"
                            >
                                <x-checkbox
                                    wire:click="toggleSelection({{ $practice->id }})"
                                    :checked="$this->isSelected($practice->id)"
                                    wire:key="row-checkbox-{{ $practice->id }}-{{ (int) $this->isSelected($practice->id) }}"
                                />
                            </div>
                        </x-table-data>

                        {{-- Practice code --}}
                        <x-table-data
                            truncate
                            class="inline-flex items-center gap-2"
                        >
                            @if (
                                $practice->renewability_date
                                && $practice->renewability_date <= now()
                            )
                                <div
                                    title="La pratica è rinnovabile dal {{ $practice->formatted_renewability_date }}"
                                >
                                    <x-icons.icon-akar-circle-alert
                                        class="text-red-custom"
                                    />
                                </div>
                            @elseif (
                                $practice->renewability_date
                                && $practice->alert_date
                                && $practice->renewability_date > now()
                                && $practice->alert_date <= now()
                            )
                                <div
                                    title="La pratica sarà rinnovabile dal {{ $practice->formatted_renewability_date }}"
                                >
                                    <x-icons.icon-akar-circle-alert
                                        class="text-orange-custom"
                                    />
                                </div>
                            @endif

                            <div>
                                {{ $practice->practice_code }}
                            </div>
                        </x-table-data>

                        <x-table-data
                            truncate
                            label="{{ $practice->customer?->full_name ?? 'N/D' }}"
                        />

                        @if (!$productType)
                            <x-table-data
                                truncate
                                class="w-[160px] font-bold!"
                                label="{{ $practice->opportunity?->productType?->name ?? 'N/D' }}"
                            />
                        @endif

                        <x-table-data
                            truncate
                            label="{{ $practice->formatted_inserted_at ?? $practice->formatted_created_at }}"
                        />

                        <x-table-data
                            truncate
                            label="{{ $practice->customer?->tax_id ?? 'N/D' }}"
                        />

                        {{-- Practice status --}}
                        <x-table-data>
                            @if (Gate::allows('updateStatus', $practice))
                                <x-clickable-badge
                                    :property="$practice->practice_status?->getLabelText()"
                                    :css="$practice->practice_status?->getLabelColor()"
                                    wire:click="selectPracticeForStatus({{ $practice->id }})"
                                    title="Cambia stato pratica"
                                />
                            @else
                                <x-badge
                                    :property="$practice->practice_status?->getLabelText()"
                                    :css="$practice->practice_status?->getLabelColor()"
                                />
                            @endif
                        </x-table-data>

                        {{-- Assigned user --}}
                        <x-table-data
                            truncate
                            class="inline-flex items-center"
                        >
                            @if ($practice->user)
                                <x-user-table-data :user="$practice->user" />
                            @else
                                N/D
                            @endif
                        </x-table-data>

                        {{-- Notes --}}
                        <x-table-data>
                            @if ($practice->opportunity?->notes)
                                <div
                                    class="relative flex w-full items-center justify-center"
                                >
                                    <button
                                        type="button"
                                        class="relative cursor-pointer"
                                        title="Visualizza note"
                                        wire:click="selectPracticeForNotes({{ $practice->id }})"
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
                                @can('view', $practice)
                                    <a
                                        href="{{ route('practice.show', ['id' => $practice->id]) }}"
                                        wire:navigate
                                    >
                                        <x-table-action-button-view />
                                    </a>
                                @endcan

                                @can('update', $practice)
                                    <a
                                        href="{{ route('practice.edit', ['id' => $practice->id]) }}"
                                        wire:navigate
                                    >
                                        <x-table-action-button-edit />
                                    </a>
                                @endcan

                                @can('delete', $practice)
                                    <x-table-action-button-delete
                                        wire:click="selectPracticeForDelete({{ $practice->id }})"
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
                Nessuna pratica trovata.
            </div>
        @endif
    </x-card>

    {{-- Update Practice Status Modal --}}
    @include('partials.practice.update-practice-status-modal')

    {{-- Delete Practice Modal --}}
    <x-delete-modal
        name="delete-practice"
        header="Conferma Eliminazione Pratica"
        function="deletePractice"
        message="Sei sicuro di voler eliminare la pratica di <strong>{{ $selectedPractice?->customer?->full_name }}</strong>?"
    />

    {{-- Filter Modal --}}
    @include('partials.practice.practice-filters-modal')

    {{-- Notes Modal --}}
    @include('partials.practice.practice-notes-modal')

    @if (!$expired)
        @can('import practices')
            {{-- Import Modal --}}
            <x-modals.import-modal
                name="import-practices-modal"
                header="Importa pratiche da Excel"
                submitFunction="importPractices"
                :importFile="$importFile"
                :temporaryImportFile="$temporaryImportFile"
                :users="$users"
                :userId="$userId"
                :userSearch="$userSearch"
                :canAssignUser="auth()->user()->can('assign practice to user')"
            />

            {{-- Import Report Modal --}}
            @include('partials.import.import-report-modal', [
                'modalName' => 'practice-import-report-modal',
                'title' => 'Report importazione pratiche',
                'entityIdLabel' => 'ID pratica',
                'successMessage' => 'Pratica importata correttamente.',
            ])
        @endcan
    @endif
</div>
