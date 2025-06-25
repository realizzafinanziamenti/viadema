<x-dashboard.dashboard-card class="col-span-24 xl:col-span-11 h-[300px] flex flex-col" header="Liquidato">

    {{-- Up --}}
    <div class="flex items-center justify-end gap-8 py-0.5 px-8 text-sm text-gray-custom-5 border border-black">
        <div class="flex items-center gap-1.5">
            <div class="h-3.5 w-3.5 bg-blue-custom rounded-full"></div>
            {{ $this->lastMonthName }}
        </div>

        <div class="flex items-center gap-1.5">
            <div class="h-3.5 w-3.5 bg-purple-custom rounded-full"></div>
            {{ $this->currentMonthName }}
        </div>
    </div>

    <div class="flex items-center gap-5 p-1 flex-1">

        {{-- Left --}}
        <div class="w-1/4 h-full border border-black truncate">
            <div class="text-2xl font-bold text-black-custom truncate mt-1.5">{{ $this->currentMonthDisbursedFormatted }}
            </div>
            <div class="text-sm text-gray-custom-5">Liquidato {{ $this->currentMonthName }}</div>
            <div class="mt-2 w-[90px]">
                <x-dashboard.dashboard-button label="{{ $this->percentageComparison }}"
                    class="bg-green-custom text-white" />
            </div>
        </div>

        {{-- Right --}}
        <div class="w-3/4 h-full border border-black">

        </div>

    </div>
</x-dashboard.dashboard-card>
