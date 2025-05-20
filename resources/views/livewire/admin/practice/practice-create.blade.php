<div class="w-full" x-data x-on:step-changed.window="window.scrollTo({ top: 0, behavior: 'smooth' })">
    <x-card class="w-3xl mx-auto">
        <x-card-header label="Crea nuova pratica" />

        <x-forms.practice-form :practiceForm="$practiceForm" :step="$step" :customers="$customers" :selectedCustomer="$selectedCustomer" :productTypes="$productTypes"
            :productSubtypes="$productSubtypes" :teamMembers="$teamMembers" :installments="$installments" :customerTypes="$customerTypes" :insurances="$insurances" :financialTables="$financialTables"
            :selectedCustomerId="$selectedCustomerId" :selectedProductTypeId="$selectedProductTypeId" :selectedProductSubtypeId="$selectedProductSubtypeId" :selectedInstallmentId="$selectedInstallmentId" :selectedCustomerTypeId="$selectedCustomerTypeId"
            :selectedInsuranceId="$selectedInsuranceId" :selectedFinancialTableId="$selectedFinancialTableId" :selectedPracticeUserId="$selectedPracticeUserId" />
    </x-card>

    {{-- Create New Customer Modal --}}
    <x-modal name="customer-create" maxWidth="3xl">
        <x-modal-header label="Crea nuovo cliente" />
        <x-forms.customer-form :teamMembers="$teamMembers" form="customerForm" search="teamMemberSearch"
            submitFunction="saveCustomer" :selectedUserId="$selectedUserId" closeModalButton />
    </x-modal>
</div>
