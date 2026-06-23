<x-dashboard.dashboard-card class="col-span-24 xl:col-span-14 h-[300px] flex flex-col gap-3"
    header="Ultime pratiche liquidate">

    @if (count($practices) > 0)
        <x-table class="mt-1" minWidth="min-w-[500px]">
            {{-- Table header --}}
            <x-slot name="header" class="border-b">
                <x-table-header height="h-8" label="ID" class="w-[80px]" />
                <x-table-header height="h-8" label="Cliente" class="w-1/5" />
                <x-table-header height="h-8" label="Email" class="w-1/5" />
                <x-table-header height="h-8" label="Cellulare" class="w-1/5" />
                <x-table-header height="h-8" label="Pratiche" class="w-1/5 text-center" />
                <x-table-header height="h-8" label="Liquidato" class="w-1/5" />
                <x-table-header height="h-8" class="w-[50px]">
                    {{-- Actions --}}
                </x-table-header>
            </x-slot>

            {{-- Table body --}}
            @foreach ($practices as $practice)
                <tr wire:key='{{ $practice->id }}' class="border-y border-collapse">
                    <x-table-data height="h-10" label="{{ $practice->id }}" />
                    <x-table-data height="h-10" truncate label="{{ $practice->customer?->full_name }}" />
                    <x-table-data height="h-10" truncate label="{{ $practice->customer?->email ?? 'N/D' }}" />
                    <x-table-data height="h-10" truncate label="{{ $practice->customer?->phone ?? 'N/D' }}" />
                    <x-table-data height="h-10" truncate label="{{ $practice->customer?->practices_count }}"
                        class="text-center" />
                        <x-table-data height="h-10" truncate
                        label="{{
                            $practice->opportunity?->amount_disbursed !== null
                                ? number_format((float) $practice->opportunity->amount_disbursed, 2, ',', '.') . '€'
                                : 'N/D'
                        }}" />

                    {{-- Actions --}}
                    <x-table-data height="h-10" class="inline-flex items-center justify-end w-full gap-3">
                        @can('view', $practice)
                            <a href="{{ route('practice.show', ['id' => $practice->id]) }}" wire:navigate>
                                <x-table-action-button-view />
                            </a>
                        @endcan
                    </x-table-data>
                </tr>
            @endforeach
        </x-table>
    @else
        <div class="text-sm py-3">
            Nessuna pratica liquidata trovata.
        </div>
    @endif

</x-dashboard.dashboard-card>
