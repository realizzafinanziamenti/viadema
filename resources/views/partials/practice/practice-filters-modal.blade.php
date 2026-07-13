<x-modals.filter-modal header="Filtra pratiche" maxWidth="5xl">
    <div class="grid grid-cols-3 gap-4">
        {{-- User --}}
        <div class="flex flex-col gap-1.5">
            <flux:label>Collaboratore</flux:label>
            <x-dropdown-select size="sm" :selectable-items="$teamMembers" :selected="$tempSelectedTeamMemberForFilter" searchable search="teamMemberSearch"
                placeholder='Seleziona collaboratore' setFunction="setTeamMember" :has-error="$errors->has('tempSelectedTeamMemberForFilter')" />
            <flux:error name="tempSelectedTeamMemberForFilter" />
        </div>

        {{-- Practice Status --}}
        @if ($expired === false)
            <div class="flex flex-col gap-1.5">
                <flux:label>Stato pratica</flux:label>
                <x-dropdown-select size="sm" :selectable-items="$practiceStatusesForFilter" :selected="$tempSelectedPracticeStatusForFilter" placeholder='Seleziona stato'
                    setFunction="setPracticeStatusForFilter" :has-error="$errors->has('tempSelectedPracticeStatusForFilter')" />
                <flux:error name="tempSelectedPracticeStatusForFilter" />
            </div>
        @endif


        {{-- Customer --}}
        <div class="flex flex-col gap-1.5">
            <flux:label>Cliente</flux:label>
            <x-dropdown-select size="sm" :selectable-items="$customers" :selected="$tempSelectedCustomerForFilter" searchable search="customerSearch"
                placeholder='Seleziona cliente' setFunction="setCustomer" :has-error="$errors->has('tempSelectedCustomerForFilter')" />
            <flux:error name="tempSelectedCustomerForFilter" />
        </div>

        {{-- Customer Type --}}
        <div class="flex flex-col gap-1.5">
            <flux:label>Tipologia cliente</flux:label>
            <x-dropdown-select size="sm" :selectable-items="$customerTypes" :selected="$tempSelectedCustomerTypeForFilter"
                placeholder='Seleziona tipologia cliente' setFunction="setCustomerType" :has-error="$errors->has('tempSelectedCustomerTypeForFilter')" />
            <flux:error name="tempSelectedCustomerTypeForFilter" />
        </div>



        {{-- Inserted At Date --}}
        <div class="flex flex-col gap-1.5">
            <flux:label>Data di inserimento</flux:label>
            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-1">
                    <flux:input type="date" size="sm" wire:model='tempInsertedAtDateMin' />
                    <flux:error name="tempInsertedAtDateMin" />
                </div>
                <div class="col-span-1">
                    <flux:input type="date" size="sm" wire:model='tempInsertedAtDateMax' />
                    <flux:error name="tempInsertedAtDateMax" />
                </div>
            </div>
        </div>



        {{-- Disbursement Date --}}
        @if ($expired === true)
            <div class="flex flex-col gap-1.5">
                <flux:label>Data liquidazione</flux:label>
                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-1">
                        <flux:input type="date" size="sm" wire:model='tempDisbursementDateMin' />
                        <flux:error name="tempDisbursementDateMin" />
                    </div>
                    <div class="col-span-1">
                        <flux:input type="date" size="sm" wire:model='tempDisbursementDateMax' />
                        <flux:error name="tempDisbursementDateMax" />
                    </div>
                </div>
            </div>
        @endif
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

        @include(
            'partials.practice-opportunity.practice-opportunity-filters',
            [
                'showProductType' => $type === null,
            ]
        )

    </div>
</x-modals.filter-modal>
