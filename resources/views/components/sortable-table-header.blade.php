@props([
    'label',
    'field',
    'sortField' => null,
    'sortDirection' => 'asc',
    'sortMethod' => 'sortBy',
    'height' => 'h-12',
])

@php
    $isActive = $sortField === $field;

    $ariaSort = match (true) {
        !$isActive => 'none',
        $sortDirection === 'asc' => 'ascending',
        default => 'descending',
    };

    $nextDirectionLabel = $isActive && $sortDirection === 'asc'
        ? 'decrescente'
        : 'crescente';
@endphp

<th
    scope="col"
    aria-sort="{{ $ariaSort }}"
    title="{{ $label }}"
    {{ $attributes->merge([
        'class' => $height . ' px-3 truncate font-medium text-[13px]',
    ]) }}
>
    <button
        type="button"
        wire:click="{{ $sortMethod }}('{{ $field }}')"
        class="group inline-flex w-full items-center gap-1 rounded-sm text-left hover:text-azure-custom focus:outline-none focus-visible:ring-2 focus-visible:ring-zinc-300"
        aria-label="Ordina {{ $label }} in modo {{ $nextDirectionLabel }}"
        title="Ordina {{ $label }} in modo {{ $nextDirectionLabel }}"
    >
        <span class="truncate">
            {{ $label }}
        </span>

        {{-- Fixed space prevents the header from moving when the arrow appears --}}
        <span
            class="inline-flex size-4 shrink-0 items-center justify-center text-sm leading-none"
            aria-hidden="true"
        >
            @if ($isActive)
                {{ $sortDirection === 'asc' ? '↑' : '↓' }}
            @endif
        </span>
    </button>
</th>
