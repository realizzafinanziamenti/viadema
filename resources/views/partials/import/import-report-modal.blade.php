@php
    $modalName = $modalName ?? 'lead-import-report-modal';
    $title = $title ?? 'Report importazione lead';
    $entityIdLabel = $entityIdLabel ?? 'ID lead';
    $successMessage = $successMessage ?? 'Lead importato correttamente.';

    $report = $this->selectedImportReport;
    $rows = $report !== null
        ? $this->importReportRows
        : null;
@endphp

<x-modal
    :name="$modalName"
    maxWidth="5xl"
>
    <div class="flex flex-col">
        {{-- Header --}}
        <div class="mb-6 flex items-start justify-between gap-4">
            <div class="min-w-0">
                <x-modal-header :label="$title" />

                @if ($report)
                    <div class="mt-1 truncate text-sm text-gray-custom-4">
                        {{ $report->file_name }}
                    </div>
                @endif
            </div>

            <flux:button
                type="button"
                size="sm"
                variant="ghost"
                icon="x-mark"
                inset
                x-on:click="$dispatch('close-modal', '{{ $modalName }}')"
                title="Chiudi report"
                aria-label="Chiudi report"
                class="shrink-0"
            />
        </div>

        @if ($report)
            @php
                $statusValue = $report->status->value;

                $statusLabel = match ($statusValue) {
                    'pending' => 'In attesa',
                    'processing' => 'In elaborazione',
                    'completed' => 'Completato',
                    'failed' => 'Fallito',
                    default => ucfirst($statusValue),
                };

                $statusClasses = match ($statusValue) {
                    'completed' => 'bg-green-50 text-green-700 ring-green-600/20',
                    'failed' => 'bg-red-50 text-red-700 ring-red-600/20',
                    'pending', 'processing' => 'bg-amber-50 text-amber-700 ring-amber-600/20',
                    default => 'bg-zinc-50 text-zinc-700 ring-zinc-600/20',
                };
            @endphp

            {{-- General failure --}}
            @if ($report->error_message)
                <div
                    class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"
                >
                    <div class="font-semibold">
                        Importazione interrotta
                    </div>

                    <div class="mt-1 break-words">
                        {{ $report->error_message }}
                    </div>
                </div>
            @endif

            {{-- Report metadata --}}
            <div class="mb-6 rounded-lg border border-zinc-200 bg-zinc-50 p-4">
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div>
                        <div class="text-xs font-medium uppercase tracking-wide text-gray-custom-4">
                            Stato
                        </div>

                        <div class="mt-1">
                            <span
                                class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset {{ $statusClasses }}"
                            >
                                {{ $statusLabel }}
                            </span>
                        </div>
                    </div>

                    <div>
                        <div class="text-xs font-medium uppercase tracking-wide text-gray-custom-4">
                            Avviato
                        </div>

                        <div class="mt-1 text-sm font-medium text-black-custom">
                            {{ $report->started_at?->format('d/m/Y H:i:s') ?? 'N/D' }}
                        </div>
                    </div>

                    <div>
                        <div class="text-xs font-medium uppercase tracking-wide text-gray-custom-4">
                            Completato
                        </div>

                        <div class="mt-1 text-sm font-medium text-black-custom">
                            {{ $report->completed_at?->format('d/m/Y H:i:s') ?? 'N/D' }}
                        </div>
                    </div>

                    <div class="min-w-0">
                        <div class="text-xs font-medium uppercase tracking-wide text-gray-custom-4">
                            File
                        </div>

                        <div
                            class="mt-1 truncate text-sm font-medium text-black-custom"
                            title="{{ $report->file_name }}"
                        >
                            {{ $report->file_name }}
                        </div>
                    </div>
                </div>
            </div>

            {{-- Counters --}}
            <div class="mb-6 grid gap-3 sm:grid-cols-3">
                <div class="rounded-lg border border-zinc-200 bg-white p-4">
                    <div class="text-sm text-gray-custom-4">
                        Righe elaborate
                    </div>

                    <div class="mt-1 text-2xl font-bold text-black-custom">
                        {{ $report->total_rows }}
                    </div>
                </div>

                <div class="rounded-lg border border-green-200 bg-green-50 p-4">
                    <div class="text-sm text-green-700">
                        Importate
                    </div>

                    <div class="mt-1 text-2xl font-bold text-green-700">
                        {{ $report->imported_rows }}
                    </div>
                </div>

                <div class="rounded-lg border border-red-200 bg-red-50 p-4">
                    <div class="text-sm text-red-700">
                        Fallite
                    </div>

                    <div class="mt-1 text-2xl font-bold text-red-700">
                        {{ $report->failed_rows }}
                    </div>
                </div>
            </div>

            {{-- Filters --}}
            <div class="mb-4 flex flex-wrap items-center gap-2">
                <flux:button
                    type="button"
                    size="sm"
                    :variant="$importReportFilter === 'all' ? 'primary' : 'ghost'"
                    wire:click="setImportReportFilter('all')"
                >
                    Tutte
                    <span class="ms-1 text-xs opacity-75">
                        {{ $report->total_rows }}
                    </span>
                </flux:button>

                <flux:button
                    type="button"
                    size="sm"
                    :variant="$importReportFilter === 'imported' ? 'primary' : 'ghost'"
                    wire:click="setImportReportFilter('imported')"
                >
                    Importate
                    <span class="ms-1 text-xs opacity-75">
                        {{ $report->imported_rows }}
                    </span>
                </flux:button>

                <flux:button
                    type="button"
                    size="sm"
                    :variant="$importReportFilter === 'failed' ? 'primary' : 'ghost'"
                    wire:click="setImportReportFilter('failed')"
                >
                    Fallite
                    <span class="ms-1 text-xs opacity-75">
                        {{ $report->failed_rows }}
                    </span>
                </flux:button>
            </div>

            {{-- Rows table --}}
            @if ($rows && $rows->count() > 0)
                <div class="overflow-hidden rounded-lg border border-zinc-200">
                    <div class="overflow-x-auto">
                        <table class="min-w-[900px] w-full border-collapse text-left text-sm">
                            <thead class="bg-zinc-50">
                                <tr class="border-b border-zinc-200">
                                    <th class="w-20 px-4 py-3 font-semibold text-black-custom">
                                        Riga
                                    </th>

                                    <th class="w-48 px-4 py-3 font-semibold text-black-custom">
                                        Nominativo
                                    </th>

                                    <th class="w-32 px-4 py-3 font-semibold text-black-custom">
                                        Esito
                                    </th>

                                    <th class="px-4 py-3 font-semibold text-black-custom">
                                        Dettaglio
                                    </th>

                                    <th class="w-28 px-4 py-3 font-semibold text-black-custom">
                                        {{ $entityIdLabel }}
                                    </th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-zinc-200 bg-white">
                                @foreach ($rows as $row)
                                    @php
                                        $rowStatus = $row->status->value;

                                        $rowErrors = collect($row->errors ?? [])
                                            ->flatten()
                                            ->filter()
                                            ->map(fn ($error) => (string) $error)
                                            ->unique()
                                            ->values();
                                    @endphp

                                    <tr wire:key="import-report-row-{{ $row->id }}">
                                        <td class="px-4 py-3 align-top font-medium text-black-custom">
                                            {{ $row->row_number }}
                                        </td>

                                        <td class="px-4 py-3 align-top">
                                            <div class="font-medium text-black-custom">
                                                {{ $row->label ?: 'N/D' }}
                                            </div>
                                        </td>

                                        <td class="px-4 py-3 align-top">
                                            @if ($rowStatus === 'imported')
                                                <span
                                                    class="inline-flex items-center rounded-full bg-green-50 px-2.5 py-1 text-xs font-semibold text-green-700 ring-1 ring-inset ring-green-600/20"
                                                >
                                                    Importata
                                                </span>
                                            @else
                                                <span
                                                    class="inline-flex items-center rounded-full bg-red-50 px-2.5 py-1 text-xs font-semibold text-red-700 ring-1 ring-inset ring-red-600/20"
                                                >
                                                    Fallita
                                                </span>
                                            @endif
                                        </td>

                                        <td class="px-4 py-3 align-top text-gray-custom-5">
                                            @if ($rowErrors->isNotEmpty())
                                                <ul class="space-y-1">
                                                    @foreach ($rowErrors as $error)
                                                        <li class="flex items-start gap-2">
                                                            <span
                                                                class="mt-[7px] size-1.5 shrink-0 rounded-full bg-red-500"
                                                            ></span>

                                                            <span class="break-words">
                                                                {{ $error }}
                                                            </span>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            @elseif ($row->message)
                                                <div class="break-words">
                                                    {{ $row->message }}
                                                </div>
                                            @elseif ($rowStatus === 'imported')
                                                <span class="text-green-700">
                                                    {{ $successMessage }}
                                                </span>
                                            @else
                                                <span>
                                                    Nessun dettaglio disponibile.
                                                </span>
                                            @endif
                                        </td>

                                        <td class="px-4 py-3 align-top font-medium text-black-custom">
                                            {{ $row->entity_id ?? '—' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Report pagination --}}
                @if ($rows->hasPages())
                    <div class="mt-5">
                        {{ $rows->links() }}
                    </div>
                @endif
            @else
                <div
                    class="rounded-lg border border-dashed border-zinc-300 px-6 py-10 text-center text-sm text-gray-custom-4"
                >
                    @if ($importReportFilter === 'imported')
                        Nessuna riga importata.
                    @elseif ($importReportFilter === 'failed')
                        Nessuna riga fallita.
                    @else
                        Nessun risultato disponibile per questo report.
                    @endif
                </div>
            @endif

            {{-- Footer --}}
            <div class="mt-8 flex justify-end">
                <flux:button
                    type="button"
                    size="sm"
                    variant="primary"
                    x-on:click="$dispatch('close-modal', '{{ $modalName }}')"
                    class="px-10 bg-gray-custom-2 border-gray-custom-2 text-gray-custom-5 hover:bg-gray-custom-3-hover hover:border-gray-custom-3-hover hover:text-white"
                >
                    Chiudi
                </flux:button>
            </div>
        @else
            <div
                class="rounded-lg border border-dashed border-zinc-300 px-6 py-10 text-center text-sm text-gray-custom-4"
            >
                Report non disponibile.
            </div>
        @endif
    </div>
</x-modal>
