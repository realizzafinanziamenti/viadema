<div class="grid grid-cols-2 gap-6">

    {{-- Customer Select --}}
    @if (!$customerPreselected)
        <div class="flex flex-col gap-1.5 col-span-2">
            <div class="flex items-center justify-between">
                <flux:label>
                    Cerca cliente censito
                </flux:label>

                <x-buttons.inline-action-button
                    label="Crea anagrafica nuovo cliente"
                    wire:click="openCreateCustomerModal"
                >
                    <x-slot:icon>
                        <flux:icon.plus class="size-2.5" />
                    </x-slot:icon>
                </x-buttons.inline-action-button>
            </div>

            <x-dropdown-select
                size="sm"
                :selectable-items="$customers"
                :selected="$practiceForm->customerId"
                searchable
                search="customerSearch"
                placeholder="Seleziona cliente"
                setFunction="setCustomer"
                :has-error="$errors->has('practiceForm.customerId')"
            />

            <flux:error name="practiceForm.customerId" />
        </div>
    @endif

    {{-- Editable Customer Data --}}
    @if ($selectedCustomer)
        <div class="col-span-2">
            @include('partials.customer.customer-form-fields', [
                'context' => 'practice',
                'search' => 'teamMemberSearch',
                'form' => 'customerForm',
                'selectedUserId' => $customerForm->userId,
            ])
        </div>
    @endif

    {{-- Acquisition Channel --}}
    <x-forms.acquisition-channel-field
        :options="$leadSources"
        :selected="$practiceForm->acquisitionChannel"
        set-function="setOpportunityAcquisitionChannel"
        error-name="practiceForm.acquisitionChannel"
    />

    @if (isset($sourceOpportunities) && $sourceOpportunities->count() > 1)
        <div class="flex flex-col gap-1.5 col-span-2">
            <flux:label>Opportunità da convertire</flux:label>
            <select wire:change="setPracticeOpportunity($event.target.value)" class="border border-zinc-200 rounded-md text-sm p-2">
                <option value="">Seleziona opportunità</option>
                @foreach ($sourceOpportunities as $source)
                    <option value="{{ $source->id }}" @selected($practiceForm->opportunity?->id === $source->id)>
                        #{{ $source->id }} - {{ $source->productType?->name ?? 'Senza prodotto' }}
                    </option>
                @endforeach
            </select>
            <flux:error name="practiceForm.opportunity" />
        </div>
    @endif

    @include('partials.practice.attachment-fields', [
        'existingAttachments' => isset($practice) ? $practice->attachments : ($existingAttachments ?? collect()),
        'attachmentOwner' => $practice ?? null,
        'canDeleteAttachments' => isset($practice) && Gate::allows('delete', $practice),
        'pendingAttachments' => $practiceForm->attachments,
        'attachmentField' => 'practiceForm.attachments',
    ])
</div>

{{-- Next Step Buttons --}}
<div class="flex items-center justify-end gap-x-3 mt-18">

    @if (!$customerPreselected)
        <a
            href="{{ route('practice.index') }}"
            wire:navigate
        >
            <flux:button
                variant="primary"
                type="button"
                size="sm"
                class="px-10 bg-gray-custom-2 border-gray-custom-2 text-gray-custom-5
                    hover:bg-gray-custom-3-hover hover:border-gray-custom-3-hover hover:text-white"
            >
                Annulla
            </flux:button>
        </a>
    @else
        <a
            href="{{ route('lead.show', ['id' => $selectedCustomer->id]) }}"
            wire:navigate
        >
            <flux:button
                variant="primary"
                type="button"
                size="sm"
                class="px-10 bg-gray-custom-2 border-gray-custom-2 text-gray-custom-5
                    hover:bg-gray-custom-3-hover hover:border-gray-custom-3-hover hover:text-white"
            >
                Torna al dettaglio lead
            </flux:button>
        </a>
    @endif

    <flux:button
        variant="primary"
        type="button"
        size="sm"
        wire:click="firstNextStep"
        class="px-10 bg-azure-custom border-azure-custom
            hover:bg-azure-custom-hover hover:border-azure-custom-hover"
    >
        Continua
    </flux:button>

</div>
