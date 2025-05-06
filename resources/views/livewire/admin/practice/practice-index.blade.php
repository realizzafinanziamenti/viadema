<div>
    @if ($productType)
        <x-page-title label="{{ $productType->name }}" class="mt-1" />
    @elseif ($expired)
        <x-page-title label="Archivio pratiche" class="mt-1" />
    @else
        <x-page-title label="Gestione pratiche" class="mt-1" />
    @endif

    <x-card>
        {{-- Filters and Create Button --}}
        <div class="flex items-center justify-between mb-5">
            <flux:input class="w-sm! xl:w-lg!" wire:model.live.debounce.500ms='search' icon:trailing="magnifying-glass"
                placeholder="Cerca..." />

            @if (!$expired)
                @can('create customers')
                    {{-- <a href="{{ route('customer.create') }}" wire:navigate> --}}
                    <flux:button icon="plus" class="bg-blue-custom! hover:bg-blue-custom-hover!  text-white! px-10">Crea
                        nuova
                        pratica</flux:button>
                    {{-- </a> --}}
                @endcan
            @endif
        </div>

        <x-table class="mb-5">
            {{-- Table Header --}}
            <x-slot name="header" class="border-b">
                <x-table-header label="Id pratica" class="w-2/16" />
                <x-table-header label="Cliente" class="w-3/16" />

                @if (!$productType)
                    <x-table-header label="Prodotto" class="w-3/16" />
                @endif

                <x-table-header label="Data apertura" class="w-2/16" />
                <x-table-header label="Codice fiscale" class="w-3/16" />
                <x-table-header label="Stato pratica" class="min-w-[130px] w-2/16" />
                <x-table-header label="Collaboratore" class="w-4/16" />
                <x-table-header label="Note" class="w-[50px]" />
                <x-table-header class="w-[150px]">
                    {{-- Actions --}}
                </x-table-header>
            </x-slot>

            {{-- Table body --}}
            @foreach ($practices as $practice)
                <tr wire:key='{{ $practice->id }}' class="border-y border-collapse">
                    <x-table-data label="{{ $practice->practice_code }}" />
                    <x-table-data truncate label="{{ $practice->customer->full_name }}" />

                    @if (!$productType)
                        <x-table-data truncate class="font-bold!" label="{{ $practice->productType->name }}" />
                    @endif

                    <x-table-data truncate label="{{ $practice->formatted_started_at }}" />
                    <x-table-data truncate label="{{ $practice->customer->tax_id }}" />

                    <x-table-data truncate>
                        <div
                            class="flex items-center justify-between w-full max-w-[105px] py-1 px-2 border rounded-lg text-[13px] gap-x-1 font-bold {{ $practice->practice_status->getLabelColor() }}">
                            <div class="truncate" title="{{ $practice->practice_status->getLabelText() }}">
                                {{ $practice->practice_status->getLabelText() }}</div>
                            <x-icons.icon-akar-chevron-left-small default="size-3" class="shrink-0" />
                        </div>
                    </x-table-data>

                    <x-table-data class="inline-flex items-center">
                        <x-user-table-data :user="$practice->teamMember" />
                    </x-table-data>

                    <x-table-data>
                        <div class="flex items-center justify-center w-full">
                            <x-icons.icon-akar-chat-bubble class="text-gray-custom-3" />
                        </div>
                    </x-table-data>

                    {{-- Actions --}}
                    <x-table-data>
                        <div class="flex items-center justify-end w-full gap-3">
                            @can('view', $practice)
                                {{-- <a href="{{ route('practice.show', ['id' => $practice->id]) }}" wire:navigate> --}}
                                <x-table-action-button-view />
                                {{-- </a> --}}
                            @endcan

                            @can('update', $practice)
                                {{-- <a href="{{ route('practice.edit', ['id' => $practice->id]) }}" wire:navigate> --}}
                                <x-table-action-button-edit />
                                {{-- </a> --}}
                            @endcan

                            @can('delete', $practice)
                                <x-table-action-button-delete wire:click='selectPracticeForDelete({{ $practice->id }})' />
                            @endcan
                        </div>
                    </x-table-data>
                </tr>
            @endforeach
        </x-table>

        {{-- Pagination buttons --}}
        {{ $practices->links() }}
    </x-card>
</div>
