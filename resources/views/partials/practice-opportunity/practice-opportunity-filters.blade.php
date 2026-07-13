@php
    $showProductType = $showProductType ?? true;
@endphp

{{-- Product Type --}}
@if ($showProductType)
    <div class="flex flex-col gap-1.5">
        <flux:label>Prodotto</flux:label>

        <x-dropdown-select
            size="sm"
            :selectable-items="$productTypes"
            :selected="$tempSelectedProductTypeForFilter"
            placeholder="Seleziona prodotto"
            setFunction="setProductType"
            :has-error="$errors->has('tempSelectedProductTypeForFilter')"
        />

        <flux:error name="tempSelectedProductTypeForFilter" />
    </div>
@endif

{{-- Product Subtype --}}
<div class="flex flex-col gap-1.5">
    <flux:label>Tipo prodotto</flux:label>

    <x-dropdown-select
        size="sm"
        :selectable-items="$productSubtypes"
        :selected="$tempSelectedProductSubtypeForFilter"
        placeholder="Seleziona tipo prodotto"
        setFunction="setProductSubtype"
        :has-error="$errors->has('tempSelectedProductSubtypeForFilter')"
    />

    <flux:error name="tempSelectedProductSubtypeForFilter" />
</div>

{{-- Insurance --}}
<div class="flex flex-col gap-1.5">
    <flux:label>Assicurazione</flux:label>

    <x-dropdown-select
        size="sm"
        :selectable-items="$insurances"
        :selected="$tempSelectedInsuranceForFilter"
        placeholder="Seleziona assicurazione"
        setFunction="setInsurance"
        :has-error="$errors->has('tempSelectedInsuranceForFilter')"
    />

    <flux:error name="tempSelectedInsuranceForFilter" />
</div>

{{-- Installment --}}
<div class="flex flex-col gap-1.5">
    <flux:label>Rate</flux:label>

    <x-dropdown-select
        size="sm"
        :selectable-items="$installments"
        :selected="$tempSelectedInstallmentForFilter"
        placeholder="Seleziona rate"
        setFunction="setInstallment"
        :has-error="$errors->has('tempSelectedInstallmentForFilter')"
    />

    <flux:error name="tempSelectedInstallmentForFilter" />
</div>

{{-- First Installment Date --}}
<x-filters.range label="Data di inizio">
    <x-slot:from>
        <flux:input
            type="date"
            size="sm"
            wire:model="tempFirstInstallmentDateMin"
        />

        <flux:error name="tempFirstInstallmentDateMin" />
    </x-slot:from>

    <x-slot:to>
        <flux:input
            type="date"
            size="sm"
            wire:model="tempFirstInstallmentDateMax"
        />

        <flux:error name="tempFirstInstallmentDateMax" />
    </x-slot:to>
</x-filters.range>

{{-- Last Installment Date --}}
<x-filters.range label="Data di fine">
    <x-slot:from>
        <flux:input
            type="date"
            size="sm"
            wire:model="tempLastInstallmentDateMin"
        />

        <flux:error name="tempLastInstallmentDateMin" />
    </x-slot:from>

    <x-slot:to>
        <flux:input
            type="date"
            size="sm"
            wire:model="tempLastInstallmentDateMax"
        />

        <flux:error name="tempLastInstallmentDateMax" />
    </x-slot:to>
</x-filters.range>

{{-- Amount Disbursed --}}
<x-filters.range label="Importo">
    <x-slot:from>
        <x-forms.input-with-symbol
            type="number"
            min="0.00"
            max="99999999.99"
            step=".01"
            size="sm"
            wire:model="tempAmountDisbursedMin"
            symbol="€"
        />

        <flux:error name="tempAmountDisbursedMin" />
    </x-slot:from>

    <x-slot:to>
        <x-forms.input-with-symbol
            type="number"
            min="0.00"
            max="99999999.99"
            step=".01"
            size="sm"
            wire:model="tempAmountDisbursedMax"
            symbol="€"
        />

        <flux:error name="tempAmountDisbursedMax" />
    </x-slot:to>
</x-filters.range>

{{-- Total Amount --}}
<x-filters.range label="Totale dovuto">
    <x-slot:from>
        <x-forms.input-with-symbol
            type="number"
            min="0.00"
            max="99999999.99"
            step=".01"
            size="sm"
            wire:model="tempTotalAmountMin"
            symbol="€"
        />

        <flux:error name="tempTotalAmountMin" />
    </x-slot:from>

    <x-slot:to>
        <x-forms.input-with-symbol
            type="number"
            min="0.00"
            max="99999999.99"
            step=".01"
            size="sm"
            wire:model="tempTotalAmountMax"
            symbol="€"
        />

        <flux:error name="tempTotalAmountMax" />
    </x-slot:to>
</x-filters.range>

{{-- Rate Amount --}}
<x-filters.range label="Rata mensile">
    <x-slot:from>
        <x-forms.input-with-symbol
            type="number"
            min="0.00"
            max="99999999.99"
            step=".01"
            size="sm"
            wire:model="tempRateAmountMin"
            symbol="€"
        />

        <flux:error name="tempRateAmountMin" />
    </x-slot:from>

    <x-slot:to>
        <x-forms.input-with-symbol
            type="number"
            min="0.00"
            max="99999999.99"
            step=".01"
            size="sm"
            wire:model="tempRateAmountMax"
            symbol="€"
        />

        <flux:error name="tempRateAmountMax" />
    </x-slot:to>
</x-filters.range>

{{-- TAN --}}
<x-filters.range label="Tan">
    <x-slot:from>
        <x-forms.input-with-symbol
            type="number"
            min="0.00"
            max="10000.00"
            step=".01"
            size="sm"
            wire:model="tempTanMin"
            symbol="%"
        />

        <flux:error name="tempTanMin" />
    </x-slot:from>

    <x-slot:to>
        <x-forms.input-with-symbol
            type="number"
            min="0.00"
            max="10000.00"
            step=".01"
            size="sm"
            wire:model="tempTanMax"
            symbol="%"
        />

        <flux:error name="tempTanMax" />
    </x-slot:to>
</x-filters.range>

{{-- TAEG --}}
<x-filters.range label="Taeg">
    <x-slot:from>
        <x-forms.input-with-symbol
            type="number"
            min="0.00"
            max="10000.00"
            step=".01"
            size="sm"
            wire:model="tempTaegMin"
            symbol="%"
        />

        <flux:error name="tempTaegMin" />
    </x-slot:from>

    <x-slot:to>
        <x-forms.input-with-symbol
            type="number"
            min="0.00"
            max="10000.00"
            step=".01"
            size="sm"
            wire:model="tempTaegMax"
            symbol="%"
        />

        <flux:error name="tempTaegMax" />
    </x-slot:to>
</x-filters.range>
