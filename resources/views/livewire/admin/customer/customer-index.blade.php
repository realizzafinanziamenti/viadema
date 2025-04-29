<div>
    <x-page-title label="Anagrafica Clienti" class="mt-1" />

    <x-card>
        {{-- Filters and Create Button --}}
        <div class="flex items-center justify-between mb-5">
            <flux:input class="w-sm! xl:w-lg!" wire:model.live.debounce.500ms='search' icon:trailing="magnifying-glass"
                placeholder="Cerca per nome, cognome..." />

            @can('create customers')
                {{-- <a href="{{ route('customer.create') }}" wire:navigate> --}}
                <flux:button icon="plus" class="bg-blue-custom! hover:bg-blue-custom-hover!  text-white! px-10">Crea
                    nuova
                    anagrafica</flux:button>
                {{-- </a> --}}
            @endcan
        </div>

        <x-table class="mb-5">
            {{-- Table Header --}}
            <x-slot name="header" class="border-b">
                <x-table-header label="Id cliente" class="w-1/12" />
                <x-table-header label="Nome cliente" class="w-2/12" />
                <x-table-header label="Cellulare" class="w-[160px]" />
                <x-table-header label="Codice fiscale" class="w-[160px]" />
                <x-table-header label="Città" class="w-[160px]" />
                <x-table-header label="Email" class="w-2/12" />
                <x-table-header label="Collaboratore" class="w-2/12" />
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
                    <x-table-data truncate label="{{ $customer->tax_id }}" />
                    <x-table-data truncate label="{{ $customer->city }}" />
                    <x-table-data truncate label="{{ $customer->email }}" />

                    <x-table-data class="inline-flex items-center gap-2.5">
                        {{-- Profile photo --}}
                        <img class="object-cover w-10 h-10 border rounded-full shrink-0"
                            src="{{ $customer->user->getProfilePhotoUrl() }}" alt="Immagine Profilo Agenzia">

                        <span class="truncate"
                            title="{{ $customer->user->full_name }}">{{ $customer->user->full_name }}</span>
                    </x-table-data>

                    {{-- Actions --}}
                    <x-table-data>
                        <div class="flex items-center justify-end w-full gap-3">
                            @can('view', $customer)
                                {{-- <a href="{{ route('customer.show', ['id' => $customer->id]) }}" wire:navigate> --}}
                                <x-table-action-button-view />
                                {{-- </a> --}}
                            @endcan

                            @can('update', $customer)
                                {{-- <a href="{{ route('customer.edit', ['id' => $customer->id]) }}" wire:navigate> --}}
                                <x-table-action-button-edit />
                                {{-- </a> --}}
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
</div>
