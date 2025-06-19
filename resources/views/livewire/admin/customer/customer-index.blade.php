<div>
    <x-page-title label="Anagrafica Clienti" class="mt-1" />

    <x-card>
        {{-- Filters and Create Button --}}
        <div class="flex items-center justify-between mb-5">
            <flux:input class="w-sm! xl:w-lg!" wire:model.live.debounce.500ms='search' icon:trailing="magnifying-glass"
                placeholder="Cerca per nome, cognome..." />

            @can('create customers')
                <a href="{{ route('customer.create') }}" wire:navigate>
                    <x-buttons.create-button label="Crea nuova anagrafica" />
                </a>
            @endcan
        </div>

        <x-table class="mb-5">
            {{-- Table Header --}}
            <x-slot name="header" class="border-b">
                <x-table-header label="Id cliente" class="w-2/20" />
                <x-table-header label="Nome cliente" class="w-4/20" />
                <x-table-header label="Cellulare" class="w-3/20" />
                <x-table-header label="Codice fiscale" class="w-3/20" />
                <x-table-header label="Città" class="w-3/20" />
                <x-table-header label="Email" class="w-3/20" />
                <x-table-header label="Collaboratore" class="w-4/20" />
                <x-table-header class="w-[150px]">
                    {{-- Actions --}}
                </x-table-header>
            </x-slot>

            {{-- Table body --}}
            @foreach ($customers as $customer)
                <tr wire:key='{{ $customer->id }}' class="border-y border-collapse">
                    <x-table-data label="{{ $customer->id }}" />
                    <x-table-data truncate label="{{ $customer->full_name }}" />
                    <x-table-data truncate label="{{ $customer->phone }}" />
                    <x-table-data truncate label="{{ $customer->tax_id ?? 'N/D' }}" />
                    <x-table-data truncate label="{{ $customer->city ?? 'N/D' }}" />
                    <x-table-data truncate label="{{ $customer->email ?? 'N/D' }}" />

                    <x-table-data class="inline-flex items-center">
                        <x-user-table-data :user="$customer->user" />
                    </x-table-data>

                    {{-- Actions --}}
                    <x-table-data>
                        <div class="flex items-center justify-end w-full gap-3">
                            @can('view', $customer)
                                <a href="{{ route('customer.show', ['id' => $customer->id]) }}" wire:navigate>
                                    <x-table-action-button-view />
                                </a>
                            @endcan

                            @can('update', $customer)
                                <a href="{{ route('customer.edit', ['id' => $customer->id]) }}" wire:navigate>
                                    <x-table-action-button-edit />
                                </a>
                            @endcan

                            @can('delete', $customer)
                                <x-table-action-button-delete wire:click='selectCustomerForDelete({{ $customer->id }})' />
                            @endcan
                        </div>
                    </x-table-data>
                </tr>
            @endforeach
        </x-table>

        {{-- Pagination buttons --}}
        {{ $customers->links() }}
    </x-card>

    {{-- Delete User Modal --}}
    <x-delete-modal name="delete-customer" header="Conferma Eliminazione Cliente" function="deleteCustomer"
        message="Sei sicuro di voler eliminare il cliente <strong>{{ $selectedCustomer?->full_name }}</strong>?" />
</div>
