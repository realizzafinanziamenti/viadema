<div>
    <x-page-title label="Calendario" class="mt-1" />

    <x-card>

        {{-- Calendar Nav --}}
        <div class="flex items-center gap-5">

            {{-- Monthly Nav --}}
            <div>
                {{-- Prev Month Button --}}
                <button type="button" wire:click="prev"
                    class="relative inline-flex items-center px-2 py-2 text-sm font-medium leading-5 transition duration-150 ease-in-out rounded bg-gray-1 text-gray-5 hover:bg-gray-3 focus:outline-none">
                    <x-icons.icon-akar-chevron-left-small />
                </button>

                {{-- Current Month --}}
                <div>
                    {{ $currentMonth }}
                </div>

                {{-- Next Button --}}
                <button type="button" wire:click="next"
                    class="relative inline-flex items-center px-2 py-2 text-sm font-medium leading-5 transition duration-150 ease-in-out rounded bg-gray-1 text-gray-5 hover:bg-gray-3 focus:outline-none">
                    <x-icons.icon-akar-chevron-right-small />
                </button>
            </div>

        </div>

    </x-card>
</div>
