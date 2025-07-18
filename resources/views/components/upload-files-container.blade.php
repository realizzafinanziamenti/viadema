@props(['model' => null])

<div x-data="{
    isDragging: false,

    handleDrop(e) {
        this.isDragging = false;

        const droppedFiles = e.dataTransfer.files;
        $refs.input.files = droppedFiles;
        $refs.input.dispatchEvent(new Event('change', { bubbles: true }));
    }
}" @dragover.prevent="isDragging = true" @dragleave.prevent="isDragging = false"
    @drop.prevent="handleDrop($event)" class="w-full">

    <input id="files" name="files" type="file" wire:model="{{ $model }}" class="hidden" multiple
        x-ref="input" accept="{{ $this->acceptedFileTypes() }}" />

    <label for="files" class="cursor-pointer relative">
        <div :class="isDragging
            ?
            'border-blue-custom-hover bg-blue-custom-light text-blue-custom' :
            'border-gray-custom-3 bg-[#F1F0EF] text-gray-custom-5'"
            class="relative w-full h-30 flex justify-center items-center border border-dashed rounded-md">

            <div wire:loading.remove wire:target="{{ $model }}"
                class="w-48 text-center flex flex-col items-center gap-2">
                <flux:icon.cloud-arrow-up class="size-8" />

                <div class="text-[13px]">Trascina un file qui o seleziona un file dal tuo <span
                        class="font-semibold">computer</span>
                </div>
            </div>

            {{-- Loading spinner --}}
            <div wire:loading wire:target="{{ $model }}">
                <flux:icon.loading class="size-10" />
            </div>

        </div>
    </label>
</div>
