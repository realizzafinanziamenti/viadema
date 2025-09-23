<div>
    <x-page-title label="Elenco Attività" class="mt-1" />

    <x-card>
        <x-table class="mb-5 z-10">
            {{-- Table Header --}}
            <x-slot name="header" class="border-b">
                <x-table-header label="Descrizione" />
                <x-table-header label="Tipo" class="w-[200px]" />
                <x-table-header label="Azione" class="w-[200px]" />
                <x-table-header label="Effettuato da" class="w-[200px]" />
                <x-table-header label="Data e ora" class="w-[160px]" />
                <x-table-header class="w-[60px]">
                    {{-- Actions --}}
                </x-table-header>
            </x-slot>

            {{-- Table body --}}
            @foreach ($activityLogs as $log)
                <tr wire:key='{{ $log->id }}' class="border-y border-collapse z-10">

                    <x-table-data truncate label="{{ $log->description }}" />
                    <x-table-data truncate label="{{ $this->formatFieldValue($log->log_name) ?? '-' }}" />
                    <x-table-data truncate label="{{ $this->formatFieldValue($log->event) ?? '-' }}" />
                    <x-table-data truncate label="{{ $log->causer?->full_name ?? 'Sistema' }}" />
                    <x-table-data truncate label="{{ $log->created_at->format('d/m/Y H:i') }}" />

                    {{-- Actions --}}
                    <x-table-data>
                        <div class="flex items-center justify-end w-full gap-3">
                            @can('view', $log)
                                <div title="Visualizza dettagli attività"
                                    wire:click="selectLogForDetails({{ $log->id }})">
                                    <x-table-action-button-view />
                                </div>
                            @endcan
                        </div>
                    </x-table-data>
                </tr>
            @endforeach
        </x-table>

        {{-- Pagination buttons --}}
        {{ $activityLogs->links() }}
    </x-card>

    {{-- Log Details Modal --}}
    @include('partials.activity-log.log-details-modal')
</div>
