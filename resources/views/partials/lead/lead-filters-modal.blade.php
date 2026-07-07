<x-modals.filter-modal header="Filtra leads" maxWidth="4xl">
    <div class="grid grid-cols-3 gap-4">
        {{-- User --}}
        <div class="flex flex-col gap-1.5">
            <flux:label>Collaboratore</flux:label>
            <x-dropdown-select
                size="sm"
                :selectable-items="$teamMembers"
                :selected="$tempSelectedTeamMemberForFilter"
                searchable
                search="teamMemberSearch"
                placeholder="Seleziona collaboratore"
                setFunction="setTeamMember"
                :has-error="$errors->has('tempSelectedTeamMemberForFilter')"
            />
            <flux:error name="tempSelectedTeamMemberForFilter" />
        </div>

        {{-- Lead Status --}}
        <div class="flex flex-col gap-1.5">
            <flux:label>Stato lead</flux:label>
            <x-dropdown-select
                size="sm"
                :selectable-items="$leadStatuses"
                :selected="$tempSelectedLeadStatusForFilter"
                placeholder="Seleziona stato"
                setFunction="setLeadStatusForFilter"
                :has-error="$errors->has('tempSelectedLeadStatusForFilter')"
            />
            <flux:error name="tempSelectedLeadStatusForFilter" />
        </div>

        {{-- Lead Source --}}
        <div class="flex flex-col gap-1.5">
            <flux:label>Provenienza</flux:label>
            <x-dropdown-select
                size="sm"
                :selectable-items="$leadSourcesForFilter"
                :selected="$tempSelectedLeadSourceForFilter"
                placeholder="Seleziona provenienza"
                setFunction="setLeadSourceForFilter"
                :has-error="$errors->has('tempSelectedLeadSourceForFilter')"
            />
            <flux:error name="tempSelectedLeadSourceForFilter" />
        </div>

        {{-- Customer Type --}}
        <div class="flex flex-col gap-1.5">
            <flux:label>Tipologia cliente</flux:label>
            <x-dropdown-select
                size="sm"
                :selectable-items="$customerTypes"
                :selected="$tempSelectedCustomerTypeForFilter"
                placeholder="Seleziona tipologia cliente"
                setFunction="setCustomerType"
                :has-error="$errors->has('tempSelectedCustomerTypeForFilter')"
            />
            <flux:error name="tempSelectedCustomerTypeForFilter" />
        </div>

        {{-- Created At --}}
        <div class="flex flex-col gap-1.5">
            <flux:label>Data creazione</flux:label>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <flux:input type="date" size="sm" wire:model="tempCreatedAtDateMin" />
                    <flux:error name="tempCreatedAtDateMin" />
                </div>
                <div>
                    <flux:input type="date" size="sm" wire:model="tempCreatedAtDateMax" />
                    <flux:error name="tempCreatedAtDateMax" />
                </div>
            </div>
        </div>

        {{-- Updated At --}}
        <div class="flex flex-col gap-1.5">
            <flux:label>Ultimo contatto</flux:label>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <flux:input type="date" size="sm" wire:model="tempUpdatedAtDateMin" />
                    <flux:error name="tempUpdatedAtDateMin" />
                </div>
                <div>
                    <flux:input type="date" size="sm" wire:model="tempUpdatedAtDateMax" />
                    <flux:error name="tempUpdatedAtDateMax" />
                </div>
            </div>
        </div>

        {{-- Recontact Date --}}
        <div class="flex flex-col gap-1.5">
            <flux:label>Data ricontatto</flux:label>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <flux:input type="date" size="sm" wire:model="tempRecontactDateMin" />
                    <flux:error name="tempRecontactDateMin" />
                </div>
                <div>
                    <flux:input type="date" size="sm" wire:model="tempRecontactDateMax" />
                    <flux:error name="tempRecontactDateMax" />
                </div>
            </div>
        </div>
    </div>
</x-modals.filter-modal>
