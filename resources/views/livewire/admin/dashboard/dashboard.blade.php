<div>
    <x-page-title label="{{ $this->greeting }}" class="mt-1" />

    <div class="grid grid-cols-12 gap-4">

        {{-- First Row --}}
        <x-card class="col-span-12 xl:col-span-4">
            <div class='text-gray-custom-5 font-bold text-lg leading-5'>
                Numero pratiche
            </div>
        </x-card>

        <x-card class="col-span-12 xl:col-span-8" header="Pratiche Liquidate">

        </x-card>
        {{-- End First Row --}}

        {{-- Second Row --}}
        <x-card class="col-span-12 xl:col-span-5" header="Liquidato">

        </x-card>

        <x-card class="col-span-12 xl:col-span-7" header="I miei collaboratori">

        </x-card>
        {{-- End Second Row --}}

        {{-- Third Row --}}
        <x-card class="col-span-12 xl:col-span-5" header="Spese mensili team">

        </x-card>

        <x-card class="col-span-12 xl:col-span-7" header="Pratiche per comparto">

        </x-card>
        {{-- End Third Row --}}

    </div>
</div>
