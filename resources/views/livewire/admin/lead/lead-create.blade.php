<div class="w-full">
    <x-card class="w-3xl mx-auto">
        <x-card-header label="Crea nuovo profilo" />

        <form wire:submit.prevent='save' class="w-2xl mx-auto mt-10 mb-5">
            @include('partials.customer.customer-form-fields', [
                'context' => 'lead',
                'search' => 'search',
                'form' => 'form',
                'selectedUserId' => $form->userId,
                'selectedCustomerTypeId' => $form->customerTypeId,
                'selectedLeadStatus' => $form->leadStatus,
                'leadSources' => $leadSources,
                'selectedAcquisitionChannel' => $opportunityForm->acquisitionChannel,
            ])
            @include('partials.practice.practice-opportunity-fields', [
                'productTypes' => $productTypes,
                'productSubtypes' => $productSubtypes,
                'installments' => $installments,
                'insurances' => $insurances,
                'selectedProductTypeId' => $opportunityForm->productTypeId,
                'selectedProductSubtypeId' => $opportunityForm->productSubtypeId,
                'selectedInstallmentId' => $opportunityForm->installmentId,
                'selectedInsuranceId' => $opportunityForm->insuranceId,
                'financialTables' => $financialTables,
                'customerTypes' => $customerTypes,
                'productionTypes' => $productionTypes,
                'selectedFinancialTableId' => $opportunityForm->financialTableId,
                'selectedOpportunityCustomerTypeId' => $opportunityForm->customerTypeId,
                'selectedProductionType' => $opportunityForm->productionType,
            ])

            {{-- Submit Buttons --}}
            <div class="flex items-center justify-end gap-x-3 mt-18">
                <a href="{{ route('lead.index') }}" wire:navigate>
                    <flux:button variant="primary" type="button" size="sm"
                        class="px-10 bg-gray-custom-2 border-gray-custom-2 text-gray-custom-5 hover:bg-gray-custom-3-hover hover:border-gray-custom-3-hover hover:text-white">
                        Annulla
                    </flux:button>
                </a>

                <flux:button variant="primary" type="submit" size="sm"
                    class="px-10 bg-azure-custom border-azure-custom hover:bg-azure-custom-hover hover:border-azure-custom-hover">
                    Crea
                </flux:button>
            </div>
        </form>
    </x-card>
</div>
