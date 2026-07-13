<div>
    <x-page-title label="Leads" class="mt-1" />

    <x-card>
        {{-- Filters and Create Button --}}
        <div class="flex items-center justify-between gap-4 mb-5">
            <div class="flex items-center gap-4 flex-1">
                <div class="w-full max-w-md 2xl:max-w-lg!">
                    <flux:input class="w-sm! xl:w-lg!" wire:model.live.debounce.500ms='search'
                        icon:trailing="magnifying-glass" placeholder="Cerca per nome, cognome..." />
                </div>
            </div>

            <div class="flex items-center gap-4">
                <x-buttons.filter-modal-button />
                @can('export leads')
                    <x-buttons.export-button :disabled="$this->selectedCount === 0" wire:click='exportSelectedLeads' />
                @endcan

                @can('import leads')
                    <x-buttons.import-button wire:click="openImportModal" />
                @endcan

                @can('create leads')
                    <a href="{{ route('lead.create') }}" wire:navigate>
                        <x-buttons.create-button />
                    </a>
                @endcan
            </div>
        </div>

        {{-- Bulk selections --}}
        @if ($this->hasSelection)
            <div class="text-sm ml-3.5 mb-1.5">
                Elementi selezionati: {{ $this->selectedCount }}
                <button class="underline ml-2 cursor-pointer hover:text-azure-custom"
                    wire:click="selectAllResults">Seleziona tutti</button>
                <button class="underline ml-2 cursor-pointer hover:text-azure-custom"
                    wire:click="clearSelection">Deseleziona tutti</button>
            </div>
        @endif

        @if (count($this->rows) > 0)
            <x-table class="mb-5" minWidth="min-w-[1740px]">
                {{-- Table Header --}}
                <x-slot name="header" class="border-b">
                    {{-- Checkbox --}}
                    <x-table-header class="w-[40px]">
                        <div class="inline-flex items-center justify-start ps-1 w-full h-full">
                            <x-checkbox wire:model="pageSelected" wire:click="toggleSelectPage" />
                        </div>
                    </x-table-header>
                    <x-table-header label="N. Trattativa" class="w-[100px]" />
                    <x-table-header label="Tipologia" class="w-[160px]" />
                    <x-table-header label="Nominativo" class="w-1/2" />
                    <x-table-header label="Telefono" class="w-[160px]" />
                    <x-table-header label="Stato" class="w-[160px]" />
                    <x-table-header label="Provenienza" class="w-[160px]" />
                    <x-table-header label="Assegnato a" class="w-1/2" />
                    <x-table-header label="Data creazione" class="w-[120px]" />
                    <x-table-header label="Data ricontatto" class="w-[140px]" />
                    <x-table-header label="Note" class="w-[60px]" />
                    <x-table-header class="w-[150px]">
                        {{-- Actions --}}
                    </x-table-header>
                </x-slot>

                {{-- Table body --}}
                @foreach ($this->rows as $lead)
                    <tr wire:key='{{ $lead->id }}' class="border-y border-collapse">
                        {{-- Checkbox --}}
                        <x-table-data class="w-[40px]">
                            <div class="inline-flex items-center justify-start ps-1 w-full h-full">
                                <x-checkbox wire:click="toggleSelection({{ $lead->id }})" :checked="$this->isSelected($lead->id)"
                                    wire:key="row-checkbox-{{ $lead->id }}-{{ (int) $this->isSelected($lead->id) }}" />
                            </div>
                        </x-table-data>

                        <x-table-data truncate label="{{ $lead->formatted_id }}" />
                        <x-table-data truncate label="{{ $lead->customerType?->name ?? 'N/D' }}" />
                        <x-table-data truncate label="{{ $lead->full_name }}" />
                        <x-table-data truncate label="{{ $lead->phone ?? 'N/D' }}" />

                        <x-table-data>
                            @if (Gate::allows('updateLeadStatus', $lead))
                                <x-clickable-badge :property="$lead->lead_status?->getLabelText()" :css="$lead->lead_status?->getLabelColor()"
                                    wire:click="selectLeadForStatus({{ $lead->id }})" title="Cambia stato lead" />
                            @else
                                <x-badge :property="$lead->lead_status?->getLabelText()" :css="$lead->lead_status?->getLabelColor()" />
                            @endif
                        </x-table-data>

                        <x-table-data truncate label="{{ $lead->lead_source?->getLabelText() ?? 'N/D' }}" />

                        <x-table-data truncate class="flex items-center">
                            <x-user-table-data :user="$lead->user" />
                        </x-table-data>

                        <x-table-data truncate label="{{ $lead->formatted_created_at }}" />
                        <x-table-data truncate label="{{ $lead->recontact_date?->format('d/m/Y') ?? 'N/D' }}" />

                        {{-- Notes --}}
                        <x-table-data>
                            @if ($lead->notes)
                                <div class="flex items-center justify-center w-full relative">
                                    <button class="relative cursor-pointer" title="Visualizza note"
                                        wire:click="selectLeadForNotes({{ $lead->id }})">
                                        <x-icons.icon-akar-chat-bubble class="text-gray-custom-3" />
                                        <div
                                            class="absolute right-0 bottom-[2px] flex items-center justify-center w-3 h-3 text-[10px] rounded-full bg-orange-custom">
                                        </div>
                                    </button>
                                </div>
                            @else
                                <div class="flex items-center justify-center w-full" title="Nessuna nota disponibile">
                                    <x-icons.icon-akar-chat-bubble class="text-gray-custom-3" />
                                </div>
                            @endif
                        </x-table-data>

                        {{-- Actions --}}
                        <x-table-data>
                            <div class="flex items-center justify-end w-full gap-3">
                                @can('view', $lead)
                                    <a href="{{ route('lead.show', ['id' => $lead->id]) }}" wire:navigate>
                                        <x-table-action-button-view />
                                    </a>
                                @endcan

                                @can('update', $lead)
                                    <a href="{{ route('lead.edit', ['id' => $lead->id]) }}" wire:navigate>
                                        <x-table-action-button-edit />
                                    </a>
                                @endcan

                                @can('delete', $lead)
                                    <x-table-action-button-delete wire:click='selectLeadForDelete({{ $lead->id }})' />
                                @endcan
                            </div>
                        </x-table-data>
                    </tr>
                @endforeach
            </x-table>

            {{-- Pagination buttons --}}
            {{ $this->rows->links() }}
        @else
            <div class="text-base py-4">
                Nessun lead trovato.
            </div>
        @endif
    </x-card>

    {{-- Update Lead Status Modal --}}
    @include('partials.customer.update-lead-status-modal')

    {{-- Delete Lead Modal --}}
    <x-delete-modal name="delete-lead" header="Conferma Eliminazione Lead" function="deleteLead"
        message="Sei sicuro di voler eliminare il lead <strong>{{ $selectedLead?->full_name }}</strong>?" />

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
</div>
