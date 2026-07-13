@php
    /*
     * Nelle pagine pratiche il prodotto può essere già determinato
     * dal contesto della pagina tramite $type.
     *
     * Nei lead, invece, il filtro prodotto sarà sempre visibile.
     */
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
<div class="flex flex-col gap-1.5">
    <flux:label>Data di inizio</flux:label>

    <div class="grid grid-cols-2 gap-4">
        <div>
            <flux:input
                type="date"
                size="sm"
                wire:model="tempFirstInstallmentDateMin"
            />

            <flux:error name="tempFirstInstallmentDateMin" />
        </div>

        <div>
            <flux:input
                type="date"
                size="sm"
                wire:model="tempFirstInstallmentDateMax"
            />

            <flux:error name="tempFirstInstallmentDateMax" />
        </div>
    </div>
</div>

{{-- Last Installment Date --}}
<div class="flex flex-col gap-1.5">
    <flux:label>Data di fine</flux:label>

    <div class="grid grid-cols-2 gap-4">
        <div>
            <flux:input
                type="date"
                size="sm"
                wire:model="tempLastInstallmentDateMin"
            />

            <flux:error name="tempLastInstallmentDateMin" />
        </div>

        <div>
            <flux:input
                type="date"
                size="sm"
                wire:model="tempLastInstallmentDateMax"
            />

            <flux:error name="tempLastInstallmentDateMax" />
        </div>
    </div>
</div>


{{-- Amount Disbursed --}}
<div class="flex flex-col gap-1.5">
    <flux:label>Importo</flux:label>

    <div class="grid grid-cols-2 gap-4">
        <div>
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
        </div>

        <div>
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
        </div>
    </div>
</div>

{{-- Total Amount --}}
<div class="flex flex-col gap-1.5">
    <flux:label>Totale dovuto</flux:label>

    <div class="grid grid-cols-2 gap-4">
        <div>
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
        </div>

        <div>
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
        </div>
    </div>
</div>

{{-- Rate Amount --}}
<div class="flex flex-col gap-1.5">
    <flux:label>Rata mensile</flux:label>

    <div class="grid grid-cols-2 gap-4">
        <div>
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
        </div>

        <div>
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
        </div>
    </div>
</div>

{{-- TAN --}}
<div class="flex flex-col gap-1.5">
    <flux:label>Tan</flux:label>

    <div class="grid grid-cols-2 gap-4">
        <div>
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
        </div>

        <div>
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
        </div>
    </div>
</div>

{{-- TAEG --}}
<div class="flex flex-col gap-1.5">
    <flux:label>Taeg</flux:label>

    <div class="grid grid-cols-2 gap-4">
        <div>
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
        </div>

        <div>
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
        </div>
    </div>
</div>
