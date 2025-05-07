<div class="w-full">
    <x-card class="w-3xl mx-auto">
        <x-card-header label="Crea nuova pratica" />

        <form wire:submit.prevent='save' class="w-2xl mx-auto mt-10 mb-5">

            {{-- Toggle Buttons --}}
            <x-forms.step-label-container class="mx-auto w-5/6 mb-6">
                <x-forms.step-label label="Dati Cliente" :step="1" :currentStep="$step" />
                <x-forms.step-label label="Informazioni Generali" :step="2" :currentStep="$step" />
                <x-forms.step-label label="Riepilogo" :step="3" :currentStep="$step" />
            </x-forms.step-label-container>

            {{-- STEP 1 --}}
            <div wire:show="step === 1">
                <div class="grid grid-cols-2 gap-6">
                    {{-- Customer Select --}}
                    <div class="flex flex-col gap-1.5 col-span-2">
                        <flux:label>Cerca cliente censito</flux:label>

                        <x-dropdown-select model="practiceForm.userId" :selectable-items="$customers" :has-error="$errors->has('practiceForm.userId')" searchable
                            search-model="customerSearch" placeholder="Seleziona cliente" />

                        <flux:error name="practiceForm.userId" />
                    </div>

                    {{-- First Name --}}
                    <div class="flex flex-col gap-1.5">
                        <flux:label>Nome</flux:label>
                        <flux:input size="sm" :value="$selectedCustomer?->first_name" disabled />
                    </div>
                    {{-- Last Name --}}
                    <div class="flex flex-col gap-1.5">
                        <flux:label>Cognome</flux:label>
                        <flux:input size="sm" :value="$selectedCustomer?->last_name" disabled />
                    </div>
                    {{-- Phone --}}
                    <div class="flex flex-col gap-1.5">
                        <flux:label>Cellulare</flux:label>
                        <flux:input size="sm" :value="$selectedCustomer?->phone" disabled />
                    </div>
                    {{-- Date of Birth --}}
                    <div class="flex flex-col gap-1.5">
                        <flux:label>Data di Nascita</flux:label>
                        <flux:input size="sm" :value="$selectedCustomer?->formatted_date_of_birth" disabled />
                    </div>
                    {{-- Tax ID --}}
                    <div class="flex flex-col gap-1.5">
                        <flux:label>Codice Fiscale</flux:label>
                        <flux:input size="sm" :value="$selectedCustomer?->tax_id" disabled />
                    </div>
                    {{-- Email --}}
                    <div class="flex flex-col gap-1.5">
                        <flux:label>Email</flux:label>
                        <flux:input type="email" size="sm" :value="$selectedCustomer?->email" disabled />
                    </div>
                    {{-- Address --}}
                    <div class="flex flex-col gap-1.5">
                        <flux:label>Indirizzo</flux:label>
                        <flux:input size="sm" :value="$selectedCustomer?->address" disabled />
                    </div>

                    <div class="grid grid-cols-3 gap-6">
                        {{-- Postal Code --}}
                        <div class="flex flex-col gap-1.5 col-span-1">
                            <flux:label>Cap</flux:label>
                            <flux:input size="sm" :value="$selectedCustomer?->postal_code" disabled />
                        </div>
                        {{-- Province --}}
                        <div class="flex flex-col gap-1.5 col-span-2">
                            <flux:label>Provincia</flux:label>
                            <flux:input size="sm" :value="$selectedCustomer?->state" disabled />
                        </div>
                    </div>

                    {{-- City --}}
                    <div class="flex flex-col gap-1.5">
                        <flux:label>Città</flux:label>
                        <flux:input size="sm" :value="$selectedCustomer?->city" disabled />
                    </div>

                    {{-- Team Member Select --}}
                    @if (auth()->user()->can('assign customer to user'))
                        <div class="flex flex-col gap-1.5">
                            <flux:label>Collaboratore</flux:label>
                            <flux:input size="sm" :value="$selectedCustomer?->user?->full_name" disabled />
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
                        <flux:label>Id pratica</flux:label>
                        <div class="flex flex-col gap-0.5">
                            <flux:input size="sm" wire:model='practiceForm.firstName' />
                            <flux:error name="practiceForm.firstName" />
                        </div>
                    </div>
                    {{-- Product Type --}}
                    <div class="flex flex-col gap-1.5">
                        <flux:label>Prodotto</flux:label>
                        <div class="flex flex-col gap-0.5">
                            <flux:input size="sm" wire:model='practiceForm.lastName' />
                            <flux:error name="practiceForm.lastName" />
                        </div>
                    </div>
                    {{-- Product Subtype --}}
                    <div class="flex flex-col gap-1.5">
                        <flux:label>Tipo prodotto</flux:label>
                        <div class="flex flex-col gap-0.5">
                            <flux:input size="sm" wire:model='practiceForm.phone' />
                            <flux:error name="practiceForm.phone" />
                        </div>
                    </div>
                    {{-- Team Member --}}
                    <div class="flex flex-col gap-1.5">
                        <flux:label>Assegna a</flux:label>
                        <div class="flex flex-col gap-0.5">
                            <flux:input size="sm" wire:model='practiceForm.phone' />
                            <flux:error name="practiceForm.phone" />
                        </div>
                    </div>
                    {{-- Started At Date --}}
                    <div class="flex flex-col gap-1.5">
                        <flux:label>Data di inizio</flux:label>
                        <div class="flex flex-col gap-0.5">
                            <flux:input type="date" size="sm" wire:model='practiceForm.dateOfBirth' />
                            <flux:error name="practiceForm.dateOfBirth" />
                        </div>
                    </div>
                    {{-- Paid At Date --}}
                    <div class="flex flex-col gap-1.5">
                        <flux:label>Data di fine</flux:label>
                        <div class="flex flex-col gap-0.5">
                            <flux:input size="sm" wire:model='practiceForm.taxId' />
                            <flux:error name="practiceForm.taxId" />
                        </div>
                    </div>
                    {{-- Amount Disbursed --}}
                    <div class="flex flex-col gap-1.5">
                        <flux:label>Importo</flux:label>
                        <div class="flex flex-col gap-0.5">
                            <flux:input type="email" size="sm" wire:model='practiceForm.email' />
                            <flux:error name="practiceForm.email" />
                        </div>
                    </div>
                    {{-- Installment --}}
                    <div class="flex flex-col gap-1.5">
                        <flux:label>Rate</flux:label>
                        <div class="flex flex-col gap-0.5">
                            <flux:input size="sm" wire:model='practiceForm.address' />
                            <flux:error name="practiceForm.address" />
                        </div>
                    </div>
                    {{-- Rate Amount --}}
                    <div class="flex flex-col gap-1.5">
                        <flux:label>Rata mensile</flux:label>
                        <div class="flex flex-col gap-0.5">
                            <flux:input size="sm" wire:model='practiceForm.postalCode' />
                            <flux:error name="practiceForm.postalCode" />
                        </div>
                    </div>
                    {{-- Taeg --}}
                    <div class="flex flex-col gap-1.5">
                        <flux:label>Taeg fisso</flux:label>
                        <div class="flex flex-col gap-0.5">
                            <flux:input size="sm" wire:model='practiceForm.state' />
                            <flux:error name="practiceForm.state" />
                        </div>
                    </div>
                    {{-- Tan --}}
                    <div class="flex flex-col gap-1.5">
                        <flux:label>Tan fisso</flux:label>
                        <div class="flex flex-col gap-0.5">
                            <flux:input size="sm" wire:model='practiceForm.city' />
                            <flux:error name="practiceForm.city" />
                        </div>
                    </div>
                    {{-- Total Amount --}}
                    <div class="flex flex-col gap-1.5">
                        <flux:label>Totale dovuto</flux:label>
                        <div class="flex flex-col gap-0.5">
                            <flux:input size="sm" wire:model='practiceForm.city' />
                            <flux:error name="practiceForm.city" />
                        </div>
                    </div>
                    {{-- Renewed --}}
                    <div class="flex flex-col gap-1.5">
                        <flux:label>Rinnovo</flux:label>
                        <div class="flex flex-col gap-0.5">
                            <flux:input size="sm" wire:model='practiceForm.city' />
                            <flux:error name="practiceForm.city" />
                        </div>
                    </div>
                    {{-- Customer Type --}}
                    <div class="flex flex-col gap-1.5">
                        <flux:label>Tipologia cliente</flux:label>
                        <div class="flex flex-col gap-0.5">
                            <flux:input size="sm" wire:model='practiceForm.city' />
                            <flux:error name="practiceForm.city" />
                        </div>
                    </div>
                    {{-- Insurance --}}
                    <div class="flex flex-col gap-1.5">
                        <flux:label>Assicurazione</flux:label>
                        <div class="flex flex-col gap-0.5">
                            <flux:input size="sm" wire:model='practiceForm.city' />
                            <flux:error name="practiceForm.city" />
                        </div>
                    </div>
                    {{-- Financial Table --}}
                    <div class="flex flex-col gap-1.5">
                        <flux:label>Tabella provvigionale</flux:label>
                        <div class="flex flex-col gap-0.5">
                            <flux:input size="sm" wire:model='practiceForm.city' />
                            <flux:error name="practiceForm.city" />
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
            </div>

        </form>
    </x-card>
</div>
