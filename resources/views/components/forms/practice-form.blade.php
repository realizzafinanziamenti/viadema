@props([
    'submitFunction' => 'savePractice',
    'form' => 'practiceForm',
    'practiceForm',
    'submitButtonLabel' => 'Continua',
    'customers' => [],
    'selectedCustomer' => null,
    'selectedCustomerId' => null,
    'customerSearch' => 'customerSearch',
    'productTypes' => [],
    'selectedProductTypeId' => null,
    'productSubtypes' => [],
    'selectedProductSubtypeId' => null,
    'teamMembers' => [],
    'selectedPracticeUserId' => null,
    'teamMemberSearch' => 'teamMemberSearch',
    'installments' => [],
    'selectedInstallmentId' => null,
    'customerTypes' => [],
    'selectedCustomerTypeId' => null,
    'insurances' => [],
    'selectedInsuranceId' => null,
    'financialTables' => [],
    'selectedFinancialTableId' => null,
    'step' => 1,
])

<form wire:submit.prevent='{{ $submitFunction }}' class="w-xl mx-auto mt-10 mb-5">

    {{-- Toggle Buttons --}}
    <x-forms.step-label-container class="mx-auto w-3/4 mb-6">
        <x-forms.step-label label="Dati Cliente" :step="1" :currentStep="$step" />
        <x-forms.step-label label="Informazioni Generali" :step="2" :currentStep="$step" />
        <x-forms.step-label label="Riepilogo" :step="3" :currentStep="$step" />
    </x-forms.step-label-container>

    {{-- STEP 1 --}}
    @if ($step === 1)
        <div class="grid grid-cols-2 gap-6">
            {{-- Customer Select --}}
            <div class="flex flex-col gap-1.5 col-span-2">
                <div class="flex items-center justify-between">
                    <flux:label>Cerca cliente censito</flux:label>

                    <x-buttons.inline-action-button label="Crea anagrafica nuovo cliente"
                        wire:click="openCreateCustomerModal">
                        <x-slot:icon>
                            <flux:icon.plus class="size-2.5" />
                        </x-slot:icon>
                    </x-buttons.inline-action-button>
                </div>

                <x-dropdown-select size="sm" :selectable-items="$customers" :selected="$selectedCustomerId" searchable :search="$customerSearch"
                    placeholder='Seleziona cliente' setFunction="setCustomer" :has-error="$errors->has('practiceForm.userId')" />

                <flux:error name="practiceForm.customerId" />
            </div>

            <x-forms.customer-preview-fields :selectedCustomer="$selectedCustomer" />
        </div>

        {{-- Next Step Buttons --}}
        <div class="flex items-center justify-end gap-x-3 mt-18">
            <a href="{{ route('practice.index') }}" wire:navigate>
                <flux:button variant="primary" type="button" size="sm"
                    class="px-10 bg-gray-custom-2 border-gray-custom-2 text-gray-custom-5 hover:bg-gray-custom-3-hover hover:border-gray-custom-3-hover hover:text-white">
                    Annulla
                </flux:button>
            </a>

            <flux:button variant="primary" type="button" size="sm" wire:click="firstNextStep"
                class="px-10 bg-azure-custom border-azure-custom hover:bg-azure-custom-hover hover:border-azure-custom-hover">
                Continua
            </flux:button>
        </div>
    @endif

    {{-- STEP 2 --}}
    @if ($step === 2)
        <div class="grid grid-cols-2 gap-6">
            {{-- Practice Code --}}
            <div class="flex flex-col gap-1.5">
                <flux:label>Id pratica *</flux:label>
                <div class="flex flex-col gap-0.5">
                    <flux:input size="sm" wire:model='{{ $form }}.practiceCode' />
                    <flux:error name="{{ $form }}.practiceCode" />
                </div>
            </div>
            {{-- Product Type --}}
            <div class="flex flex-col gap-1.5">
                <flux:label>Prodotto *</flux:label>
                <div class="flex flex-col gap-0.5">
                    <x-dropdown-select size="sm" :selectable-items="$productTypes" :selected="$selectedProductTypeId"
                        placeholder='Seleziona prodotto' setFunction="setProductType" :has-error="$errors->has('practiceForm.productTypeId')" />

                    <flux:error name="{{ $form }}.productTypeId" />
                </div>
            </div>
            {{-- Product Subtype --}}
            <div class="flex flex-col gap-1.5">
                <flux:label>Tipo prodotto</flux:label>
                <div class="flex flex-col gap-0.5">
                    <x-dropdown-select size="sm" :selectable-items="$productSubtypes" :selected="$selectedProductSubtypeId"
                        placeholder='Seleziona tipo prodotto' setFunction="setProductSubtype" :has-error="$errors->has('practiceForm.productSubtypeId')" />

                    <flux:error name="{{ $form }}.productSubtypeId" />
                </div>
            </div>
            {{-- Team Member --}}
            <div class="flex flex-col gap-1.5">
                <flux:label>Assegna a *</flux:label>
                <div class="flex flex-col gap-0.5">
                    <x-dropdown-select size="sm" :selectable-items="$teamMembers" :selected="$selectedPracticeUserId" searchable
                        :search="$teamMemberSearch" placeholder='Seleziona collaboratore' setFunction="setPracticeTeamMember"
                        :has-error="$errors->has('{{ $form }}.userId')" />

                    <flux:error name="{{ $form }}.userId" />
                </div>
            </div>
            {{-- Started At Date --}}
            <div class="flex flex-col gap-1.5">
                <flux:label>Data di inizio *</flux:label>
                <div class="flex flex-col gap-0.5">
                    <flux:input type="date" size="sm" wire:model='{{ $form }}.startedAt' />
                    <flux:error name="{{ $form }}.startedAt" />
                </div>
            </div>
            {{-- Paid At Date --}}
            <div class="flex flex-col gap-1.5">
                <flux:label>Data di fine</flux:label>
                <div class="flex flex-col gap-0.5">
                    <flux:input type="date" size="sm" wire:model='{{ $form }}.paidAt' />
                    <flux:error name="{{ $form }}.paidAt" />
                </div>
            </div>
            {{-- Amount Disbursed --}}
            <div class="flex flex-col gap-1.5">
                <flux:label>Importo *</flux:label>
                <div class="flex flex-col gap-0.5">
                    <x-forms.input-with-symbol type="number" min="0.00" step=".01" size="sm"
                        model="{{ $form }}.amountDisbursed" symbol="€" />
                    <flux:error name="{{ $form }}.amountDisbursed" />
                </div>
            </div>
            {{-- Installment --}}
            <div class="flex flex-col gap-1.5">
                <flux:label>Rate *</flux:label>
                <div class="flex flex-col gap-0.5">
                    <x-dropdown-select size="sm" :selectable-items="$installments" :selected="$selectedInstallmentId" placeholder='Seleziona rate'
                        setFunction="setInstallment" :has-error="$errors->has('practiceForm.installmentId')" />

                    <flux:error name="{{ $form }}.installmentId" />
                </div>
            </div>
            {{-- Rate Amount --}}
            <div class="flex flex-col gap-1.5">
                <flux:label>Rata mensile *</flux:label>
                <div class="flex flex-col gap-0.5">
                    <x-forms.input-with-symbol type="number" min="0.00" step=".01" size="sm"
                        model="{{ $form }}.rateAmount" symbol="€" />
                    <flux:error name="{{ $form }}.rateAmount" />
                </div>
            </div>
            {{-- Taeg --}}
            <div class="flex flex-col gap-1.5">
                <flux:label>Taeg fisso *</flux:label>
                <div class="flex flex-col gap-0.5">
                    <x-forms.input-with-symbol type="number" min="0.00" step=".01" size="sm"
                        model="{{ $form }}.taeg" symbol="%" />
                    <flux:error name="{{ $form }}.taeg" />
                </div>
            </div>
            {{-- Tan --}}
            <div class="flex flex-col gap-1.5">
                <flux:label>Tan fisso *</flux:label>
                <div class="flex flex-col gap-0.5">
                    <x-forms.input-with-symbol type="number" min="0.00" step=".01" size="sm"
                        model="{{ $form }}.tan" symbol="%" />
                    <flux:error name="{{ $form }}.tan" />
                </div>
            </div>
            {{-- Total Amount --}}
            <div class="flex flex-col gap-1.5">
                <flux:label>Totale dovuto</flux:label>
                <div class="flex flex-col gap-0.5">
                    <x-forms.input-with-symbol type="number" min="0.00" step=".01" size="sm"
                        model="{{ $form }}.totalAmount" symbol="€" />
                    <flux:error name="{{ $form }}.totalAmount" />
                </div>
            </div>
            {{-- Renewed --}}
            <div class="flex flex-col gap-1.5">
                <flux:label>Rinnovo</flux:label>
                <div class="flex flex-col gap-0.5">
                    <flux:input type="date" size="sm" wire:model='{{ $form }}.renewableAt' />
                    <flux:error name="{{ $form }}.renewableAt" />
                </div>
            </div>
            {{-- Customer Type --}}
            <div class="flex flex-col gap-1.5">
                <flux:label>Tipologia cliente</flux:label>
                <div class="flex flex-col gap-0.5">
                    <x-dropdown-select size="sm" :selectable-items="$customerTypes" :selected="$selectedCustomerTypeId"
                        placeholder='Seleziona tipologia cliente' setFunction="setCustomerType" :has-error="$errors->has('practiceForm.customerTypeId')" />

                    <flux:error name="{{ $form }}.customerTypeId" />
                </div>
            </div>
            {{-- Insurance --}}
            <div class="flex flex-col gap-1.5">
                <flux:label>Assicurazione</flux:label>
                <div class="flex flex-col gap-0.5">
                    <x-dropdown-select size="sm" :selectable-items="$insurances" :selected="$selectedInsuranceId"
                        placeholder='Seleziona assicurazione' setFunction="setInsurance" :has-error="$errors->has('practiceForm.insuranceId')" />

                    <flux:error name="{{ $form }}.insuranceId" />
                </div>
            </div>
            {{-- Financial Table --}}
            <div class="flex flex-col gap-1.5">
                <flux:label>Tabella provvigionale</flux:label>
                <div class="flex flex-col gap-0.5">
                    <x-dropdown-select size="sm" :selectable-items="$financialTables" :selected="$selectedFinancialTableId"
                        placeholder='Seleziona tabella provvigionale' setFunction="setFinancialTable"
                        :has-error="$errors->has('practiceForm.financialTableId')" />

                    <flux:error name="{{ $form }}.financialTableId" />
                </div>
            </div>
            {{-- Notes --}}
            <div class="flex flex-col gap-1.5 col-span-2">
                <flux:textarea label="Note" resize="none" wire:model='{{ $form }}.notes' />
                <flux:error name="{{ $form }}.notes" />
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
    @endif

    {{-- STEP 3 --}}
    @if ($step === 3)
        {{-- Customer Details --}}
        <x-forms.customer-preview-fields :selectedCustomer="$selectedCustomer" />

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
                <x-display-input value="{{ $practiceForm->startedAt }}" />
            </div>
            {{-- Paid At Date --}}
            <div class="flex flex-col gap-1.5">
                <flux:label>Data di fine</flux:label>
                <x-display-input value="{{ $practiceForm->paidAt }}" />
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
            {{-- Renewed --}}
            <div class="flex flex-col gap-1.5">
                <flux:label>Rinnovo</flux:label>
                <x-display-input value="{{ $practiceForm->renewableAt }}" />
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

        {{-- Next Step Buttons --}}
        <div class="flex items-center justify-end gap-x-3 mt-18">
            <flux:button variant="primary" type="button" size="sm" wire:click="secondPrevStep"
                class="px-10 bg-gray-custom-2 border-gray-custom-2 text-gray-custom-5 hover:bg-gray-custom-3-hover hover:border-gray-custom-3-hover hover:text-white">
                Annulla
            </flux:button>

            <flux:button variant="primary" type="submit" size="sm"
                class="px-10 bg-azure-custom border-azure-custom hover:bg-azure-custom-hover hover:border-azure-custom-hover">
                {{ $submitButtonLabel }}
            </flux:button>
        </div>
    @endif

</form>
