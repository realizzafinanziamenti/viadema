{{-- Customer Details --}}
@include('partials.practice.customer-preview-fields')

{{-- Attachments --}}
@if (!empty($practiceForm->attachments) || (!empty($practice?->attachments) && $practice->attachments->isNotEmpty()))
    <div class="mt-6 flex flex-col gap-1.5">
        <flux:label>Allegati</flux:label>

        {{-- Old Attachements for Uploaded Practice --}}
        @if (!empty($practice?->attachments) && $practice->attachments->isNotEmpty())
            @foreach ($practice->attachments as $attachment)
                <x-display-input value="{{ $attachment->file_name }}" />
            @endforeach
        @endif

        {{-- New Attachments --}}
        @foreach ($practiceForm->attachments as $attachment)
            <x-display-input value="{{ $attachment->getClientOriginalName() }}" />
        @endforeach
    </div>
@endif

{{-- Practice Details --}}
<div class="grid grid-cols-2 gap-6 mt-6">
    {{-- Practice Code --}}
    <div class="flex flex-col gap-1.5">
        <flux:label>Id pratica</flux:label>
        <x-display-input value="{{ $practiceForm->practiceCode }}" />
    </div>
    {{-- Product Type --}}
    <div class="flex flex-col gap-1.5">
        <flux:label>Prodotto</flux:label>
        <x-display-input :value="$productTypes[$practiceForm->productTypeId] ?? null" />
    </div>
    {{-- Product Subtype --}}
    <div class="flex flex-col gap-1.5">
        <flux:label>Tipo prodotto</flux:label>
        <x-display-input :value="$productSubtypes[$practiceForm->productSubtypeId] ?? null" />
    </div>
    {{-- Team Member --}}
    <div class="flex flex-col gap-1.5">
        <flux:label>Assegna a</flux:label>
        <x-display-input :value="$teamMembers[$practiceForm->userId] ?? null" />
    </div>
    {{-- Started At Date --}}
    <div class="flex flex-col gap-1.5">
        <flux:label>Data di inizio</flux:label>
        <x-display-input
            value="{{ \Carbon\Carbon::parse($practiceForm->firstInstallmentDate)->format('d/m/y') ?? '' }}" />
    </div>
    {{-- Paid At Date --}}
    <div class="flex flex-col gap-1.5">
        <flux:label>Data di fine</flux:label>
        <x-display-input
            value="{{ \Carbon\Carbon::parse($practiceForm->lastInstallmentDate)->format('d/m/y') ?? '' }}" />
    </div>
    {{-- Amount Disbursed --}}
    <div class="flex flex-col gap-1.5">
        <flux:label>Importo</flux:label>
        <x-display-input value="{{ $practiceForm->amountDisbursed }}" />
    </div>
    {{-- Installment --}}
    <div class="flex flex-col gap-1.5">
        <flux:label>Rate</flux:label>
        <x-display-input :value="$installments[$practiceForm->installmentId] ?? null" />
    </div>
    {{-- Rate Amount --}}
    <div class="flex flex-col gap-1.5">
        <flux:label>Rata mensile</flux:label>
        <x-display-input value="{{ $practiceForm->rateAmount }}" />
    </div>
    {{-- Taeg --}}
    <div class="flex flex-col gap-1.5">
        <flux:label>Taeg fisso</flux:label>
        <x-display-input value="{{ $practiceForm->taeg }}" />
    </div>
    {{-- Tan --}}
    <div class="flex flex-col gap-1.5">
        <flux:label>Tan fisso</flux:label>
        <x-display-input value="{{ $practiceForm->tan }}" />
    </div>
    {{-- Total Amount --}}
    <div class="flex flex-col gap-1.5">
        <flux:label>Totale dovuto</flux:label>
        <x-display-input value="{{ $practiceForm->totalAmount }}" />
    </div>
    {{-- Renewability Percentage --}}
    <div class="flex flex-col gap-1.5">
        <flux:label>Percentuale rinnovabilità *</flux:label>
        <x-display-input value="{{ $practiceForm->renewabilityPercentage }}" />
    </div>
    {{-- Percentage Alert --}}
    <div class="flex flex-col gap-1.5">
        <flux:label>Percentuale alert *</flux:label>
        <x-display-input value="{{ $practiceForm->percentageAlert }}" />
    </div>
    {{-- Renewed --}}
    <div class="flex flex-col gap-1.5">
        <flux:label>Rinnovo</flux:label>
        <x-display-input value="{{ \Carbon\Carbon::parse($practiceForm->renewabilityDate)->format('d/m/y') ?? '' }}" />
    </div>
    {{-- Customer Type --}}
    <div class="flex flex-col gap-1.5">
        <flux:label>Tipologia cliente</flux:label>
        <x-display-input :value="$customerTypes[$practiceForm->customerTypeId] ?? null" />
    </div>
    {{-- Insurance --}}
    <div class="flex flex-col gap-1.5">
        <flux:label>Assicurazione</flux:label>
        <x-display-input :value="$insurances[$practiceForm->insuranceId] ?? null" />
    </div>
    {{-- Financial Table --}}
    <div class="flex flex-col gap-1.5">
        <flux:label>Tabella provvigionale</flux:label>
        <x-display-input :value="$financialTables[$practiceForm->financialTableId] ?? null" />
    </div>
    {{-- Notes --}}
    <div class="flex flex-col gap-1.5 col-span-2">
        <flux:label>Note</flux:label>
        <x-display-textarea value="{{ $practiceForm->notes }}" />
    </div>
</div>
