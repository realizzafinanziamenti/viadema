<div class="w-full" x-data x-on:step-changed.window="window.scrollTo({ top: 0, behavior: 'smooth' })">
    <x-card class="w-3xl mx-auto">
        <x-card-header label="Modifica nuova pratica" />

        <form wire:submit.prevent='savePractice' class="w-2xl mx-auto mt-10 mb-5">

            {{-- Toggle Buttons --}}
            <x-forms.step-label-container class="mx-auto w-3/4 mb-6">
                <x-forms.step-label label="Dati Cliente" :step="1" :currentStep="$step" />
                <x-forms.step-label label="Informazioni Generali" :step="2" :currentStep="$step" />
                <x-forms.step-label label="Riepilogo" :step="3" :currentStep="$step" />
            </x-forms.step-label-container>

            {{-- STEP 1 --}}
            @if ($step === 1)
                @include('partials.practice.form-step-1')
            @endif

            {{-- STEP 2 --}}
            @if ($step === 2)
                @include('partials.practice.form-step-2')
            @endif

            {{-- STEP 3 --}}
            @if ($step === 3)
                @include('partials.practice.form-step-3')

                {{-- Last Step Buttons --}}
                <div class="flex items-center justify-end gap-x-3 mt-18">
                    <flux:button variant="primary" type="button" size="sm" wire:click="secondPrevStep"
                        class="px-10 bg-gray-custom-2 border-gray-custom-2 text-gray-custom-5 hover:bg-gray-custom-3-hover hover:border-gray-custom-3-hover hover:text-white">
                        Annulla
                    </flux:button>

                    <flux:button variant="primary" type="submit" size="sm"
                        class="px-10 bg-azure-custom border-azure-custom hover:bg-azure-custom-hover hover:border-azure-custom-hover">
                        Modifica
                    </flux:button>
                </div>
            @endif

        </form>
    </x-card>

    {{-- Create New Customer Modal --}}
    <x-modal name="customer-create" maxWidth="3xl">
        <x-modal-header label="Crea nuovo cliente" />

        <form wire:submit.prevent='saveCustomer' class="w-full mt-10 mb-5">
            @include('partials.customer.customer-form-fields', [
                'context' => 'customer',
                'search' => 'teamMemberSearch',
                'form' => 'customerForm',
                'selectedUserId' => $customerForm->userId,
            ])

            {{-- Submit Buttons --}}
            <div class="flex items-center justify-end gap-x-3 mt-18">
                <a href="{{ route('customer.index') }}" wire:navigate>
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
    </x-modal>

    {{-- Delete Practice Modal --}}
    <x-delete-modal name="delete-attachment" header="Conferma Eliminazione Allegato" function="deleteAttachment"
        message="Sei sicuro di voler eliminare l'allegato' <strong>{{ $selectedAttachment?->file_name }}</strong>?" />
</div>
