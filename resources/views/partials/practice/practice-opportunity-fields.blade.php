<div class="grid grid-cols-2 gap-6 mt-10 pt-8 border-t border-zinc-200">
    <div class="col-span-2">
        <h3 class="text-base font-semibold">Dati pratica</h3>
        <p class="text-sm text-gray-custom-4 mt-1">
            Informazioni economiche e operative associate al lead.
        </p>
    </div>

    {{-- Ente erogante --}}
    <div class="flex flex-col gap-1.5">
        <flux:label>Ente erogante</flux:label>
        <flux:input size="sm" wire:model="opportunityForm.disbursingInstitution" />
        <flux:error name="opportunityForm.disbursingInstitution" />
    </div>

    {{-- Product Type --}}
    <div class="flex flex-col gap-1.5">
        <flux:label>Prodotto</flux:label>
        <x-dropdown-select
            size="sm"
            align="right"
            :selectable-items="$productTypes"
            :selected="$selectedProductTypeId"
            placeholder="Seleziona prodotto"
            setFunction="setOpportunityProductType"
            :has-error="$errors->has('opportunityForm.productTypeId')"
        />
        <flux:error name="opportunityForm.productTypeId" />
    </div>

    {{-- Product Subtype --}}
    <div class="flex flex-col gap-1.5">
        <flux:label>Tipo prodotto</flux:label>
        <x-dropdown-select
            size="sm"
            align="right"
            :selectable-items="$productSubtypes"
            :selected="$selectedProductSubtypeId"
            placeholder="Seleziona tipo prodotto"
            setFunction="setOpportunityProductSubtype"
            :has-error="$errors->has('opportunityForm.productSubtypeId')"
        />
        <flux:error name="opportunityForm.productSubtypeId" />
    </div>

    {{-- Rinnovo --}}
    <div class="flex flex-col gap-1.5">
        <flux:label>Rinnovo</flux:label>
        <x-dropdown-select
            size="sm"
            align="right"
            :selectable-items="['0' => 'No', '1' => 'Sì']"
            :selected="$opportunityForm->isRenewal ? '1' : '0'"
            placeholder="Seleziona rinnovo"
            setFunction="setOpportunityIsRenewal"
            :has-error="$errors->has('opportunityForm.isRenewal')"
        />
        <flux:error name="opportunityForm.isRenewal" />
    </div>

    {{-- Produzione --}}
    <div class="flex flex-col gap-1.5">
        <flux:label>Produzione</flux:label>
        <x-dropdown-select
            size="sm"
            align="right"
            :selectable-items="$productionTypes"
            :selected="$selectedProductionType"
            placeholder="Seleziona produzione"
            setFunction="setOpportunityProductionType"
            :has-error="$errors->has('opportunityForm.productionType')"
        />
        <flux:error name="opportunityForm.productionType" />
    </div>

    {{-- Installment --}}
    <div class="flex flex-col gap-1.5">
        <flux:label>Rate</flux:label>
        <x-dropdown-select
            size="sm"
            align="right"
            :selectable-items="$installments"
            :selected="$selectedInstallmentId"
            placeholder="Seleziona rate"
            setFunction="setOpportunityInstallment"
            :has-error="$errors->has('opportunityForm.installmentId')"
        />
        <flux:error name="opportunityForm.installmentId" />
    </div>

    {{-- First Installment Date --}}
    <div class="flex flex-col gap-1.5">
        <flux:label>Data di inizio</flux:label>
        <flux:input size="sm" type="date" wire:model="opportunityForm.firstInstallmentDate" />
        <flux:error name="opportunityForm.firstInstallmentDate" />
    </div>

    {{-- Last Installment Date --}}
    <div class="flex flex-col gap-1.5">
        <flux:label>Data di fine</flux:label>
        <flux:input size="sm" type="date" wire:model="opportunityForm.lastInstallmentDate" />
        <flux:error name="opportunityForm.lastInstallmentDate" />
    </div>

    {{-- Amount Disbursed --}}
    <div class="flex flex-col gap-1.5">
        <flux:label>Importo</flux:label>
        <flux:input size="sm" type="number" step="0.01" wire:model="opportunityForm.amountDisbursed" placeholder="€" />
        <flux:error name="opportunityForm.amountDisbursed" />
    </div>

    {{-- Rate Amount --}}
    <div class="flex flex-col gap-1.5">
        <flux:label>Rata mensile</flux:label>
        <flux:input size="sm" type="number" step="0.01" wire:model="opportunityForm.rateAmount" placeholder="€" />
        <flux:error name="opportunityForm.rateAmount" />
    </div>

    {{-- TAEG --}}
    <div class="flex flex-col gap-1.5">
        <flux:label>Taeg fisso</flux:label>
        <flux:input size="sm" type="number" step="0.01" wire:model="opportunityForm.taeg" placeholder="%" />
        <flux:error name="opportunityForm.taeg" />
    </div>

    {{-- TAN --}}
    <div class="flex flex-col gap-1.5">
        <flux:label>Tan fisso</flux:label>
        <flux:input size="sm" type="number" step="0.001" wire:model="opportunityForm.tan" placeholder="%" />
        <flux:error name="opportunityForm.tan" />
    </div>

    {{-- TEG --}}
    <div class="flex flex-col gap-1.5">
        <flux:label>Teg fisso</flux:label>
        <flux:input size="sm" type="number" step="0.01" wire:model="opportunityForm.teg" placeholder="%" />
        <flux:error name="opportunityForm.teg" />
    </div>

    {{-- Total Amount --}}
    <div class="flex flex-col gap-1.5">
        <flux:label>Totale dovuto</flux:label>
        <flux:input size="sm" type="number" step="0.01" wire:model="opportunityForm.totalAmount" placeholder="€" />
        <flux:error name="opportunityForm.totalAmount" />
    </div>

    {{-- Renewability Percentage --}}
    <div class="flex flex-col gap-1.5">
        <flux:label>Percentuale rinnovabilità</flux:label>
        <flux:input size="sm" type="number" step="0.01" wire:model="opportunityForm.renewabilityPercentage" placeholder="%" />
        <flux:error name="opportunityForm.renewabilityPercentage" />
    </div>

    {{-- Percentage Alert --}}
    <div class="flex flex-col gap-1.5">
        <flux:label>Percentuale alert</flux:label>
        <flux:input size="sm" type="number" step="0.01" wire:model="opportunityForm.percentageAlert" placeholder="%" />
        <flux:error name="opportunityForm.percentageAlert" />
    </div>

    {{-- Opportunity Customer Type --}}
    <div class="flex flex-col gap-1.5">
        <flux:label>Tipologia cliente</flux:label>
        <x-dropdown-select
            size="sm"
            align="right"
            :selectable-items="$customerTypes"
            :selected="$selectedOpportunityCustomerTypeId"
            placeholder="Seleziona tipologia cliente"
            setFunction="setOpportunityCustomerType"
            :has-error="$errors->has('opportunityForm.customerTypeId')"
        />
        <flux:error name="opportunityForm.customerTypeId" />
    </div>

    {{-- Insurance --}}
    <div class="flex flex-col gap-1.5">
        <flux:label>Assicurazione</flux:label>
        <x-dropdown-select
            size="sm"
            align="right"
            :selectable-items="$insurances"
            :selected="$selectedInsuranceId"
            placeholder="Seleziona assicurazione"
            setFunction="setOpportunityInsurance"
            :has-error="$errors->has('opportunityForm.insuranceId')"
        />
        <flux:error name="opportunityForm.insuranceId" />
    </div>

    {{-- Financial Table --}}
    <div class="flex flex-col gap-1.5">
        <flux:label>Tabella provvigionale</flux:label>
        <x-dropdown-select
            size="sm"
            align="right"
            :selectable-items="$financialTables"
            :selected="$selectedFinancialTableId"
            placeholder="Seleziona tabella provvigionale"
            setFunction="setOpportunityFinancialTable"
            :has-error="$errors->has('opportunityForm.financialTableId')"
        />
        <flux:error name="opportunityForm.financialTableId" />
    </div>

    {{-- Istituto finanziario --}}
    <div class="flex flex-col gap-1.5">
        <flux:label>Istituto finanziario</flux:label>
        <flux:input size="sm" wire:model="opportunityForm.financialInstitution" />
        <flux:error name="opportunityForm.financialInstitution" />
    </div>

    {{-- Finanziaria estinta --}}
    <div class="flex flex-col gap-1.5">
        <flux:label>Finanziaria estinta</flux:label>
        <flux:input size="sm" wire:model="opportunityForm.previousFinance" />
        <flux:error name="opportunityForm.previousFinance" />
    </div>

    {{-- Notes --}}
    <div class="flex flex-col gap-1.5 col-span-2">
        <flux:textarea label="Note pratica" resize="none" wire:model="opportunityForm.notes" />
        <flux:error name="opportunityForm.notes" />
    </div>
</div>
