@props([
    'label',
    'fromLabel' => 'Da',
    'toLabel' => 'A',
])

<div {{ $attributes->class(['flex min-w-0 flex-col gap-1.5']) }}>
    <flux:label>{{ $label }}</flux:label>

    <div
        class="
            grid min-w-0
            grid-cols-[auto_minmax(0,1fr)_auto_minmax(0,1fr)]
            items-start gap-x-1
        "
    >
        {{-- From label --}}
        <span
            class="
                whitespace-nowrap pt-2.5
                text-[11px] font-medium leading-none
                text-gray-custom-4
            "
        >
            {{ $fromLabel }}
        </span>

        {{-- From input --}}
        <div class="min-w-0">
            {{ $from }}
        </div>

        {{-- To label --}}
        <span
            class="
                ml-1 whitespace-nowrap pt-2.5
                text-[11px] font-medium leading-none
                text-gray-custom-4
            "
        >
            {{ $toLabel }}
        </span>

        {{-- To input --}}
        <div class="min-w-0">
            {{ $to }}
        </div>
    </div>
</div>
