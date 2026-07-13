<x-modals.filter-modal header="Filtra leads" maxWidth="5xl">
    <div
        class="
            grid grid-cols-1
            gap-y-4
            md:grid-cols-2 md:gap-x-6
            xl:grid-cols-3 xl:gap-x-8
        "
    >
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
        <x-filters.range label="Data creazione">
            <x-slot:from>
                <flux:input
                    type="date"
                    size="sm"
                    wire:model="tempCreatedAtDateMin"
                />

                <flux:error name="tempCreatedAtDateMin" />
            </x-slot:from>

            <x-slot:to>
                <flux:input
                    type="date"
                    size="sm"
                    wire:model="tempCreatedAtDateMax"
                />

                <flux:error name="tempCreatedAtDateMax" />
            </x-slot:to>
        </x-filters.range>

        {{-- Updated At --}}
        <x-filters.range label="Ultimo contatto">
            <x-slot:from>
                <flux:input
                    type="date"
                    size="sm"
                    wire:model="tempUpdatedAtDateMin"
                />

                <flux:error name="tempUpdatedAtDateMin" />
            </x-slot:from>

            <x-slot:to>
                <flux:input
                    type="date"
                    size="sm"
                    wire:model="tempUpdatedAtDateMax"
                />

                <flux:error name="tempUpdatedAtDateMax" />
            </x-slot:to>
        </x-filters.range>

        {{-- Recontact Date --}}
        <x-filters.range label="Data ricontatto">
            <x-slot:from>
                <flux:input
                    type="date"
                    size="sm"
                    wire:model="tempRecontactDateMin"
                />

                <flux:error name="tempRecontactDateMin" />
            </x-slot:from>

            <x-slot:to>
                <flux:input
                    type="date"
                    size="sm"
                    wire:model="tempRecontactDateMax"
                />

                <flux:error name="tempRecontactDateMax" />
            </x-slot:to>
        </x-filters.range>

        {{-- Practice Opportunity Filters --}}
        @include(
            'partials.practice-opportunity.practice-opportunity-filters',
            [
                'showProductType' => true,
            ]
        )
    </div>
</x-modals.filter-modal>
