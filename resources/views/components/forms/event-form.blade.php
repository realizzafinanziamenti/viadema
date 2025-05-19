@props([
    'submitFunction' => 'save',
    'form' => 'form',
    'submitButtonLabel' => 'Crea',
    'modalName' => 'event-create',
])

<form wire:submit.prevent='{{ $submitFunction }}' class="w-full mt-10 mb-5">
    <div class="grid grid-cols-2 gap-6" x-data="{
        startDate: '',
        minDate: '{{ now()->addDay()->format('Y-m-d') }}',
        maxDate: '{{ now()->addMonth()->format('Y-m-d') }}'
    }" x-init="$watch('startDate', value => {
        if (value) {
            minDate = new Date(value);
            minDate.setDate(minDate.getDate() + 1);
            minDate = minDate.toISOString().split('T')[0]; // Formattazione YYYY-MM-DD
    
            maxDate = new Date(value);
            maxDate.setMonth(maxDate.getMonth() + 1);
            maxDate.setDate(maxDate.getDate() + 1);
            maxDate = maxDate.toISOString().split('T')[0];
        }
    })">
        {{-- Title --}}
        <div class="flex flex-col gap-1.5 col-span-2">
            <flux:label>Titolo *</flux:label>
            <div class="flex flex-col gap-0.5">
                <flux:input size="sm" wire:model='{{ $form }}.title' />
                <flux:error name="{{ $form }}.title" />
            </div>
        </div>
        {{-- Start Date --}}
        <div class="flex flex-col gap-1.5">
            <flux:label>Data evento *</flux:label>
            <div class="flex flex-col gap-0.5">
                <flux:input type="date" size="sm" wire:model='{{ $form }}.startDate' x-model="startDate"
                    id="start_date" name="start_date" min="{{ now()->format('Y-m-d') }}" />
                <flux:error name="{{ $form }}.startDate" />
            </div>
        </div>
        {{-- Repeat Until --}}
        <div class="flex flex-col gap-1.5">
            @if ($submitFunction === 'save')
                <flux:label>Ripeti evento fino al</flux:label>
                <div class="flex flex-col gap-0.5">
                    <flux:input type="date" size="sm" wire:model='{{ $form }}.repeatUntil'
                        x-bind:min="minDate" x-bind:max="maxDate" id="repeat_until"
                        name="repeat_until" />
                    <flux:label>Max 1 mese dalla data di inizio</flux:label>
                    <flux:error name="{{ $form }}.repeatUntil" />
                </div>
            @endif
        </div>
        {{-- Start Time --}}
        <div class="flex flex-col gap-1.5">
            <flux:label>Orario inizio *</flux:label>
            <div class="flex flex-col gap-0.5">
                <flux:input type="time" size="sm" wire:model='{{ $form }}.startTime' id="start_time"
                    name="start_time" min="08:00" max="21:00" />
                <flux:label>Min: 08:00 - Max: 21:00</flux:label>
                <flux:error name="{{ $form }}.startTime" />
            </div>
        </div>
        {{-- End Time --}}
        <div class="flex flex-col gap-1.5">
            <flux:label>Orario fine *</flux:label>
            <div class="flex flex-col gap-0.5">
                <flux:input type="time" size="sm" wire:model='{{ $form }}.endTime' id="end_time"
                    name="end_time" min="08:30" max="22:00" />
                <flux:label>Min: 08:30 - Max: 22:00</flux:label>
                <flux:error name="{{ $form }}.endTime" />
            </div>
        </div>
        {{-- Description --}}
        <div class="flex flex-col gap-1.5 col-span-2">
            <flux:textarea label="Descrizione" resize="none" wire:model='{{ $form }}.description' />
            <flux:error name="{{ $form }}.description" />
        </div>
    </div>

    {{-- Submit Buttons --}}
    <div class="flex items-center justify-end gap-x-3 mt-18">
        <flux:button variant="primary" type="button" size="sm"
            x-on:click="$dispatch('close-modal', '{{ $modalName }}')"
            class="px-10 bg-gray-custom-2 border-gray-custom-2 text-gray-custom-5 hover:bg-gray-custom-3-hover hover:border-gray-custom-3-hover hover:text-white">
            Annulla
        </flux:button>

        <flux:button variant="primary" type="submit" size="sm"
            class="px-10 bg-azure-custom border-azure-custom hover:bg-azure-custom-hover hover:border-azure-custom-hover">
            {{ $submitButtonLabel }}
        </flux:button>
    </div>
</form>
