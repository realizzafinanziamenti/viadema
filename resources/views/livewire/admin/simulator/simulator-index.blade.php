<div>
    <x-page-title label="Simulatore" class="mt-1" />

    <x-card class="max-w-3xl mx-auto">
        <x-card-header class="mb-3" label="Simulazione cessione del quinto" />

        <form wire:submit.prevent='' class="w-xl mx-auto mt-10 mb-5">

            <div class="grid grid-cols-2 gap-6">
                {{-- Product Type --}}
                <div class="flex flex-col gap-1.5">
                    <flux:label>Prodotto</flux:label>
                    <div class="flex flex-col gap-0.5">
                        <x-dropdown-select size="sm" placeholder='Seleziona prodotto' />

                        {{-- <flux:error name="practiceForm.productTypeId" /> --}}
                    </div>
                </div>

                {{-- First Installment Date --}}
                <div class="flex flex-col gap-1.5">
                    <flux:label>Data decorrenza</flux:label>
                    <div class="flex flex-col gap-0.5">
                        <flux:input type="date" size="sm" />
                        {{-- <flux:error name="practiceForm.firstInstallmentDate" /> --}}
                    </div>
                </div>

                {{-- Product Subtype --}}
                <div class="flex flex-col gap-1.5">
                    <flux:label>Tipo prodotto</flux:label>
                    <div class="flex flex-col gap-0.5">
                        <x-dropdown-select size="sm" placeholder='Seleziona tipo prodotto' />

                        {{-- <flux:error name="practiceForm.productTypeId" /> --}}
                    </div>
                </div>

                {{-- Customer Type --}}
                <div class="flex flex-col gap-1.5">
                    <flux:label>Tipologia cliente</flux:label>
                    <div class="flex flex-col gap-0.5">
                        <x-dropdown-select size="sm" placeholder='Seleziona tipologia cliente' />

                        {{-- <flux:error name="practiceForm.customerTypeId" /> --}}
                    </div>
                </div>

                {{-- Category --}}
                <div class="flex flex-col gap-1.5">
                    <flux:label>Categoria</flux:label>
                    <div class="flex flex-col gap-0.5">
                        <x-dropdown-select size="sm" placeholder='Seleziona categoria' />

                        {{-- <flux:error name="practiceForm.customerTypeId" /> --}}
                    </div>
                </div>

                {{-- Insurance --}}
                <div class="flex flex-col gap-1.5">
                    <flux:label>Assicurazione</flux:label>
                    <div class="flex flex-col gap-0.5">
                        <x-dropdown-select size="sm" placeholder='Seleziona assicurazione' />

                        {{-- <flux:error name="practiceForm.insuranceId" /> --}}
                    </div>
                </div>

                {{-- Date of Employement --}}
                <div class="flex flex-col gap-1.5">
                    <flux:label>Data assunzione</flux:label>
                    <div class="flex flex-col gap-0.5">
                        <flux:input type="date" size="sm" />
                        {{-- <flux:error name="practiceForm.firstInstallmentDate" /> --}}
                    </div>
                </div>

                {{-- Birth Date --}}
                <div class="flex flex-col gap-1.5">
                    <flux:label>Data di nascita</flux:label>
                    <div class="flex flex-col gap-0.5">
                        <flux:input type="date" size="sm" />
                        {{-- <flux:error name="practiceForm.firstInstallmentDate" /> --}}
                    </div>
                </div>

                {{-- Rate Amount --}}
                <div class="flex flex-col gap-1.5">
                    <flux:label>Rata mensile</flux:label>
                    <div class="flex flex-col gap-0.5">
                        <x-forms.input-with-symbol type="number" min="0.00" max="99999999.99" step=".01"
                            size="sm" symbol="€" />
                        {{-- <flux:error name="practiceForm.rateAmount" /> --}}
                    </div>
                </div>

                {{-- Installment --}}
                <div class="flex flex-col gap-1.5">
                    <flux:label>Rate</flux:label>
                    <div class="flex flex-col gap-0.5">
                        <x-dropdown-select size="sm" placeholder='Seleziona rate' />
                        {{-- <flux:error name="practiceForm.installmentId" /> --}}
                    </div>
                </div>
            </div>

            {{-- Buttons --}}
            <div class="flex items-center justify-end gap-x-3 mt-18">
                <flux:button variant="primary" type="submit" size="sm"
                    class="px-10 bg-azure-custom border-azure-custom hover:bg-azure-custom-hover hover:border-azure-custom-hover">
                    Genera preventivo
                </flux:button>
            </div>

        </form>
    </x-card>
</div>
