<x-modal name="practice-notes" maxWidth="lg">
    <div class="flex flex-col">
        <x-modal-header label="Note pratica {{ $selectedPractice?->id }}" class="mb-6" />

        <div class="flex flex-col gap-1.5 col-span-2">
            <flux:label>Note</flux:label>
            <x-display-textarea value="{{ $selectedPractice?->notes }}" />
        </div>

        {{-- Button --}}
        <div class="flex gap-3 justify-end mt-16">
            <flux:button variant="primary" type="button" size="sm"
                x-on:click="$dispatch('close-modal', 'practice-notes')"
                class="px-10 bg-gray-custom-2 border-gray-custom-2 text-gray-custom-5 hover:bg-gray-custom-3-hover hover:border-gray-custom-3-hover hover:text-white">
                Chiudi
            </flux:button>
        </div>
    </div>
</x-modal>
