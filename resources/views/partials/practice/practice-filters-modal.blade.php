<x-modals.filter-modal header="Filtra pratiche" maxWidth="5xl">
    <div class="grid grid-cols-3 gap-4">
        {{-- User --}}
        <div class="flex flex-col gap-1.5">
            <flux:label>Collaboratore</flux:label>
            <x-dropdown-select size="sm" :selectable-items="$teamMembers" :selected="$tempSelectedTeamMemberForFilter" searchable search="teamMemberSearch"
                placeholder='Seleziona collaboratore' setFunction="setTeamMember" />
        </div>
        {{-- Practice Status --}}
        <div class="flex flex-col gap-1.5">
            <flux:label>Stato pratica</flux:label>
            <x-dropdown-select size="sm" :selectable-items="$practiceStatuses" :selected="$tempSelectedPracticeStatusForFilter" placeholder='Seleziona stato'
                setFunction="setPracticeStatusForFilter" />
        </div>
        {{-- Product Type --}}
        @if ($type === null)
            <div class="flex flex-col gap-1.5">
                <flux:label>Prodotto</flux:label>
                <x-dropdown-select size="sm" :selectable-items="$productTypes" :selected="$tempSelectedProductTypeForFilter" placeholder='Seleziona prodotto'
                    setFunction="setProductType" />
            </div>
        @endif
        {{-- Product SubType --}}
        <div class="flex flex-col gap-1.5">
            <flux:label>Tipo prodotto</flux:label>
            <x-dropdown-select size="sm" :selectable-items="$productSubtypes" :selected="$tempSelectedProductSubtypeForFilter" placeholder='Seleziona tipo prodotto'
                setFunction="setProductSubtype" />
        </div>
        {{-- Customer --}}
        <div class="flex flex-col gap-1.5">
            <flux:label>Cliente</flux:label>
            <x-dropdown-select size="sm" :selectable-items="$customers" :selected="$tempSelectedCustomerForFilter" searchable search="customerSearch"
                placeholder='Seleziona cliente' setFunction="setCustomer" />
        </div>
        {{-- Customer Type --}}
        <div class="flex flex-col gap-1.5">
            <flux:label>Tipologia cliente</flux:label>
            <x-dropdown-select size="sm" :selectable-items="$customerTypes" :selected="$tempSelectedCustomerTypeForFilter"
                placeholder='Seleziona tipologia cliente' setFunction="setCustomerType" />
        </div>
        {{-- Insurance --}}
        <div class="flex flex-col gap-1.5">
            <flux:label>Assicurazione</flux:label>
            <x-dropdown-select size="sm" :selectable-items="$insurances" :selected="$tempSelectedInsuranceForFilter"
                placeholder='Seleziona assicurazione' setFunction="setInsurance" />
        </div>
        {{-- Installment --}}
        <div class="flex flex-col gap-1.5">
            <flux:label>Rate</flux:label>
            <x-dropdown-select size="sm" :selectable-items="$installments" :selected="$tempSelectedInstallmentForFilter" placeholder='Seleziona rate'
                setFunction="setInstallment" />
        </div>
        {{-- First Installment Date --}}
        <div class="flex flex-col gap-1.5">
            <flux:label>Data di inizio (range)</flux:label>
            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-1">
                    <flux:input type="date" size="sm" wire:model='tempFirstInstallmentDateMin' />
                    <flux:error name="tempFirstInstallmentDateMin" />
                </div>
                <div class="col-span-1">
                    <flux:input type="date" size="sm" wire:model='tempFirstInstallmentDateMax' />
                    <flux:error name="tempFirstInstallmentDateMax" />
                </div>
            </div>
        </div>
        {{-- Last Installment Date --}}
        <div class="flex flex-col gap-1.5">
            <flux:label>Data di fine (range)</flux:label>
            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-1">
                    <flux:input type="date" size="sm" wire:model='tempLastInstallmentDateMin' />
                    <flux:error name="tempLastInstallmentDateMin" />
                </div>
                <div class="col-span-1">
                    <flux:input type="date" size="sm" wire:model='tempLastInstallmentDateMax' />
                    <flux:error name="tempLastInstallmentDateMax" />
                </div>
            </div>
        </div>
        {{-- Renewability Date --}}
        <div class="flex flex-col gap-1.5">
            <flux:label>Data rinnovabilità</flux:label>
            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-1">
                    <flux:input type="date" size="sm" wire:model='tempRenewabilityDateMin' />
                    <flux:error name="tempRenewabilityDateMin" />
                </div>

                <div class="col-span-1">
                    <flux:input type="date" size="sm" wire:model='tempRenewabilityDateMax' />
                    <flux:error name="tempRenewabilityDateMax" />
                </div>
            </div>
        </div>
        {{-- Amount --}}
        <div class="flex flex-col gap-1.5">
            <flux:label>Importo (range)</flux:label>
            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-1">
                    <x-forms.input-with-symbol type="number" min="0.00" step=".01" size="sm"
                        wire:model="tempAmountDisbursedMin" symbol="€" />
                    <flux:error name="tempAmountDisbursedMin" />
                </div>

                <div class="col-span-1">
                    <x-forms.input-with-symbol type="number" min="0.00" step=".01" size="sm"
                        wire:model="tempAmountDisbursedMax" symbol="€" />
                    <flux:error name="tempAmountDisbursedMax" />
                </div>
            </div>
        </div>
        {{-- Total Amount --}}
        <div class="flex flex-col gap-1.5">
            <flux:label>Totale dovuto</flux:label>
            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-1">
                    <x-forms.input-with-symbol type="number" min="0.00" step=".01" size="sm"
                        wire:model="tempTotalAmountMin" symbol="€" />
                    <flux:error name="tempTotalAmountMin" />
                </div>

                <div class="col-span-1">
                    <x-forms.input-with-symbol type="number" min="0.00" step=".01" size="sm"
                        wire:model="tempTotalAmountMax" symbol="€" />
                    <flux:error name="tempTotalAmountMax" />
                </div>
            </div>
        </div>
        {{-- Rate Amount --}}
        <div class="flex flex-col gap-1.5">
            <flux:label>Rata mensile</flux:label>
            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-1">
                    <x-forms.input-with-symbol type="number" min="0.00" step=".01" size="sm"
                        wire:model="tempRateAmountMin" symbol="€" />
                    <flux:error name="tempRateAmountMin" />
                </div>

                <div class="col-span-1">
                    <x-forms.input-with-symbol type="number" min="0.00" step=".01" size="sm"
                        wire:model="tempRateAmountMax" symbol="€" />
                    <flux:error name="tempRateAmountMax" />
                </div>
            </div>
        </div>
        {{-- Tan --}}
        <div class="flex flex-col gap-1.5">
            <flux:label>Tan</flux:label>
            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-1">
                    <x-forms.input-with-symbol type="number" min="0.00" step=".01" size="sm"
                        wire:model="tempTanMin" symbol="%" />
                    <flux:error name="tempTanMin" />
                </div>

                <div class="col-span-1">
                    <x-forms.input-with-symbol type="number" min="0.00" step=".01" size="sm"
                        wire:model="tempTanMax" symbol="%" />
                    <flux:error name="tempTanMax" />
                </div>
            </div>
        </div>
        {{-- Taeg --}}
        <div class="flex flex-col gap-1.5">
            <flux:label>Taeg</flux:label>
            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-1">
                    <x-forms.input-with-symbol type="number" min="0.00" step=".01" size="sm"
                        wire:model="tempTaegMin" symbol="%" />
                    <flux:error name="tempTaegMin" />
                </div>

                <div class="col-span-1">
                    <x-forms.input-with-symbol type="number" min="0.00" step=".01" size="sm"
                        wire:model="tempTaegMax" symbol="%" />
                    <flux:error name="tempTaegMax" />
                </div>
            </div>
        </div>
    </div>
</x-modals.filter-modal>
