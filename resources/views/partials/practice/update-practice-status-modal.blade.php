<x-modal name="update-practice-status">
    <div class="flex flex-col">
        <x-modal-header label="Aggiorna stato pratica" class="mb-6" />

        <form wire:submit.prevent='updatePracticeStatus'>
            {{-- Practice Status Dropdown --}}
            <div class="flex flex-col gap-1.5">
                <flux:label>Stato pratica</flux:label>
                <x-dropdown-select size="sm" :selectable-items="$practiceStatuses" :selected="$selectedPracticeStatus" placeholder='Seleziona stato'
                    setFunction="setPracticeStatus" :has-error="$errors->has('selectedPracticeStatus')" />

                <flux:error name="selectedPracticeStatus" />
            </div>

            {{-- FOR DISBURSED PRACTICES ONLY --}}
            @if ($selectedPracticeStatus === App\Enums\PracticeStatus::DISBURSED->value)
                {{-- Disbursement Date --}}
                <div wire:transition class="flex flex-col gap-1.5 mt-3">
                    <flux:label>Data liquidazione</flux:label>
                    <div class="flex flex-col gap-0.5">
                        <flux:input type="date" size="sm" wire:model='disbursementDate' />
                        <flux:error name="disbursementDate" />
                    </div>
                </div>

                {{-- Archive Practice Warning --}}
                <div wire:transition class="text-[13px] text-gray-custom-4 mt-3">
                    La pratica verrà archiviata
                </div>
            @endif

            {{-- Buttons --}}
            <div class="flex gap-3 justify-end mt-16">
                <flux:button variant="primary" type="button" size="sm"
                    x-on:click="$dispatch('close-modal', 'update-practice-status')"
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
