<div>
    <x-page-title label="{{ $this->greeting }}" class="mt-1" />

    <div class="grid grid-cols-24 gap-4">

        {{-- FIRST ROW --}}

        {{-- Practice Counter --}}
        <livewire:admin.dashboard.practice-counter />

        <x-card class="col-span-24 xl:col-span-14" header="Pratiche Liquidate">

        </x-card>

        {{-- END FIRST ROW --}}


        {{-- SECOND ROW --}}

        {{-- Disbursed Comparison --}}
        <livewire:admin.dashboard.disbursed-comparison />

        <x-card class="col-span-24 xl:col-span-13" header="I miei collaboratori">

        </x-card>

        {{-- END SECOND ROW --}}


        {{-- THIRD ROW --}}

        <x-card class="col-span-24 xl:col-span-11" header="Spese mensili team">

        </x-card>

        <x-card class="col-span-24 xl:col-span-13" header="Pratiche per comparto">

        </x-card>

        {{-- END THIRD ROW --}}

    </div>
</div>
