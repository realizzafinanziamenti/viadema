<div>
    <x-page-title label="Leads" class="mt-1" />

    <x-card>
        {{-- Filters and Create Button --}}
        <div class="flex items-center justify-between mb-5">
            <flux:input class="w-sm! xl:w-lg!" wire:model.live.debounce.500ms='search' icon:trailing="magnifying-glass"
                placeholder="Cerca per nome, cognome..." />

            @can('create leads')
                <a href="{{ route('lead.create') }}" wire:navigate>
                    <x-buttons.create-button label="Crea nuovo profilo" />
                </a>
            @endcan
        </div>

        <x-table class="mb-5">
            {{-- Table Header --}}
            <x-slot name="header" class="border-b">
                <x-table-header label="Codice ID" class="w-2/20" />
                <x-table-header label="Possibile cliente" class="w-4/20" />
                <x-table-header label="Tipologia" class="w-3/20" />
                <x-table-header label="Comunicazioni" class="w-3/20" />
                <x-table-header label="Stato" class="w-3/20" />
                <x-table-header label="Canale di acquisizione" class="w-3/20" />
                <x-table-header label="Collaboratore" class="w-4/20" />
                <x-table-header class="w-[150px]">
                    {{-- Actions --}}
                </x-table-header>
            </x-slot>

            {{-- Table body --}}
            @foreach ($leads as $lead)
                <tr wire:key='{{ $lead->id }}' class="border-y border-collapse">
                    <x-table-data label="{{ $lead->id }}" />
                    <x-table-data truncate label="{{ $lead->full_name }}" />
                    <x-table-data truncate label="{{ $lead->customerType?->name ?? 'N/D' }}" />
                    <x-table-data truncate label="{{ $lead->lead_communication?->getLabelText() ?? 'N/D' }}" />
                    <x-table-data truncate class="uppercase font-semibold {{ $lead->lead_status?->getLabelColor() }}"
                        label="{{ $lead->lead_status?->getLabeltext() ?? 'N/D' }}" />
                    <x-table-data truncate label="{{ $lead->lead_source?->getLabelText() ?? 'N/D' }}" />

                    <x-table-data class="inline-flex items-center">
                        <x-user-table-data :user="$lead->user" />
                    </x-table-data>

                    {{-- Actions --}}
                    <x-table-data>
                        <div class="flex items-center justify-end w-full gap-3">
                            @can('view', $lead)
                                {{-- <a href="{{ route('lead.show', ['id' => $lead->id]) }}" wire:navigate> --}}
                                <x-table-action-button-view />
                                {{-- </a> --}}
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
        {{ $leads->links() }}
    </x-card>

    {{-- Delete Lead Modal --}}
    <x-delete-modal name="delete-lead" header="Conferma Eliminazione Lead" function="deleteLead"
        message="Sei sicuro di voler eliminare il lead <strong>{{ $selectedLead?->full_name }}</strong>?" />
</div>
