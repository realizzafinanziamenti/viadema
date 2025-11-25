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
                @can('import leads')
                    <x-buttons.import-button />
                @endcan

                @can('create leads')
                    <a href="{{ route('lead.create') }}" wire:navigate>
                        <x-buttons.create-button label="Crea nuovo profilo" />
                    </a>
                @endcan
            </div>
        </div>

        <x-table class="mb-5" minWidth="min-w-[1600px]">
            {{-- Table Header --}}
            <x-slot name="header" class="border-b">
                <x-table-header label="N. Trattativa" class="w-[100px]" />
                <x-table-header label="Tipologia" class="w-[160px]" />
                <x-table-header label="Nominativo" class="w-1/2" />
                <x-table-header label="Telefono" class="w-[160px]" />
                <x-table-header label="Stato" class="w-[160px]" />
                <x-table-header label="Provenienza" class="w-[160px]" />
                <x-table-header label="Assegnato a" class="w-1/2" />
                <x-table-header label="Data creazione" class="w-[100px]" />
                <x-table-header label="Ultimo contatto" class="w-[100px]" />
                <x-table-header label="Note" class="w-[50px]" />
                <x-table-header class="w-[150px]">
                    {{-- Actions --}}
                </x-table-header>
            </x-slot>

            {{-- Table body --}}
            @foreach ($this->leads as $lead)
                <tr wire:key='{{ $lead->id }}' class="border-y border-collapse">
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
                    <x-table-data truncate label="{{ $lead->formatted_updated_at }}" />

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
        {{ $this->leads->links() }}
    </x-card>

    {{-- Update Lead Status Modal --}}
    @include('partials.customer.update-lead-status-modal')

    {{-- Delete Lead Modal --}}
    <x-delete-modal name="delete-lead" header="Conferma Eliminazione Lead" function="deleteLead"
        message="Sei sicuro di voler eliminare il lead <strong>{{ $selectedLead?->full_name }}</strong>?" />

    {{-- Notes Modal --}}
    @include('partials.lead.lead-notes-modal')
</div>
