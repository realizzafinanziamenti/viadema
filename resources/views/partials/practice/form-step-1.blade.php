<div class="grid grid-cols-2 gap-6">
    {{-- Customer Select --}}
    <div class="flex flex-col gap-1.5 col-span-2">
        <div class="flex items-center justify-between">
            <flux:label>Cerca cliente censito</flux:label>

            <x-buttons.inline-action-button label="Crea anagrafica nuovo cliente" wire:click="openCreateCustomerModal">
                <x-slot:icon>
                    <flux:icon.plus class="size-2.5" />
                </x-slot:icon>
            </x-buttons.inline-action-button>
        </div>

        <x-dropdown-select size="sm" :selectable-items="$customers" :selected="$practiceForm->customerId" searchable search="customerSearch"
            placeholder='Seleziona cliente' setFunction="setCustomer" :has-error="$errors->has('practiceForm.customerId')" />

        <flux:error name="practiceForm.customerId" />
    </div>

    @include('partials.practice.customer-preview-fields')

    {{-- Attachments --}}
    <div class="flex flex-col gap-6 col-span-2">
        {{-- Old Attachements for Uploaded Practice --}}
        @if (!empty($practice?->attachments) && $practice->attachments->isNotEmpty())
            <div class="flex flex-col gap-1.5">
                <flux:label>Allegati esistenti</flux:label>

                @foreach ($practice?->attachments as $attachment)
                    <x-display-file :attachment="$attachment" value="{{ $attachment->file_name }}" />
                @endforeach
            </div>
        @endif

        {{-- Temporary Attachments --}}
        <div class="flex flex-col gap-1.5">
            <flux:label>Allegati</flux:label>

            <x-upload-files-container model="temporaryFiles" multiple />

            <flux:error name="temporaryFiles" />

            <div class="text-xs text-gray-custom-4">
                <div>- Max 10MB per file</div>
                <div>- Max 10 file per volta</div>
                <div>- Formati accettati: jpg, png, pdf, doc, docx, xls, xlsm, xlsx, csv</div>
            </div>
        </div>

        {{-- Temporary Attachments Preview --}}
        @if (!empty($practiceForm->attachments))
            <div class="flex flex-col gap-1.5">
                <flux:label class="font-semibold">Allegati temporanei caricati
                    {{ count($practiceForm->attachments) }}/{{ count($practiceForm->attachments) }}</flux:label>

                @foreach ($practiceForm->attachments as $index => $attachment)
                    <x-display-preview-file :attachment="$attachment" :index="$index" />
                @endforeach
            </div>
        @endif
    </div>
</div>

{{-- Next Step Buttons --}}
<div class="flex items-center justify-end gap-x-3 mt-18">
    <a href="{{ route('practice.index') }}" wire:navigate>
        <flux:button variant="primary" type="button" size="sm"
            class="px-10 bg-gray-custom-2 border-gray-custom-2 text-gray-custom-5 hover:bg-gray-custom-3-hover hover:border-gray-custom-3-hover hover:text-white">
            Annulla
        </flux:button>
    </a>

    <flux:button variant="primary" type="button" size="sm" wire:click="firstNextStep"
        class="px-10 bg-azure-custom border-azure-custom hover:bg-azure-custom-hover hover:border-azure-custom-hover">
        Continua
    </flux:button>
</div>
