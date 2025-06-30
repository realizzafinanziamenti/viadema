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

        <x-table class="mb-5" minWidth="min-w-[2000px]">
            {{-- Table Header --}}
            <x-slot name="header" class="border-b">
                <x-table-header label="N. Trattativa" class="w-[100px]" />
                <x-table-header label="Tipologia" class="w-[160px]" />
                <x-table-header label="Nominativo" class="w-3/10" />
                <x-table-header label="Email" class="w-3/10" />
                <x-table-header label="Telefono" class="w-[160px]" />
                <x-table-header label="Città" class="w-[160px]" />
                <x-table-header label="Stato" class="w-[150px]" />
                <x-table-header label="Importo" class="w-[120px]" />
                <x-table-header label="Provenienza" class="w-[160px]" />
                <x-table-header label="Assegnato a" class="w-4/10" />
                <x-table-header label="Data creazione" class="w-[100px]" />
                <x-table-header label="Ultimo contatto" class="w-[100px]" />
                <x-table-header class="w-[150px]">
                    {{-- Actions --}}
                </x-table-header>
            </x-slot>

            {{-- Table body --}}
            @foreach ($leads as $lead)
                <tr wire:key='{{ $lead->id }}' class="border-y border-collapse">
                    <x-table-data truncate label="{{ $lead->formatted_id }}" />
                    <x-table-data truncate label="{{ $lead->customerType?->name ?? 'N/D' }}" />
                    <x-table-data truncate label="{{ $lead->full_name }}" />
                    <x-table-data truncate label="{{ $lead->email ?? 'N/D' }}" />
                    <x-table-data truncate label="{{ $lead->phone ?? 'N/D' }}" />
                    <x-table-data truncate label="{{ $lead->city ?? 'N/D' }}" />

                    <x-table-data>
                        <x-clickable-badge :property="$lead->lead_status?->getLabelText()" :css="$lead->lead_status?->getLabelColor()"
                            wire:click="selectLeadForStatus({{ $lead->id }})" />
                    </x-table-data>

                    <x-table-data truncate label="{{ $lead->formatted_amount ?? 'N/D' }}" />
                    <x-table-data truncate label="{{ $lead->lead_source?->getLabelText() ?? 'N/D' }}" />

                    <x-table-data truncate class="flex items-center">
                        <x-user-table-data :user="$lead->user" />
                    </x-table-data>

                    <x-table-data truncate label="{{ $lead->formatted_created_at }}" />
                    <x-table-data truncate label="{{ $lead->formatted_updated_at }}" />


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
        {{ $leads->links() }}
    </x-card>

    {{-- Update Lead Status Modal --}}
    @include('partials.customer.update-lead-status-modal')

    {{-- Delete Lead Modal --}}
    <x-delete-modal name="delete-lead" header="Conferma Eliminazione Lead" function="deleteLead"
        message="Sei sicuro di voler eliminare il lead <strong>{{ $selectedLead?->full_name }}</strong>?" />
</div>
