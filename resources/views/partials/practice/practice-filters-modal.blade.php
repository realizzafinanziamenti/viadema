<x-modals.filter-modal header="Filtra pratiche" maxWidth="5xl">
    <div class="grid grid-cols-3 gap-4">
        {{-- User --}}
        <div class="flex flex-col gap-1.5">
            <flux:label>Collaboratore</flux:label>
            <x-dropdown-select size="sm" :selectable-items="$teamMembers" :selected="$selectedTeamMemberForFilter" searchable search="teamMemberSearch"
                placeholder='Seleziona collaboratore' setFunction="setTeamMember" />
        </div>
        {{-- Practice Status --}}
        <div class="flex flex-col gap-1.5">
            <flux:label>Stato pratica</flux:label>
            <x-dropdown-select size="sm" :selectable-items="$practiceStatuses" :selected="$selectedPracticeStatus" placeholder='Seleziona stato'
                setFunction="setPracticeStatus" />
        </div>
        {{-- Product Type --}}
        @if ($type === null)
            <div class="flex flex-col gap-1.5">
                <flux:label>Prodotto</flux:label>
                <x-dropdown-select size="sm" :selectable-items="$productTypes" :selected="$selectedProductTypeForFilter" placeholder='Seleziona prodotto'
                    setFunction="setProductType" />
            </div>
        @endif
        {{-- Product SubType --}}
        <div class="flex flex-col gap-1.5">
            <flux:label>Tipo prodotto</flux:label>
            <x-dropdown-select size="sm" :selectable-items="$productSubtypes" :selected="$selectedProductSubtypeForFilter" placeholder='Seleziona tipo prodotto'
                setFunction="setProductSubtype" />
        </div>
        {{-- Customer --}}
        <div class="flex flex-col gap-1.5">
            <flux:label>Cliente</flux:label>
            <x-dropdown-select size="sm" :selectable-items="$customers" :selected="$selectedCustomerForFilter" searchable search="teamMemberSearch"
                placeholder='Seleziona cliente' setFunction="setCustomer" />
        </div>
        {{-- Customer Type --}}
        <div class="flex flex-col gap-1.5">
            <flux:label>Tipologia cliente</flux:label>
            <x-dropdown-select size="sm" :selectable-items="$customerTypes" :selected="$selectedCustomerTypeForFilter"
                placeholder='Seleziona tipologia cliente' setFunction="setCustomerType" />
        </div>
        {{-- Insurance --}}
        <div class="flex flex-col gap-1.5">
            <flux:label>Assicurazione</flux:label>
            <x-dropdown-select size="sm" :selectable-items="$insurances" :selected="$selectedInsuranceForFilter"
                placeholder='Seleziona assicurazione' setFunction="setInsurance" />
        </div>
        {{-- Installment --}}
        <div class="flex flex-col gap-1.5">
            <flux:label>Rate</flux:label>
            <x-dropdown-select size="sm" :selectable-items="$installments" :selected="$selectedInstallmentForFilter" placeholder='Seleziona rate'
                setFunction="setInstallment" emptySelectableItems="Seleziona prima prodotto" />
        </div>
        {{-- First Installment Date --}}
        <div class="flex flex-col gap-1.5">
            <flux:label>Data di inizio (range)</flux:label>
            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-1">
                    <flux:input type="date" size="sm" wire:model='startDate' />
                    <flux:error name="startDate" />
                </div>
                <div class="col-span-1">
                    <flux:input type="date" size="sm" wire:model='endDate' />
                    <flux:error name="startDate" />
                </div>
            </div>
        </div>
        {{-- Last Installment Date --}}
        <div class="flex flex-col gap-1.5">
            <flux:label>Data di fine (range)</flux:label>
            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-1">
                    <flux:input type="date" size="sm" wire:model='startDate' />
                    <flux:error name="startDate" />
                </div>
                <div class="col-span-1">
                    <flux:input type="date" size="sm" wire:model='endDate' />
                    <flux:error name="endDate" />
                </div>
            </div>
        </div>
        {{-- Renewability Date --}}
        <div class="flex flex-col gap-1.5">
            <flux:label>Data rinnovabilità</flux:label>
            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-1">
                    <flux:input type="date" size="sm" wire:model='startDate' />
                    <flux:error name="startDate" />
                </div>

                <div class="col-span-1">
                    <flux:input type="date" size="sm" wire:model='endDate' />
                    <flux:error name="endDate" />
                </div>
            </div>
        </div>
        {{-- Amount --}}
        <div class="flex flex-col gap-1.5">
            <flux:label>Importo (range)</flux:label>
            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-1">
                    <x-forms.input-with-symbol type="number" min="0.00" step=".01" size="sm"
                        wire:model="practiceForm.amountDisbursed" symbol="€" />
                    <flux:error name="startDate" />
                </div>

                <div class="col-span-1">
                    <x-forms.input-with-symbol type="number" min="0.00" step=".01" size="sm"
                        wire:model="practiceForm.amountDisbursed" symbol="€" />
                    <flux:error name="endDate" />
                </div>
            </div>
        </div>
        {{-- Total Amount --}}
        <div class="flex flex-col gap-1.5">
            <flux:label>Totale dovuto</flux:label>
            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-1">
                    <x-forms.input-with-symbol type="number" min="0.00" step=".01" size="sm"
                        wire:model="practiceForm.amountDisbursed" symbol="€" />
                    <flux:error name="startDate" />
                </div>

                <div class="col-span-1">
                    <x-forms.input-with-symbol type="number" min="0.00" step=".01" size="sm"
                        wire:model="practiceForm.amountDisbursed" symbol="€" />
                    <flux:error name="endDate" />
                </div>
            </div>
        </div>
        {{-- Rate Amount --}}
        <div class="flex flex-col gap-1.5">
            <flux:label>Rata mensile</flux:label>
            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-1">
                    <x-forms.input-with-symbol type="number" min="0.00" step=".01" size="sm"
                        wire:model="practiceForm.amountDisbursed" symbol="€" />
                    <flux:error name="startDate" />
                </div>

                <div class="col-span-1">
                    <x-forms.input-with-symbol type="number" min="0.00" step=".01" size="sm"
                        wire:model="practiceForm.amountDisbursed" symbol="€" />
                    <flux:error name="endDate" />
                </div>
            </div>
        </div>
        {{-- Tan --}}
        <div class="flex flex-col gap-1.5">
            <flux:label>Tan</flux:label>
            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-1">
                    <x-forms.input-with-symbol type="number" min="0.00" step=".01" size="sm"
                        wire:model="practiceForm.taeg" symbol="%" />
                    <flux:error name="startDate" />
                </div>

                <div class="col-span-1">
                    <x-forms.input-with-symbol type="number" min="0.00" step=".01" size="sm"
                        wire:model="practiceForm.taeg" symbol="%" />
                    <flux:error name="endDate" />
                </div>
            </div>
        </div>
        {{-- Taeg --}}
        <div class="flex flex-col gap-1.5">
            <flux:label>Taeg</flux:label>
            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-1">
                    <x-forms.input-with-symbol type="number" min="0.00" step=".01" size="sm"
                        wire:model="practiceForm.taeg" symbol="%" />
                    <flux:error name="startDate" />
                </div>

                <div class="col-span-1">
                    <x-forms.input-with-symbol type="number" min="0.00" step=".01" size="sm"
                        wire:model="practiceForm.taeg" symbol="%" />
                    <flux:error name="endDate" />
                </div>
            </div>
        </div>
    </div>
</x-modals.filter-modal>
