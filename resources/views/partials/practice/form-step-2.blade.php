<div class="grid grid-cols-2 gap-6">
    {{-- Disbursing Institution --}}
    <div class="flex flex-col gap-1.5">
        <flux:label>Ente erogante</flux:label>
        <div class="flex flex-col gap-0.5">
            <flux:input size="sm" wire:model='practiceForm.disbursingInstitution' />
            <flux:error name="practiceForm.disbursingInstitution" />
        </div>
    </div>

    {{-- Product Type --}}
    <div class="flex flex-col gap-1.5">
        <flux:label>Prodotto </flux:label>
        <div class="flex flex-col gap-0.5">
            <x-dropdown-select size="sm" :selectable-items="$productTypes" :selected="$practiceForm->productTypeId" placeholder='Seleziona prodotto'
                setFunction="setProductType" :has-error="$errors->has('practiceForm.productTypeId')" />

            <flux:error name="practiceForm.productTypeId" />
        </div>
    </div>

    {{-- Product Subtype --}}
    <div class="flex flex-col gap-1.5">
        <flux:label>Tipo prodotto</flux:label>
        <div class="flex flex-col gap-0.5">
            <x-dropdown-select size="sm" :selectable-items="$productSubtypes" :selected="$practiceForm->productSubtypeId" placeholder='Seleziona tipo prodotto'
                setFunction="setProductSubtype" :has-error="$errors->has('practiceForm.productSubtypeId')" />

            <flux:error name="practiceForm.productSubtypeId" />
        </div>
    </div>

    {{-- Is Renewal --}}
    <div class="flex flex-col gap-1.5">
        <flux:label>Rinnovo </flux:label>
        <div class="flex flex-col gap-0.5">
            <x-dropdown-bool-select size="sm" :selected="$practiceForm->isRenewal" yesAction="setIsRenewal('1')"
                noAction="setIsRenewal('0')" :has-error="$errors->has('practiceForm.isRenewal')" border />
            <flux:error name="practiceForm.isRenewal" />
        </div>
    </div>

    {{-- Production type --}}
    <div class="flex flex-col gap-1.5">
        <flux:label>Produzione </flux:label>
        <div class="flex flex-col gap-0.5">
            <x-dropdown-select size="sm" :selectable-items="$productionTypes" :selected="$practiceForm->productionType" placeholder='Seleziona produzione'
                setFunction="setProductionType" :has-error="$errors->has('practiceForm.productionType')" />

            <flux:error name="practiceForm.productionType" />
        </div>
    </div>

    {{-- Team Member --}}
    @if (auth()->user()->can('assign practice to user'))
        <div class="flex flex-col gap-1.5">
            <flux:label>Assegna a </flux:label>
            <div class="flex flex-col gap-0.5">
                <x-dropdown-select size="sm" :selectable-items="$teamMembers" :selected="$practiceForm->userId" searchable
                    search="teamMemberSearch" placeholder='Seleziona collaboratore' setFunction="setPracticeTeamMember"
                    :has-error="$errors->has('practiceForm.userId')" />

                <flux:error name="practiceForm.userId" />
            </div>
        </div>
    @endif

    {{-- First Installment Date --}}
    <div class="flex flex-col gap-1.5">
        <flux:label>Data di inizio *</flux:label>
        <div class="flex flex-col gap-0.5">
            <flux:input type="date" size="sm" wire:model.live='practiceForm.firstInstallmentDate' />
            <flux:error name="practiceForm.firstInstallmentDate" />
        </div>
    </div>

    {{-- Last Installment Date --}}
    <div class="flex flex-col gap-1.5">
        <flux:label>Data di fine</flux:label>
        <div class="flex flex-col gap-0.5">
            <flux:input type="date" size="sm" readonly wire:model='practiceForm.lastInstallmentDate' />
            <flux:error name="practiceForm.lastInstallmentDate" />
        </div>
    </div>

    {{-- Amount Disbursed --}}
    <div class="flex flex-col gap-1.5">
        <flux:label>Importo </flux:label>
        <div class="flex flex-col gap-0.5">
            <x-forms.input-with-symbol type="number" min="0.00" max="99999999.99" step=".01" size="sm"
                wire:model="practiceForm.amountDisbursed" symbol="€" />
            <flux:error name="practiceForm.amountDisbursed" />
        </div>
    </div>

    {{-- Installment --}}
    <div class="flex flex-col gap-1.5">
        <flux:label>Rate </flux:label>
        <div class="flex flex-col gap-0.5">
            <x-dropdown-select size="sm" :selectable-items="$installments" :selected="$practiceForm->installmentId" placeholder='Seleziona rate'
                setFunction="setInstallment" :has-error="$errors->has('practiceForm.installmentId')" />
            <flux:error name="practiceForm.installmentId" />
        </div>
    </div>

    {{-- Rate Amount --}}
    <div class="flex flex-col gap-1.5">
        <flux:label>Rata mensile </flux:label>
        <div class="flex flex-col gap-0.5">
            <x-forms.input-with-symbol type="number" min="0.00" max="99999999.99" step=".01" size="sm"
                wire:model="practiceForm.rateAmount" symbol="€" />
            <flux:error name="practiceForm.rateAmount" />
        </div>
    </div>

    {{-- Taeg --}}
    <div class="flex flex-col gap-1.5">
        <flux:label>Taeg fisso </flux:label>
        <div class="flex flex-col gap-0.5">
            <x-forms.input-with-symbol type="number" min="0.00" max="10000.00" step=".01" size="sm"
                wire:model="practiceForm.taeg" symbol="%" />
            <flux:error name="practiceForm.taeg" />
        </div>
    </div>

    {{-- Tan --}}
    <div class="flex flex-col gap-1.5">
        <flux:label>Tan fisso </flux:label>
        <div class="flex flex-col gap-0.5">
            <x-forms.input-with-symbol type="number" min="0.00" max="10000.00" step=".01" size="sm"
                wire:model="practiceForm.tan" symbol="%" />
            <flux:error name="practiceForm.tan" />
        </div>
    </div>

    {{-- Total Amount --}}
    <div class="flex flex-col gap-1.5">
        <flux:label>Totale dovuto</flux:label>
        <div class="flex flex-col gap-0.5">
            <x-forms.input-with-symbol type="number" min="0.00" max="99999999.99" step=".01" size="sm"
                wire:model="practiceForm.totalAmount" symbol="€" />
            <flux:error name="practiceForm.totalAmount" />
        </div>
    </div>

    {{-- Renewability Percentage --}}
    <div class="flex flex-col gap-1.5">
        <flux:label>Percentuale rinnovabilità </flux:label>
        <div class="flex flex-col gap-0.5">
            <x-forms.input-with-symbol type="number" min="0.00" max="100.00" step=".01" size="sm"
                wire:model.live.debounce.250ms="practiceForm.renewabilityPercentage" symbol="%" />
            <flux:error name="practiceForm.renewabilityPercentage" />
        </div>
    </div>

    {{-- Percentage Alert --}}
    <div class="flex flex-col gap-1.5">
        <flux:label>Percentuale alert </flux:label>
        <div class="flex flex-col gap-0.5">
            <x-forms.input-with-symbol type="number" min="0.00" max="100.00" step=".01" size="sm"
                wire:model="practiceForm.percentageAlert" symbol="%" />
            <flux:error name="practiceForm.percentageAlert" />
        </div>
    </div>

    {{-- Renewed --}}
    <div class="flex flex-col gap-1.5">
        <flux:label>Data rinnovabilità</flux:label>
        <div class="flex flex-col gap-0.5">
            <flux:input type="date" size="sm" readonly wire:model='practiceForm.renewabilityDate' />
            <flux:error name="practiceForm.renewabilityDate" />
        </div>
    </div>

    {{-- Customer Type --}}
    <div class="flex flex-col gap-1.5">
        <flux:label>Tipologia cliente</flux:label>
        <div class="flex flex-col gap-0.5">
            <x-dropdown-select size="sm" :selectable-items="$customerTypes" :selected="$practiceForm->customerTypeId"
                placeholder='Seleziona tipologia cliente' setFunction="setCustomerType" :has-error="$errors->has('practiceForm.customerTypeId')" />

            <flux:error name="practiceForm.customerTypeId" />
        </div>
    </div>

    {{-- Insurance --}}
    <div class="flex flex-col gap-1.5">
        <flux:label>Assicurazione</flux:label>
        <div class="flex flex-col gap-0.5">
            <x-dropdown-select size="sm" :selectable-items="$insurances" :selected="$practiceForm->insuranceId"
                placeholder='Seleziona assicurazione' setFunction="setInsurance" :has-error="$errors->has('practiceForm.insuranceId')" />

            <flux:error name="practiceForm.insuranceId" />
        </div>
    </div>

    {{-- Financial Table --}}
    <div class="flex flex-col gap-1.5">
        <flux:label>Tabella provvigionale</flux:label>
        <div class="flex flex-col gap-0.5">
            <x-dropdown-select size="sm" :selectable-items="$financialTables" :selected="$practiceForm->financialTableId"
                placeholder='Seleziona tabella provvigionale' setFunction="setFinancialTable" :has-error="$errors->has('practiceForm.financialTableId')" />

            <flux:error name="practiceForm.financialTableId" />
        </div>
    </div>

    {{-- Financial Institution --}}
    <div class="flex flex-col gap-1.5">
        <flux:label>Istituto finanziario</flux:label>
        <div class="flex flex-col gap-0.5">
            <flux:input size="sm" wire:model='practiceForm.financialInstitution' />
            <flux:error name="practiceForm.financialInstitution" />
        </div>
    </div>

    {{-- Notes --}}
    <div class="flex flex-col gap-1.5 col-span-2">
        <flux:textarea label="Note" resize="none" wire:model='practiceForm.notes' />
        <flux:error name="practiceForm.notes" />
    </div>
</div>

{{-- Next Step Buttons --}}
<div class="flex items-center justify-end gap-x-3 mt-18">
    <flux:button variant="primary" type="button" size="sm" wire:click="firstPrevStep"
        class="px-10 bg-gray-custom-2 border-gray-custom-2 text-gray-custom-5 hover:bg-gray-custom-3-hover hover:border-gray-custom-3-hover hover:text-white">
        Annulla
    </flux:button>

    <flux:button variant="primary" type="button" size="sm" wire:click='secondNextStep'
        class="px-10 bg-azure-custom border-azure-custom hover:bg-azure-custom-hover hover:border-azure-custom-hover">
        Continua
    </flux:button>
</div>
