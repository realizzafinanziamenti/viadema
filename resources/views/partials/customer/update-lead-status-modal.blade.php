<x-modal name="update-lead-status">
    <div class="flex flex-col">
        <x-modal-header label="Aggiorna stato profilo" class="mb-6" />

        <form wire:submit.prevent='updateLeadStatus'>
            {{-- Lead Status Dropdown --}}
            <div class="flex flex-col gap-1.5">
                <flux:label>Stato profilo</flux:label>
                <x-dropdown-select size="sm" :selectable-items="$leadStatuses" :selected="$selectedLeadStatus" placeholder='Seleziona stato'
                    setFunction="setLeadStatus" :has-error="$errors->has('selectedLeadStatus')" />

                <flux:error name="selectedLeadStatus" />
            </div>
            @if ($this->selectedLeadStatusShowsRecontactDate())
                <div class="flex flex-col gap-1.5 mt-4">
                    <flux:label>Data ricontatto </flux:label>

                    <div class="flex flex-col gap-0.5">
                        <flux:input type="date" size="sm" wire:model="selectedLeadRecontactDate" />
                        <flux:error name="selectedLeadRecontactDate" />
                    </div>
                </div>
            @endif

            {{-- Buttons --}}
            <div class="flex gap-3 justify-end mt-16">
                <flux:button variant="primary" type="button" size="sm"
                    x-on:click="$dispatch('close-modal', 'update-lead-status')"
                    class="px-10 bg-gray-custom-2 border-gray-custom-2 text-gray-custom-5 hover:bg-gray-custom-3-hover hover:border-gray-custom-3-hover hover:text-white">
                    Annulla
                </flux:button>

                <flux:button variant="primary" type="submit" size="sm"
                    class="px-10 bg-azure-custom border-azure-custom hover:bg-azure-custom-hover hover:border-azure-custom-hover">
                    Aggiorna
                </flux:button>
            </div>
        </form>
    </div>
</x-modal>
