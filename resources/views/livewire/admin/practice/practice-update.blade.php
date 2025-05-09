<div class="w-full" x-data x-on:step-changed.window="window.scrollTo({ top: 0, behavior: 'smooth' })">
    <x-card class="w-3xl mx-auto">
        <x-card-header label="Modifica nuova pratica" />

        <x-forms.practice-form submitButtonLabel="Modifica" :practiceForm="$practiceForm" :step="$step" :customers="$customers"
            :selectedCustomer="$selectedCustomer" :productTypes="$productTypes" :productSubtypes="$productSubtypes" :teamMembers="$teamMembers" :installments="$installments" :customerTypes="$customerTypes"
            :insurances="$insurances" :financialTables="$financialTables" />
    </x-card>

    {{-- Create New Customer Modal --}}
    <x-modal name="customer-create" maxWidth="3xl">
        <x-modal-header label="Crea nuovo cliente" />
        <x-forms.customer-form :teamMembers="$teamMembers" form="customerForm" search="teamMemberSearch"
            submitFunction="saveCustomer" closeModalButton />
    </x-modal>
</div>
