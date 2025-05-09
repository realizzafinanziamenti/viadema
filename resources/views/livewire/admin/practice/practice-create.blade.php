<div class="w-full" x-data x-on:step-changed.window="window.scrollTo({ top: 0, behavior: 'smooth' })">
    <x-card class="w-3xl mx-auto">
        <x-card-header label="Crea nuova pratica" />

        <form wire:submit.prevent='savePractice' class="w-xl mx-auto mt-10 mb-5">

            {{-- Toggle Buttons --}}
            <x-forms.step-label-container class="mx-auto w-3/4 mb-6">
                <x-forms.step-label label="Dati Cliente" :step="1" :currentStep="$step" />
                <x-forms.step-label label="Informazioni Generali" :step="2" :currentStep="$step" />
                <x-forms.step-label label="Riepilogo" :step="3" :currentStep="$step" />
            </x-forms.step-label-container>

            {{-- STEP 1 --}}
            <div wire:show="step === 1">
                <div class="grid grid-cols-2 gap-6">
                    {{-- Customer Select --}}
                    <div class="flex flex-col gap-1.5 col-span-2">
                        <div class="flex items-center justify-between">
                            <flux:label>Cerca cliente censito</flux:label>

                            <x-buttons.inline-action-button label="Crea anagrafica nuovo cliente"
                                x-on:click="$dispatch('open-modal', 'customer-create')">
                                <x-slot:icon>
                                    <flux:icon.plus class="size-2.5" />
                                </x-slot:icon>
                            </x-buttons.inline-action-button>
                        </div>

                        <x-dropdown-select model="practiceForm.customerId" :selectable-items="$customers" :has-error="$errors->has('practiceForm.customerId')"
                            searchable search-model="customerSearch" placeholder="Seleziona cliente" />

                        <flux:error name="practiceForm.customerId" />
                    </div>

                    {{-- First Name --}}
                    <div class="flex flex-col gap-1.5">
                        <flux:label>Nome</flux:label>
                        <x-display-input value="{{ $selectedCustomer?->first_name }}" />
                    </div>
                    {{-- Last Name --}}
                    <div class="flex flex-col gap-1.5">
                        <flux:label>Cognome</flux:label>
                        <x-display-input value="{{ $selectedCustomer?->last_name }}" />
                    </div>
                    {{-- Phone --}}
                    <div class="flex flex-col gap-1.5">
                        <flux:label>Cellulare</flux:label>
                        <x-display-input value="{{ $selectedCustomer?->phone }}" />
                    </div>
                    {{-- Date of Birth --}}
                    <div class="flex flex-col gap-1.5">
                        <flux:label>Data di Nascita</flux:label>
                        <x-display-input value="{{ $selectedCustomer?->formatted_date_of_birth }}" />
                    </div>
                    {{-- Tax ID --}}
                    <div class="flex flex-col gap-1.5">
                        <flux:label>Codice Fiscale</flux:label>
                        <x-display-input value="{{ $selectedCustomer?->tax_id }}" />
                    </div>
                    {{-- Email --}}
                    <div class="flex flex-col gap-1.5">
                        <flux:label>Email</flux:label>
                        <x-display-input value="{{ $selectedCustomer?->email }}" />
                    </div>
                    {{-- Address --}}
                    <div class="flex flex-col gap-1.5">
                        <flux:label>Indirizzo</flux:label>
                        <x-display-input value="{{ $selectedCustomer?->address }}" />
                    </div>

                    <div class="grid grid-cols-3 gap-6">
                        {{-- Postal Code --}}
                        <div class="flex flex-col gap-1.5 col-span-1">
                            <flux:label>Cap</flux:label>
                            <x-display-input value="{{ $selectedCustomer?->postal_code }}" />
                        </div>
                        {{-- Province --}}
                        <div class="flex flex-col gap-1.5 col-span-2">
                            <flux:label>Provincia</flux:label>
                            <x-display-input value="{{ $selectedCustomer?->state }}" />
                        </div>
                    </div>

                    {{-- City --}}
                    <div class="flex flex-col gap-1.5">
                        <flux:label>Città</flux:label>
                        <x-display-input value="{{ $selectedCustomer?->city }}" />
                    </div>

                    {{-- Team Member Select --}}
                    @if (auth()->user()->can('assign customer to user'))
                        <div class="flex flex-col gap-1.5">
                            <flux:label>Collaboratore</flux:label>
                            <x-display-input value="{{ $selectedCustomer?->user?->full_name }}" />
                        </div>
                    @endif
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
            </div>

            {{-- STEP 2 --}}
            <div wire:show="step === 2">
                <div class="grid grid-cols-2 gap-6">
                    {{-- Practice Code --}}
                    <div class="flex flex-col gap-1.5">
                        <flux:label>Id pratica *</flux:label>
                        <div class="flex flex-col gap-0.5">
                            <flux:input size="sm" wire:model='practiceForm.practiceCode' />
                            <flux:error name="practiceForm.practiceCode" />
                        </div>
                    </div>
                    {{-- Product Type --}}
                    <div class="flex flex-col gap-1.5">
                        <flux:label>Prodotto *</flux:label>
                        <div class="flex flex-col gap-0.5">
                            <x-dropdown-select model="practiceForm.productTypeId" :selectable-items="$productTypes" :has-error="$errors->has('practiceForm.productTypeId')"
                                placeholder="Seleziona prodotto" />

                            <flux:error name="practiceForm.productTypeId" />
                        </div>
                    </div>
                    {{-- Product Subtype --}}
                    <div class="flex flex-col gap-1.5">
                        <flux:label>Tipo prodotto</flux:label>
                        <div class="flex flex-col gap-0.5">
                            <x-dropdown-select model="practiceForm.productSubtypeId" :selectable-items="$productSubtypes"
                                :has-error="$errors->has('practiceForm.productSubtypeId')" placeholder="Seleziona tipo prodotto" />

                            <flux:error name="practiceForm.productSubtypeId" />
                        </div>
                    </div>
                    {{-- Team Member --}}
                    <div class="flex flex-col gap-1.5">
                        <flux:label>Assegna a *</flux:label>
                        <div class="flex flex-col gap-0.5">
                            <x-dropdown-select model="practiceForm.userId" :selectable-items="$teamMembers" :has-error="$errors->has('practiceForm.userId')"
                                placeholder="Seleziona collaboratore" />

                            <flux:error name="practiceForm.userId" />
                        </div>
                    </div>
                    {{-- Started At Date --}}
                    <div class="flex flex-col gap-1.5">
                        <flux:label>Data di inizio *</flux:label>
                        <div class="flex flex-col gap-0.5">
                            <flux:input type="date" size="sm" wire:model='practiceForm.startedAt' />
                            <flux:error name="practiceForm.startedAt" />
                        </div>
                    </div>
                    {{-- Paid At Date --}}
                    <div class="flex flex-col gap-1.5">
                        <flux:label>Data di fine</flux:label>
                        <div class="flex flex-col gap-0.5">
                            <flux:input type="date" size="sm" wire:model='practiceForm.paidAt' />
                            <flux:error name="practiceForm.paidAt" />
                        </div>
                    </div>
                    {{-- Amount Disbursed --}}
                    <div class="flex flex-col gap-1.5">
                        <flux:label>Importo *</flux:label>
                        <div class="flex flex-col gap-0.5">
                            <x-forms.input-with-symbol type="number" min="0.00" step=".01" size="sm"
                                model="practiceForm.amountDisbursed" symbol="€" />
                            <flux:error name="practiceForm.amountDisbursed" />
                        </div>
                    </div>
                    {{-- Installment --}}
                    <div class="flex flex-col gap-1.5">
                        <flux:label>Rate *</flux:label>
                        <div class="flex flex-col gap-0.5">
                            <x-dropdown-select model="practiceForm.installmentId" :selectable-items="$installments" :has-error="$errors->has('practiceForm.installmentId')"
                                placeholder="Seleziona rate" />

                            <flux:error name="practiceForm.installmentId" />
                        </div>
                    </div>
                    {{-- Rate Amount --}}
                    <div class="flex flex-col gap-1.5">
                        <flux:label>Rata mensile *</flux:label>
                        <div class="flex flex-col gap-0.5">
                            <x-forms.input-with-symbol type="number" min="0.00" step=".01" size="sm"
                                model="practiceForm.rateAmount" symbol="€" />
                            <flux:error name="practiceForm.rateAmount" />
                        </div>
                    </div>
                    {{-- Taeg --}}
                    <div class="flex flex-col gap-1.5">
                        <flux:label>Taeg fisso *</flux:label>
                        <div class="flex flex-col gap-0.5">
                            <x-forms.input-with-symbol type="number" min="0.00" step=".01" size="sm"
                                model="practiceForm.taeg" symbol="%" />
                            <flux:error name="practiceForm.taeg" />
                        </div>
                    </div>
                    {{-- Tan --}}
                    <div class="flex flex-col gap-1.5">
                        <flux:label>Tan fisso *</flux:label>
                        <div class="flex flex-col gap-0.5">
                            <x-forms.input-with-symbol type="number" min="0.00" step=".01" size="sm"
                                model="practiceForm.tan" symbol="%" />
                            <flux:error name="practiceForm.tan" />
                        </div>
                    </div>
                    {{-- Total Amount --}}
                    <div class="flex flex-col gap-1.5">
                        <flux:label>Totale dovuto</flux:label>
                        <div class="flex flex-col gap-0.5">
                            <x-forms.input-with-symbol type="number" min="0.00" step=".01" size="sm"
                                model="practiceForm.totalAmount" symbol="€" />
                            <flux:error name="practiceForm.totalAmount" />
                        </div>
                    </div>
                    {{-- Renewed --}}
                    <div class="flex flex-col gap-1.5">
                        <flux:label>Rinnovo</flux:label>
                        <div class="flex flex-col gap-0.5">
                            <flux:input type="date" size="sm" wire:model='practiceForm.renewableAt' />
                            <flux:error name="practiceForm.renewableAt" />
                        </div>
                    </div>
                    {{-- Customer Type --}}
                    <div class="flex flex-col gap-1.5">
                        <flux:label>Tipologia cliente</flux:label>
                        <div class="flex flex-col gap-0.5">
                            <x-dropdown-select model="practiceForm.customerTypeId" :selectable-items="$customerTypes"
                                :has-error="$errors->has('practiceForm.customerTypeId')" placeholder="Seleziona tipologia cliente" />

                            <flux:error name="practiceForm.customerTypeId" />
                        </div>
                    </div>
                    {{-- Insurance --}}
                    <div class="flex flex-col gap-1.5">
                        <flux:label>Assicurazione</flux:label>
                        <div class="flex flex-col gap-0.5">
                            <x-dropdown-select model="practiceForm.insuranceId" :selectable-items="$insurances" :has-error="$errors->has('practiceForm.insuranceId')"
                                placeholder="Seleziona assicurazione" />

                            <flux:error name="practiceForm.insuranceId" />
                        </div>
                    </div>
                    {{-- Financial Table --}}
                    <div class="flex flex-col gap-1.5">
                        <flux:label>Tabella provvigionale</flux:label>
                        <div class="flex flex-col gap-0.5">
                            <x-dropdown-select model="practiceForm.financialTableId" :selectable-items="$financialTables"
                                :has-error="$errors->has('practiceForm.financialTableId')" placeholder="Seleziona tabella provvigionale" />

                            <flux:error name="practiceForm.financialTableId" />
                        </div>
                    </div>
                    {{-- Notes --}}
                    <div class="flex flex-col gap-1.5 col-span-2">
                        <flux:textarea label="Note" resize="none" wire:model='practiceForm.notes' />
                        <flux:error name="practiceForm.notes" />
                    </div>
                </div>

                @if ($errors->any())
                    <ul class="text-sm text-red-600 space-y-1 mt-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                @endif


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
            </div>

            {{-- STEP 3 --}}
            <div wire:show="step === 3">
                {{-- Customer Details --}}
                <div class="grid grid-cols-2 gap-6">
                    {{-- First Name --}}
                    <div class="flex flex-col gap-1.5">
                        <flux:label>Nome</flux:label>
                        <x-display-input value="{{ $selectedCustomer?->first_name }}" />
                    </div>
                    {{-- Last Name --}}
                    <div class="flex flex-col gap-1.5">
                        <flux:label>Cognome</flux:label>
                        <x-display-input value="{{ $selectedCustomer?->last_name }}" />
                    </div>
                    {{-- Phone --}}
                    <div class="flex flex-col gap-1.5">
                        <flux:label>Cellulare</flux:label>
                        <x-display-input value="{{ $selectedCustomer?->phone }}" />
                    </div>
                    {{-- Date of Birth --}}
                    <div class="flex flex-col gap-1.5">
                        <flux:label>Data di Nascita</flux:label>
                        <x-display-input value="{{ $selectedCustomer?->formatted_date_of_birth }}" />
                    </div>
                    {{-- Tax ID --}}
                    <div class="flex flex-col gap-1.5">
                        <flux:label>Codice Fiscale</flux:label>
                        <x-display-input value="{{ $selectedCustomer?->tax_id }}" />
                    </div>
                    {{-- Email --}}
                    <div class="flex flex-col gap-1.5">
                        <flux:label>Email</flux:label>
                        <x-display-input value="{{ $selectedCustomer?->email }}" />
                    </div>
                    {{-- Address --}}
                    <div class="flex flex-col gap-1.5">
                        <flux:label>Indirizzo</flux:label>
                        <x-display-input value="{{ $selectedCustomer?->address }}" />
                    </div>

                    <div class="grid grid-cols-3 gap-6">
                        {{-- Postal Code --}}
                        <div class="flex flex-col gap-1.5 col-span-1">
                            <flux:label>Cap</flux:label>
                            <x-display-input value="{{ $selectedCustomer?->postal_code }}" />
                        </div>
                        {{-- Province --}}
                        <div class="flex flex-col gap-1.5 col-span-2">
                            <flux:label>Provincia</flux:label>
                            <x-display-input value="{{ $selectedCustomer?->state }}" />
                        </div>
                    </div>

                    {{-- City --}}
                    <div class="flex flex-col gap-1.5">
                        <flux:label>Città</flux:label>
                        <x-display-input value="{{ $selectedCustomer?->city }}" />
                    </div>

                    {{-- Team Member Select --}}
                    @if (auth()->user()->can('assign customer to user'))
                        <div class="flex flex-col gap-1.5">
                            <flux:label>Collaboratore</flux:label>
                            <x-display-input value="{{ $selectedCustomer?->user?->full_name }}" />
                        </div>
                    @endif
                </div>

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
                        Continua
                    </flux:button>
                </div>
            </div>

        </form>
    </x-card>

    {{-- Create New Customer Modal --}}
    <x-modal name="customer-create" maxWidth="3xl">
        <x-modal-header label="Crea nuovo cliente" />
        <x-forms.customer-form :teamMembers="$teamMembers" form="customerForm" submitFunction="saveCustomer"
            closeModalButton />
    </x-modal>
</div>
