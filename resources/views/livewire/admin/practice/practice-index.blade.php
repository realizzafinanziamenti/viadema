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
        <div class="flex items-center justify-between gap-4 mb-5">
            <div class="flex items-center gap-4 flex-1">
                <div class="w-full max-w-sm 2xl:max-w-lg">
                    <flux:input wire:model.live.debounce.500ms='search' icon:trailing="magnifying-glass"
                        placeholder="Cerca per nome cliente, codice fiscale o id pratica..." />
                </div>

                <x-dropdown-select width="w-52" :selectable-items="$orderBySelect" :selected="$selectedOrderBy->value" placeholder='Ordina per'
                    setFunction="setOrderBy" :has-error="$errors->has('tempSelectedTeamMemberForFilter')" />
            </div>

            <div class="flex items-center gap-4">
                <x-buttons.filter-modal-button />

                @if (!$expired)
                    @can('create practices')
                        <a href="{{ route('practice.create') }}" wire:navigate>
                            <x-buttons.create-button label="Crea nuova pratica" />
                        </a>
                    @endcan
                @endif
            </div>
        </div>

        <x-table class="mb-5 z-10" minWidth="min-w-[1300px]">
            {{-- Table Header --}}
            <x-slot name="header" class="border-b">
                <x-table-header label="Id pratica" class="w-[110px]" />
                <x-table-header label="Cliente" class="w-3/7" />

                @if (!$productType)
                    <x-table-header label="Prodotto" class="w-[160px]" />
                @endif

                <x-table-header label="Data apertura" class="w-[110px]" />
                <x-table-header label="Codice fiscale" class="w-[170px]" />
                <x-table-header label="Stato pratica" class="w-[140px]" />
                <x-table-header label="Collaboratore" class="w-4/7" />
                <x-table-header label="Note" class="w-[50px]" />
                <x-table-header class="w-[150px]">
                    {{-- Actions --}}
                </x-table-header>
            </x-slot>

            {{-- Table body --}}
            @foreach ($practices as $practice)
                <tr wire:key='{{ $practice->id }}' class="border-y border-collapse z-10">
                    <x-table-data truncate class="inline-flex items-center gap-2">
                        @if ($practice->renewability_date <= now())
                            <x-icons.icon-akar-circle-alert class="text-red-custom" />
                        @elseif ($practice->renewability_date > now() && $practice->alert_date <= now())
                            <x-icons.icon-akar-circle-alert class="text-orange-custom" />
                        @endif

                        <div>{{ $practice->practice_code }}</div>
                    </x-table-data>

                    <x-table-data truncate label="{{ $practice->customer?->full_name }}" />

                    @if (!$productType)
                        <x-table-data truncate class="font-bold! w-[160px]"
                            label="{{ $practice->productType?->name }}" />
                    @endif

                    <x-table-data truncate label="{{ $practice->formatted_first_installment_date }}" />
                    <x-table-data truncate label="{{ $practice->customer?->tax_id }}" />

                    <x-table-data>
                        @if (!$expired)
                            <x-clickable-badge :property="$practice->practice_status?->getLabelText()" :css="$practice->practice_status?->getLabelColor()"
                                wire:click="selectPracticeForStatus({{ $practice->id }})" />
                        @else
                            <x-badge :property="$practice->practice_status?->getLabelText()" :css="$practice->practice_status?->getLabelColor()" />
                        @endif
                    </x-table-data>

                    <x-table-data truncate class="inline-flex items-center">
                        @if ($practice->user)
                            <x-user-table-data :user="$practice->user" />
                        @endif
                    </x-table-data>

                    {{-- Notes --}}
                    <x-table-data>
                        @if ($practice->notes)
                            <div class="flex items-center justify-center w-full relative">
                                <button class="relative cursor-pointer"
                                    wire:click="selectPracticeForNotes({{ $practice->id }})">
                                    <x-icons.icon-akar-chat-bubble class="text-gray-custom-3" />
                                    <div
                                        class="absolute right-0 bottom-[2px] flex items-center justify-center w-3 h-3 text-[10px] rounded-full bg-orange-custom">
                                    </div>
                                </button>
                            </div>
                        @else
                            <div class="flex items-center justify-center w-full">
                                <x-icons.icon-akar-chat-bubble class="text-gray-custom-3" />
                            </div>
                        @endif
                    </x-table-data>

                    {{-- Actions --}}
                    <x-table-data>
                        <div class="flex items-center justify-end w-full gap-3">
                            @can('view', $practice)
                                <a href="{{ route('practice.show', ['id' => $practice->id]) }}" wire:navigate>
                                    <x-table-action-button-view />
                                </a>
                            @endcan

                            @can('update', $practice)
                                <a href="{{ route('practice.edit', ['id' => $practice->id]) }}" wire:navigate>
                                    <x-table-action-button-edit />
                                </a>
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

    {{-- Update Practice Status Modal --}}
    @include('partials.practice.update-practice-status-modal')

    {{-- Delete Practice Modal --}}
    <x-delete-modal name="delete-practice" header="Conferma Eliminazione Pratica" function="deletePractice"
        message="Sei sicuro di voler eliminare la pratica di <strong>{{ $selectedPractice?->customer?->full_name }}</strong>?" />

    {{-- Filter Modal --}}
    @include('partials.practice.practice-filters-modal')

    {{-- Notes Modal --}}
    @include('partials.practice.practice-notes-modal')
</div>
