<div>
    <x-page-title label="Anagrafica Clienti" class="mt-1" />

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
                @can('export customers')
                    <x-buttons.export-button :disabled="$this->selectedCount === 0" wire:click='exportSelectedCustomers' />
                @endcan

                @can('create customers')
                    <a href="{{ route('customer.create') }}" wire:navigate>
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

        <x-table class="mb-5">
            {{-- Table Header --}}
            <x-slot name="header" class="border-b">
                {{-- Checkbox --}}
                <x-table-header class="w-[40px]">
                    <div class="inline-flex items-center justify-start ps-1 w-full h-full">
                        <x-checkbox :checked="$this->isPageFullySelected" wire:click="toggleSelectPage"
                            wire:key="selectPage-{{ $this->rows->currentPage() }}" />
                    </div>
                </x-table-header>
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
            @foreach ($this->rows as $customer)
                <tr wire:key='{{ $customer->id }}' class="border-y border-collapse">
                    {{-- Checkbox --}}
                    <x-table-data class="w-[40px]">
                        <div class="inline-flex items-center justify-start ps-1 w-full h-full">
                            <x-checkbox wire:click="toggleSelection({{ $customer->id }})" :checked="$this->isSelected($customer->id)" />
                        </div>
                    </x-table-data>

                    <x-table-data label="{{ $customer->formatted_id }}" />
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
        {{ $this->rows->links() }}
    </x-card>

    {{-- Delete User Modal --}}
    <x-delete-modal name="delete-customer" header="Conferma Eliminazione Cliente" function="deleteCustomer"
        message="Sei sicuro di voler eliminare il cliente <strong>{{ $selectedCustomer?->full_name }}</strong>?" />
</div>
