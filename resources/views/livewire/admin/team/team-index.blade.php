<div>
    <x-page-title label="Gestione Collaboratori" class="mt-1" />

    <x-card>
        {{-- Filters and Create Button --}}
        <div class="flex items-center justify-between mb-5">
            <flux:input class="w-sm! xl:w-lg!" icon:trailing="magnifying-glass" placeholder="Cerca per nome, cognome..." />

            @can('create team members')
                <a href="{{ route('user.team.create') }}" wire:navigate>
                    <flux:button icon="plus" class="bg-blue-custom! hover:bg-blue-custom-hover!  text-white! px-10">Crea
                        nuova
                        pratica</flux:button>
                </a>
            @endcan
        </div>

        {{-- Team Table --}}
        <x-table class="mb-5">
            <x-slot name="header" class="border-b">
                <x-table-header label="Id collaboratore" class="w-1/12" />
                <x-table-header label="Nome collaboratore" class="w-2/12" />
                <x-table-header label="Personale" class="w-1/12" />
                <x-table-header label="Cellulare" class="w-[160px]" />
                <x-table-header label="Codice fiscale" class="w-[170px]" />
                <x-table-header label="Email" class="w-2/12" />
                <x-table-header label="Città" class="w-2/12" />
                <x-table-header class="w-[150px]">
                    {{-- Actions --}}
                </x-table-header>
            </x-slot>

            {{-- Table body --}}
            @foreach ($teamMembers as $teamMember)
                <tr wire:key='{{ $teamMember->id }}' class="border-y border-collapse">
                    <x-table-data label="{{ $teamMember->id }}" />
                    <x-table-data truncate label="{{ $teamMember->full_name }}" />
                    <x-table-data truncate label="Per ora niente" />
                    <x-table-data truncate label="{{ $teamMember->profile->phone }}" />
                    <x-table-data truncate label="{{ $teamMember->profile->tax_id }}" />
                    <x-table-data truncate label="{{ $teamMember->email }}" />
                    <x-table-data truncate label="{{ $teamMember->profile->city }}" />

                    {{-- Actions --}}
                    <x-table-data class="inline-flex items-center justify-end w-full gap-3">
                        @can('view team members')
                            <a href="{{ route('user.team.show', ['id' => $teamMember->id]) }}" wire:navigate>
                                <x-table-action-button-view />
                            </a>
                        @endcan

                        @can('update team members')
                            <a href="{{ route('user.team.edit', ['id' => $teamMember->id]) }}" wire:navigate>
                                <x-table-action-button-edit />
                            </a>
                        @endcan

                        @can('delete team members')
                            <x-table-action-button-delete wire:click='selectUserForDelete({{ $teamMember->id }})' />
                        @endcan
                    </x-table-data>
                </tr>
            @endforeach
        </x-table>

        {{-- Pagination buttons --}}
        {{ $teamMembers->links() }}
    </x-card>

    {{-- Delete User Modal --}}
    <x-delete-modal name="delete-user" header="Conferma Eliminazione Collaboratore" function="deleteUser"
        message="Sei sicuro di voler eliminare il collaboratore <strong>{{ $selectedUser?->full_name }}</strong>?" />
</div>
