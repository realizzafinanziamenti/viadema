<div>
    <x-page-title label="{{ $this->greeting }}" class="mt-1" />

    <div class="grid grid-cols-24 gap-4">

        {{-- FIRST ROW --}}

        {{-- Practice Counter --}}
        @can('view practice counters')
            <livewire:admin.dashboard.practice-counter />
        @endcan

        {{-- Latest Disbursed Practices --}}
        @can('view latest disbursed practices')
            <livewire:admin.dashboard.latest-disbursed-practices />
        @endcan

        {{-- END FIRST ROW --}}


        {{-- SECOND ROW --}}

        {{-- Disbursed Comparison --}}
        @can('view disbursed comparison')
            <livewire:admin.dashboard.disbursed-comparison />
        @endcan

        {{-- User List --}}
        @can('view user list')
            <livewire:admin.dashboard.user-list />
        @endcan

        {{-- END SECOND ROW --}}


        {{-- THIRD ROW --}}

        @can('view monthly expenses')
            <x-card class="col-span-24 xl:col-span-11" header="Spese mensili team">

            </x-card>
        @endcan

        @can('view practices by sector')
            <x-card class="col-span-24 xl:col-span-13" header="Pratiche per comparto">

            </x-card>
        @endcan

        {{-- END THIRD ROW --}}

    </div>
</div>
