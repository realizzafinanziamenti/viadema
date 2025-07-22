<x-dashboard.dashboard-card class="col-span-24 xl:col-span-10 h-[300px] flex flex-col gap-5" header="Numero pratiche">
    <div class="flex items-center gap-5 p-2 flex-1">

        {{-- Left --}}
        <div class="w-1/3 h-full flex items-center justify-center">

            <svg viewBox="0 0 36 36" class="w-full h-auto max-w-[150px]">
                <g transform="rotate(-90 18 18)"> {{-- Rotate to start from the top --}}
                    {{-- Approved --}}
                    <circle r="16" cx="18" cy="18" fill="transparent" stroke-width="4"
                        class="stroke-green-custom"
                        stroke-dasharray="{{ $this->approvedPercentage }}, {{ 100 - $this->approvedPercentage }}"
                        stroke-linecap="round" />

                    {{-- Disbursed --}}
                    <circle r="16" cx="18" cy="18" fill="transparent" stroke-width="4"
                        class="stroke-oil-custom" stroke-linecap="round"
                        stroke-dasharray="{{ $this->disbursedPercentage }}, {{ 100 - $this->disbursedPercentage }}"
                        stroke-dashoffset="-{{ $this->approvedPercentage }}" />

                    {{-- Under Review --}}
                    <circle r="16" cx="18" cy="18" fill="transparent" stroke-width="4"
                        class="stroke-blue-custom" stroke-linecap="round"
                        stroke-dasharray="{{ $this->underReviewPercentage }}, {{ 100 - $this->underReviewPercentage }}"
                        stroke-dashoffset="-{{ $this->approvedPercentage + $this->disbursedPercentage }}" />
                </g>

                {{-- Total Practice Count --}}
                <text x="18" y="18.5" font-size="5" text-anchor="middle"
                    class="font-semibold fill-gray-custom-5">{{ $practiceCount }}</text>
                <text x="18" y="22" font-size="3" text-anchor="middle"
                    class="font-semibold fill-gray-custom-5">Pratiche
                    totali</text>
            </svg>

        </div>

        {{-- Right --}}
        <div class="w-2/3 h-full flex flex-col gap-2.5">
            <x-dashboard.practice-counter-element color="green" countLabel="Totale pratiche deliberate"
                buttonLabel="Vedi deliberate" :practiceCount="$approvedPracticeCount"
                href="{{ route('practice.index', ['status' => $approvedStatus]) }}" />

            <x-dashboard.practice-counter-element color="oil" countLabel="Totale pratiche in istruttoria"
                buttonLabel="Vedi in istruttoria" :practiceCount="$underReviewPracticeCount"
                href="{{ route('practice.index', ['status' => $underReviewStatus]) }}" />

            <x-dashboard.practice-counter-element color="blue" countLabel="Totale pratiche liquidate"
                buttonLabel="Vedi liquidate" :practiceCount="$disbursedPracticeCount"
                href="{{ route('practice.index', ['expired' => 1]) }}" />

        </div>

    </div>
</x-dashboard.dashboard-card>
